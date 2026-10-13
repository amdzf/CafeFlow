/**
 * CafeFlow POS - Frontend Engine (pos.js)
 * High-performance shopping cart, calculations, Cash modal, and Dynamic QRIS polling
 */

let cart = [];
let currentOrderId = null;
let currentOrderNumber = null;
let currentGrandTotal = 0;
let qrisPollingInterval = null;
let countdownInterval = null;

document.addEventListener('DOMContentLoaded', function () {
    initSearchAndFilters();
    renderCart();
});

// 1. Search and Category Filters
function initSearchAndFilters() {
    const searchInput = document.getElementById('productSearchInput');
    const categoryButtons = document.querySelectorAll('.category-pill');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            filterProducts();
        });
    }

    categoryButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            categoryButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            filterProducts();
        });
    });
}

function filterProducts() {
    const query = (document.getElementById('productSearchInput')?.value || '').toLowerCase().trim();
    const activeCatBtn = document.querySelector('.category-pill.active');
    const selectedCategory = activeCatBtn ? activeCatBtn.dataset.category : 'all';

    const items = document.querySelectorAll('.product-item');
    let visibleCount = 0;

    items.forEach(item => {
        const name = (item.dataset.name || '').toLowerCase();
        const sku = (item.dataset.sku || '').toLowerCase();
        const category = item.dataset.category || '';

        const matchesQuery = !query || name.includes(query) || sku.includes(query);
        const matchesCategory = (selectedCategory === 'all') || (category === selectedCategory);

        if (matchesQuery && matchesCategory) {
            item.classList.remove('d-none');
            visibleCount++;
        } else {
            item.classList.add('d-none');
        }
    });

    const noProdAlert = document.getElementById('noProductAlert');
    if (noProdAlert) {
        if (visibleCount === 0) {
            noProdAlert.classList.remove('d-none');
        } else {
            noProdAlert.classList.add('d-none');
        }
    }
}

// 2. Cart Operations
function addToCart(product) {
    if (product.is_available === false || product.is_available === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Stok Tidak Tersedia',
            text: 'Menu ini sedang kehabisan stok bahan atau porsi.',
            timer: 2000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
        return;
    }

    const existingIndex = cart.findIndex(item => item.product_id === product.id);
    if (existingIndex > -1) {
        cart[existingIndex].quantity += 1;
    } else {
        cart.push({
            product_id: product.id,
            name: product.name,
            price: parseFloat(product.price),
            quantity: 1,
            notes: ''
        });
    }

    renderCart();
}

function updateQuantity(productId, delta) {
    const idx = cart.findIndex(item => item.product_id === productId);
    if (idx === -1) return;

    cart[idx].quantity += delta;
    if (cart[idx].quantity <= 0) {
        cart.splice(idx, 1);
    }

    renderCart();
}

function updateNotes(productId, notes) {
    const item = cart.find(i => i.product_id === productId);
    if (item) {
        item.notes = notes;
    }
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.product_id !== productId);
    renderCart();
}

function clearCart() {
    if (cart.length === 0) return;
    cart = [];
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartItemsContainer');
    const emptyState = document.getElementById('cartEmptyState');

    if (cart.length === 0) {
        if (container) {
            container.innerHTML = `
                <div id="cartEmptyState" class="text-center py-5 text-muted">
                    <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                    <div class="fw-semibold">Keranjang Masih Kosong</div>
                    <div class="small text-muted">Pilih menu di samping untuk menambahkan pesanan.</div>
                </div>
            `;
        }
        calculateTotals();
        return;
    }

    let html = '';
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        html += `
            <div class="cart-item-card">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div class="me-2">
                        <div class="fw-semibold text-dark" style="font-size: 0.92rem;">${escapeHtml(item.name)}</div>
                        <div class="small text-secondary fw-semibold">${formatRupiah(item.price)}</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-danger p-0 border-0" onclick="removeFromCart(${item.product_id})">
                        <i class="bi bi-x-circle"></i>
                    </button>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="input-group input-group-sm" style="width: 105px;">
                        <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(${item.product_id}, -1)">
                            <i class="bi bi-dash"></i>
                        </button>
                        <span class="input-group-text bg-white px-2.5 fw-bold text-dark" style="min-width: 32px; justify-content: center;">
                            ${item.quantity}
                        </span>
                        <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(${item.product_id}, 1)">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>
                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                        ${formatRupiah(itemTotal)}
                    </div>
                </div>

                <div>
                    <input type="text" class="form-control form-control-sm py-1 px-2 border-0 bg-white" 
                           placeholder="Catatan (contoh: Less sugar, es sedikit)..." 
                           value="${escapeHtml(item.notes || '')}"
                           onchange="updateNotes(${item.product_id}, this.value)"
                           style="font-size: 0.78rem; border-radius: 6px;">
                </div>
            </div>
        `;
    });

    if (container) {
        container.innerHTML = html;
    }

    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    cart.forEach(item => {
        subtotal += item.price * item.quantity;
    });

    const discPercentInput = document.getElementById('discountPercentInput');
    const discPercent = discPercentInput ? Math.max(0, Math.min(100, parseFloat(discPercentInput.value) || 0)) : 0;
    const discountAmount = Math.round((subtotal * discPercent) / 100);
    const afterDiscount = Math.max(0, subtotal - discountAmount);

    const taxPercent = window.POS_CONFIG ? window.POS_CONFIG.taxPercent : 10;
    const taxAmount = Math.round((afterDiscount * taxPercent) / 100);

    const grandTotal = afterDiscount + taxAmount;
    currentGrandTotal = grandTotal;

    const elSub = document.getElementById('displaySubtotal');
    const elDisc = document.getElementById('displayDiscount');
    const elTax = document.getElementById('displayTax');
    const elTotal = document.getElementById('displayGrandTotal');

    if (elSub) elSub.textContent = formatRupiah(subtotal);
    if (elDisc) elDisc.textContent = '- ' + formatRupiah(discountAmount);
    if (elTax) elTax.textContent = formatRupiah(taxAmount);
    if (elTotal) elTotal.textContent = formatRupiah(grandTotal);
}

