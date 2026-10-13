<?php

namespace App\Controllers;

use Core\Controller;
use Core\Request;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Helpers\Logger;
use App\Helpers\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request): void
    {
        $expenseModel = new Expense();
        $catModel = new ExpenseCategory();

        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-t'));
        $categoryId = $request->get('category_id') ? (int)$request->get('category_id') : null;

        $expenses = $expenseModel->allWithCategory($startDate, $endDate, $categoryId);
        $totalAmount = $expenseModel->getTotalExpenses($startDate, $endDate);
        $categories = $catModel->all('name ASC');

        $this->render('expenses/index', [
            'title' => 'Pengeluaran Operasional - CafeFlow POS',
            'expenses' => $expenses,
            'totalAmount' => $totalAmount,
            'categories' => $categories,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedCategory' => $categoryId
        ]);
    }

    public function store(Request $request): void
    {
        $this->validateCsrf($request);

        $categoryId = (int)$request->post('category_id');
        $title = trim((string)$request->post('title'));
        $amount = (float)$request->post('amount');
        $expenseDate = $request->post('expense_date', date('Y-m-d'));
        $description = trim((string)$request->post('description', ''));

        if ($amount <= 0 || empty($title) || $categoryId <= 0) {
            $this->back('error', 'Silakan lengkapi data pengeluaran dengan benar.');
            return;
        }

        $expenseModel = new Expense();
        $id = $expenseModel->create([
            'category_id' => $categoryId,
            'title' => $title,
            'amount' => $amount,
            'expense_date' => $expenseDate,
            'description' => $description,
            'user_id' => (int)Auth::id()
        ]);

        Logger::log(
            'Catat Pengeluaran',
            'expenses',
            (string)$id,
            "Pengeluaran: {$title} senilai Rp " . number_format($amount, 0, ',', '.')
        );

        $this->redirect('/expenses', 'success', 'Pengeluaran operasional berhasil dicatat!');
    }

    public function delete(Request $request, string $id): void
    {
        $this->validateCsrf($request);

        $expenseModel = new Expense();
        $expense = $expenseModel->find((int)$id);
        if ($expense) {
            $expenseModel->delete($expense['id']);
            Logger::log('Hapus Pengeluaran', 'expenses', (string)$expense['id'], "Pengeluaran {$expense['title']} dihapus.");
        }

        $this->redirect('/expenses', 'success', 'Data pengeluaran berhasil dihapus.');
    }
}
