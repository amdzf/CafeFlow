/**
 * CafeFlow POS - Kitchen Display System Engine (kitchen.js)
 * Real-time order synchronization & status actions
 */

let kdsPollingInterval = null;

document.addEventListener('DOMContentLoaded', function () {
    startKdsPolling();
});

function startKdsPolling() {
    if (kdsPollingInterval) clearInterval(kdsPollingInterval);
    kdsPollingInterval = setInterval(fetchKitchenOrders, 4000);
}

async function fetchKitchenOrders() {
    try {
        const response = await fetch(window.KDS_CONFIG.ordersApiUrl);
        const data = await response.json();

        if (data.success) {
            renderKdsBoard(data.orders);
            const syncEl = document.getElementById('kdsLastSync');
            if (syncEl) {
                const now = new Date();
                syncEl.textContent = 'Sinkron: ' + now.toLocaleTimeString('id-ID');
            }
        }
    } catch (err) {
        console.error('KDS sync error:', err);
    }
}

function renderKdsBoard(orders) {
    const container = document.getElementById('kdsBoardContainer');
    if (!container) return;

    if (!orders || orders.length === 0) {
        container.innerHTML = `
            <div class="col-12 text-center py-5 text-muted" id="kdsEmptyState">
                <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                <h5 class="fw-bold text-dark">Semua Pesanan Selesai Disajikan!</h5>
                <div class="small">Pesanan baru yang telah dibayar akan muncul secara otomatis di sini.</div>
            </div>
        `;
        return;
    }

    let html = '';
    orders.forEach(order => {
        const tableLabel = (order.order_type === 'dine_in')
            ? (order.table_number ? 'Meja ' + order.table_number : 'Dine-In')
            : 'TAKEAWAY';

        let badgeHtml = '';
        let actionBtnHtml = '';

        if (order.order_status === 'pending') {
            badgeHtml = '<span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Menunggu</span>';
            actionBtnHtml = `
                <button type="button" class="btn btn-warning btn-sm w-100 fw-bold" onclick="updateKitchenStatus(${order.id}, 'processing')">
                    <i class="bi bi-play-circle me-1"></i> Mulai Buat
                </button>
            `;
        } else if (order.order_status === 'processing') {
            badgeHtml = '<span class="badge bg-info text-dark"><i class="bi bi-hourglass-split me-1"></i>Diproses</span>';
            actionBtnHtml = `
                <button type="button" class="btn btn-primary btn-sm w-100 fw-bold" onclick="updateKitchenStatus(${order.id}, 'ready')">
                    <i class="bi bi-bell me-1"></i> Siap Disajikan
                </button>
            `;
        } else if (order.order_status === 'ready') {
            badgeHtml = '<span class="badge bg-primary"><i class="bi bi-bell me-1"></i>Siap Saji</span>';
            actionBtnHtml = `
                <button type="button" class="btn btn-success btn-sm w-100 fw-bold" onclick="updateKitchenStatus(${order.id}, 'completed')">
                    <i class="bi bi-check-circle me-1"></i> Selesaikan
                </button>
            `;
        }

        let itemsHtml = '';
        if (order.items && Array.isArray(order.items)) {
            order.items.forEach(item => {
                let noteHtml = item.notes ? `<div class="small text-danger fw-semibold fst-italic ps-4">&bull; ${escapeHtml(item.notes)}</div>` : '';
                itemsHtml += `
                    <li class="py-1.5 border-bottom border-light d-flex align-items-start justify-content-between">
                        <div>
                            <span class="fw-bold text-dark me-2">${item.quantity}x</span>
                            <span class="fw-semibold text-dark">${escapeHtml(item.product_name)}</span>
                            ${noteHtml}
                        </div>
                    </li>
                `;
            });
        }

        const timeStr = order.created_at ? order.created_at.substring(11, 16) : '';

        html += `
            <div class="kds-card status-${order.order_status}" id="kds-order-${order.id}">
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-light bg-opacity-50">
                    <div>
                        <div class="fw-bold text-dark fs-5">${tableLabel}</div>
                        <div class="small text-muted">#${escapeHtml(order.order_number)} &bull; ${escapeHtml(order.customer_name)}</div>
                    </div>
                    <div>
                        ${badgeHtml}
                        <div class="small text-muted text-end mt-1">${timeStr}</div>
                    </div>
                </div>

                <div class="p-3">
                    <ul class="list-unstyled mb-3">
                        ${itemsHtml}
                    </ul>
                    <div class="d-flex gap-2">
                        ${actionBtnHtml}
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

async function updateKitchenStatus(orderId, newStatus) {
    try {
        const response = await fetch(`${window.KDS_CONFIG.updateStatusUrl}${orderId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.KDS_CONFIG.csrfToken
            },
            body: JSON.stringify({ order_status: newStatus })
        });

        const data = await response.json();
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Status Diperbarui',
                text: data.message,
                timer: 1500,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
            fetchKitchenOrders();
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Kesalahan Sistem', text: err.message });
    }
}

function escapeHtml(text) {
    return (text || '').toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