function handleOrderTypeChange() {
    const isDineIn = document.getElementById('typeDineIn')?.checked;
    const tableContainer = document.getElementById('tableSelectContainer');
    if (tableContainer) {
        if (isDineIn) {
            tableContainer.style.display = 'block';
        } else {
            tableContainer.style.display = 'none';
        }
    }
}

// 3. Create Order via API helper
async function createOrderApi(paymentMethod) {
    if (cart.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Keranjang Kosong',
            text: 'Silakan pilih menu pesanan terlebih dahulu.',
            confirmButtonColor: '#4A2E18'
        });
        return null;
    }

    const isDineIn = document.getElementById('typeDineIn')?.checked;
    const tableSelect = document.getElementById('tableSelect');
    const customerName = document.getElementById('customerNameInput')?.value || 'Pelanggan Walk-in';
    const discountPercent = parseFloat(document.getElementById('discountPercentInput')?.value) || 0;

    let tableId = null;
    if (isDineIn) {
        tableId = tableSelect ? tableSelect.value : null;
        if (!tableId) {
            Swal.fire({
                icon: 'warning',
                title: 'Pilih Meja',
                text: 'Untuk pesanan Dine-In, silakan pilih nomor meja yang tersedia.',
                confirmButtonColor: '#4A2E18'
            });
            return null;
        }
    }

    const payload = {
        cart: cart,
        customer_name: customerName,
        order_type: isDineIn ? 'dine_in' : 'takeaway',
        table_id: tableId,
        discount_percent: discountPercent,
        payment_method: paymentMethod
    };

    try {
        const response = await fetch(window.POS_CONFIG.createOrderUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.POS_CONFIG.csrfToken
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (!data.success) {
            throw new Error(data.message || 'Gagal membuat pesanan');
        }

        currentOrderId = data.order_id;
        currentOrderNumber = data.order_number;
        currentGrandTotal = data.grand_total;

        return data;
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal Membuat Pesanan',
            text: err.message,
            confirmButtonColor: '#4A2E18'
        });
        return null;
    }
}

// 4. CASH PAYMENT FLOW
async function openCashPaymentModal() {
    if (cart.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Keranjang Kosong', text: 'Pilih produk terlebih dahulu.' });
        return;
    }

    calculateTotals();
    const modalTotal = document.getElementById('cashModalGrandTotal');
    const tenderedInput = document.getElementById('cashTenderedInput');
    const changeDisplay = document.getElementById('cashChangeDisplay');

    if (modalTotal) modalTotal.textContent = formatRupiah(currentGrandTotal);
    if (tenderedInput) {
        tenderedInput.value = '';
    }
    if (changeDisplay) changeDisplay.textContent = formatRupiah(0);

    const cashModal = new bootstrap.Modal(document.getElementById('cashModal'));
    cashModal.show();
}

function setQuickCash(amount) {
    const input = document.getElementById('cashTenderedInput');
    if (!input) return;

    if (amount === 'exact') {
        input.value = currentGrandTotal;
    } else {
        input.value = amount;
    }
    calculateCashChange();
}

function calculateCashChange() {
    const input = document.getElementById('cashTenderedInput');
    const display = document.getElementById('cashChangeDisplay');
    const container = document.getElementById('changeContainer');

    const tendered = parseFloat(input?.value) || 0;
    const change = tendered - currentGrandTotal;

    if (display) {
        if (change >= 0) {
            display.textContent = formatRupiah(change);
            display.className = 'fs-3 fw-bold text-success';
            if (container) container.style.backgroundColor = 'var(--cafe-accent-light)';
        } else {
            display.textContent = 'Kurang ' + formatRupiah(Math.abs(change));
            display.className = 'fs-3 fw-bold text-danger';
            if (container) container.style.backgroundColor = '#FEE2E2';
        }
    }
}

