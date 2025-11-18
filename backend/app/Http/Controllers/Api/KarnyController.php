<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KarnyCustomer;
use App\Models\KarnyTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KarnyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get all customers (carnet)
     */
    public function customers()
    {
        $customers = auth()->user()
            ->karnyCustomers()
            ->with(['transactions' => function($q) {
                $q->latest()->limit(10);
            }])
            ->withCount(['transactions as pending_count' => function($q) {
                $q->where('type', 'credit')->where('status', 'pending');
            }])
            ->orderBy('name')
            ->get();

        $totalCreditOut = $customers->sum('current_balance');
        $totalOverdue = $customers->sum('overdue_payments');

        return response()->json([
            'success' => true,
            'data' => [
                'customers' => $customers,
                'stats' => [
                    'total_customers' => $customers->count(),
                    'total_credit_out' => $totalCreditOut,
                    'total_overdue' => $totalOverdue,
                ]
            ]
        ]);
    }

    /**
     * Create new customer
     */
    public function createCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = auth()->user()->karnyCustomers()->create([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'credit_limit' => $request->credit_limit ?? 0,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Client ajouté au carnet avec succès',
            'data' => $customer
        ], 201);
    }

    /**
     * Get customer details
     */
    public function showCustomer($id)
    {
        $customer = auth()->user()
            ->karnyCustomers()
            ->with('transactions')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $customer
        ]);
    }

    /**
     * Add credit transaction (client achète à crédit)
     */
    public function addCredit(Request $request, $customerId)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.001',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = auth()->user()->karnyCustomers()->findOrFail($customerId);

        // Vérifier la limite de crédit
        if ($customer->current_balance + $request->amount > $customer->credit_limit && $customer->credit_limit > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Limite de crédit dépassée. Limite: ' . $customer->credit_limit . ' TND, Actuel: ' . $customer->current_balance . ' TND'
            ], 400);
        }

        $transaction = $customer->addCredit(
            $request->amount,
            $request->description,
            $request->due_date ? \Carbon\Carbon::parse($request->due_date) : null
        );

        return response()->json([
            'success' => true,
            'message' => 'Crédit ajouté avec succès',
            'data' => [
                'transaction' => $transaction,
                'customer' => $customer->fresh()
            ]
        ], 201);
    }

    /**
     * Add payment (client paie sa dette)
     */
    public function addPayment(Request $request, $customerId)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.001',
            'method' => 'required|in:cash,mobile,bank_transfer',
            'transaction_id' => 'nullable|exists:karny_transactions,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = auth()->user()->karnyCustomers()->findOrFail($customerId);

        if ($request->amount > $customer->current_balance) {
            return response()->json([
                'success' => false,
                'message' => 'Le montant du paiement dépasse la balance actuelle'
            ], 400);
        }

        $payment = $customer->addPayment(
            $request->amount,
            $request->method,
            $request->transaction_id
        );

        return response()->json([
            'success' => true,
            'message' => 'Paiement enregistré avec succès',
            'data' => [
                'payment' => $payment,
                'customer' => $customer->fresh()
            ]
        ]);
    }

    /**
     * Get statistics
     */
    public function statistics()
    {
        $user = auth()->user();

        $totalCustomers = $user->karnyCustomers()->count();
        $activeCustomers = $user->karnyCustomers()
            ->where('current_balance', '>', 0)
            ->count();

        $totalCreditOut = $user->karnyCustomers()->sum('current_balance');

        $pendingAmount = KarnyTransaction::where('user_id', $user->id)
            ->where('type', 'credit')
            ->where('status', 'pending')
            ->sum('amount');

        $overdueAmount = KarnyTransaction::where('user_id', $user->id)
            ->where('type', 'credit')
            ->where('status', 'overdue')
            ->sum('amount');

        $thisMonthPayments = KarnyTransaction::where('user_id', $user->id)
            ->where('type', 'payment')
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'total_customers' => $totalCustomers,
                'active_customers' => $activeCustomers,
                'total_credit_out' => $totalCreditOut,
                'pending_amount' => $pendingAmount,
                'overdue_amount' => $overdueAmount,
                'this_month_payments' => $thisMonthPayments,
            ]
        ]);
    }

    /**
     * Search customer by QR code
     */
    public function searchByQR($qrCode)
    {
        $customer = auth()->user()
            ->karnyCustomers()
            ->where('qr_code', $qrCode)
            ->with('transactions')
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Client non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $customer
        ]);
    }
}