async function submitCashPayment() {
    const tenderedInput = document.getElementById('cashTenderedInput');
    const tendered = parseFloat(tenderedInput?.value) || 0;

    if (tendered < currentGrandTotal) {
        Swal.fire({
            icon: 'warning',
            title: 'Uang Kurang',
            text: 'Nominal uang tunai yang diterima kurang dari total tagihan (' + formatRupiah(currentGrandTotal) + ').',
            confirmButtonColor: '#4A2E18'
        });
        return;
    }

    const btnConfirm = document.getElementById('btnConfirmCash');
    if (btnConfirm) btnConfirm.disabled = true;

    // 1. Create order
    const orderRes = await createOrderApi('cash');
    if (!orderRes) {
        if (btnConfirm) btnConfirm.disabled = false;
        return;
    }

    // 2. Submit payment
    try {
        const payRes = await fetch(window.POS_CONFIG.payCashUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.POS_CONFIG.csrfToken
            },
            body: JSON.stringify({
                order_id: currentOrderId,
                tendered_amount: tendered
            })
        });

        const data = await payRes.json();
        if (!data.success) {
            throw new Error(data.message || 'Gagal memproses pembayaran tunai');
        }

        // Close Cash modal
        const cashModalEl = document.getElementById('cashModal');
        const cashModal = bootstrap.Modal.getInstance(cashModalEl);
        if (cashModal) cashModal.hide();

        Swal.fire({
            icon: 'success',
            title: 'Pembayaran Tunai Berhasil!',
            html: `Uang Diterima: <b>${formatRupiah(tendered)}</b><br>Kembalian: <b class="text-success">${formatRupiah(data.change_amount)}</b>`,
            showConfirmButton: true,
            confirmButtonText: '<i class="bi bi-printer"></i> Cetak Struk',
            showCancelButton: true,
            cancelButtonText: 'Tutup',
            confirmButtonColor: '#4A2E18'
        }).then((result) => {
            if (result.isConfirmed) {
                openReceiptModal(currentOrderId);
            } else {
                startNewPosOrder();
            }
        });

    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Kesalahan Pembayaran', text: err.message });
    } finally {
        if (btnConfirm) btnConfirm.disabled = false;
    }
}

// 5. QRIS PAYMENT FLOW
async function openQrisPaymentModal() {
    if (cart.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Keranjang Kosong', text: 'Pilih produk terlebih dahulu.' });
        return;
    }

    // 1. Create Order first
    const orderRes = await createOrderApi('qris');
    if (!orderRes) return;

    // 2. Open QRIS Modal
    const qrisModal = new bootstrap.Modal(document.getElementById('qrisModal'));
    qrisModal.show();

    // Reset Modal UI
    document.getElementById('qrisModalTotal').textContent = formatRupiah(currentGrandTotal);
    document.getElementById('qrisOrderNumDisplay').textContent = currentOrderNumber;
    document.getElementById('qrisLoadingSpinner').classList.remove('d-none');
    document.getElementById('qrisCodeImage').classList.add('d-none');
    document.getElementById('qrisSuccessOverlay').classList.remove('d-flex');
    document.getElementById('qrisSuccessOverlay').classList.add('d-none');
    document.getElementById('btnQrisPrintReceipt').classList.add('d-none');
    document.getElementById('qrisStatusBadge').className = 'badge bg-warning-subtle text-warning border border-warning px-2.5 py-1.5';
    document.getElementById('qrisStatusBadge').innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Menunggu Scan Pelanggan';

    // 3. Request QRIS Charge from Backend
    try {
        const chargeRes = await fetch(window.POS_CONFIG.createQrisUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.POS_CONFIG.csrfToken
            },
            body: JSON.stringify({ order_id: currentOrderId })
        });

        const data = await chargeRes.json();
        if (!data.success) {
            throw new Error(data.message || 'Gagal memperoleh QRIS dari gateway');
        }

        // Render QR Code
        const qrImg = document.getElementById('qrisCodeImage');
        qrImg.src = data.qr_url;
        qrImg.onload = () => {
            document.getElementById('qrisLoadingSpinner').classList.add('d-none');
            qrImg.classList.remove('d-none');
        };

        // Start Countdown Timer
        startQrisCountdown(15 * 60);

        // Start Background Polling every 3 seconds
        startQrisPolling(currentOrderId);

    } catch (err) {
        document.getElementById('qrisLoadingSpinner').innerHTML = `
            <div class="text-danger small mb-2"><i class="bi bi-exclamation-triangle fs-3 d-block"></i> ${err.message}</div>
        `;
    }
}

function startQrisCountdown(durationSeconds) {
    clearInterval(countdownInterval);
    let remaining = durationSeconds;
    const display = document.getElementById('qrisCountdown');

    countdownInterval = setInterval(() => {
        const m = Math.floor(remaining / 60);
        const s = remaining % 60;
        if (display) {
            display.textContent = `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }
        remaining--;
        if (remaining < 0) {
            clearInterval(countdownInterval);
            stopQrisPolling();
            if (display) display.textContent = 'Kedaluwarsa';
            document.getElementById('qrisStatusBadge').className = 'badge bg-danger-subtle text-danger border border-danger';
            document.getElementById('qrisStatusBadge').textContent = 'QRIS Telah Kedaluwarsa';
        }
    }, 1000);
}

function startQrisPolling(orderId) {
    stopQrisPolling();
    qrisPollingInterval = setInterval(async () => {
        try {
            const res = await fetch(window.POS_CONFIG.checkStatusUrl + orderId);
            const data = await res.json();
            if (data.success && data.is_paid) {
                // Payment verified!
                stopQrisPolling();
                clearInterval(countdownInterval);
                handleQrisSuccess(data);
            }
        } catch (e) {
            console.error('Polling error:', e);
        }
    }, 3000);
}

function stopQrisPolling() {
    if (qrisPollingInterval) {
        clearInterval(qrisPollingInterval);
        qrisPollingInterval = null;
    }
}

function handleQrisSuccess(data) {
    const overlay = document.getElementById('qrisSuccessOverlay');
    const badge = document.getElementById('qrisStatusBadge');
    const printBtn = document.getElementById('btnQrisPrintReceipt');

    if (overlay) {
        overlay.classList.remove('d-none');
        overlay.classList.add('d-flex');
    }

    if (badge) {
        badge.className = 'badge bg-success-subtle text-success border border-success px-2.5 py-1.5';
        badge.innerHTML = '<i class="bi bi-check2-all me-1"></i> Pembayaran Lunas (PAID)';
    }

    if (printBtn) {
        printBtn.classList.remove('d-none');
    }

    Swal.fire({
        icon: 'success',
        title: 'Pembayaran QRIS Berhasil!',
        text: `Pesanan #${data.order_number} telah terverifikasi lunas secara otomatis.`,
        timer: 3500,
        showConfirmButton: false,
        toast: true,
        position: 'top-end'
    });
}

// 6. Sandbox Simulation Webhook Trigger
async function simulatePaymentWebhook(targetStatus) {
    if (!currentOrderNumber) {
        Swal.fire({ icon: 'warning', title: 'Belum Ada Transaksi Aktif' });
        return;
    }

    const btn = (targetStatus === 'settlement') ? document.getElementById('btnSimulatePay') : document.getElementById('btnSimulateExpire');
    if (btn) btn.disabled = true;

    try {
        const res = await fetch(window.POS_CONFIG.simulateWebhookUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.POS_CONFIG.csrfToken
            },
            body: JSON.stringify({
                order_number: currentOrderNumber,
                transaction_status: targetStatus
            })
        });

        const data = await res.json();
        if (data.success || data.status === 'success') {
            Swal.fire({
                icon: 'info',
                title: 'Simulasi Gateway Dikirim',
                text: 'Webhook ' + targetStatus + ' berhasil disimulasikan dengan signature SHA512 valid.',
                timer: 2000,
                showConfirmButton: false
            });
            // Immediately poll
            if (currentOrderId) {
                const statusRes = await fetch(window.POS_CONFIG.checkStatusUrl + currentOrderId);
                const statusData = await statusRes.json();
                if (statusData.is_paid) {
                    stopQrisPolling();
                    handleQrisSuccess(statusData);
                }
            }
        } else {
            throw new Error(data.message || 'Simulasi gagal');
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Simulasi Gagal', text: err.message });
    } finally {
        if (btn) btn.disabled = false;
    }
}

// 7. Receipt Modal & Printing
function openReceiptModal(orderId) {
    const iframe = document.getElementById('receiptIframe');
    if (iframe) {
        iframe.src = window.POS_CONFIG.receiptBaseUrl + orderId + '/receipt';
    }
    const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
    modal.show();
}

function printReceiptFrame() {
    const iframe = document.getElementById('receiptIframe');
    if (iframe && iframe.contentWindow) {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    }
}

function printCurrentReceipt() {
    if (currentOrderId) {
        openReceiptModal(currentOrderId);
    }
}

function startNewPosOrder() {
    cart = [];
    currentOrderId = null;
    currentOrderNumber = null;
    currentGrandTotal = 0;
    stopQrisPolling();
    renderCart();

    // Reload page smoothly to refresh order number and table status
    window.location.reload();
}

function escapeHtml(text) {
    return (text || '').toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
