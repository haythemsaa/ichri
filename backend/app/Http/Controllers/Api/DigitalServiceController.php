<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DigitalServiceTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DigitalServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get available services
     */
    public function services()
    {
        $services = [
            [
                'id' => 'mobile_topup',
                'name' => 'Recharge Mobile',
                'icon' => '📱',
                'providers' => [
                    ['id' => 'ooredoo', 'name' => 'Ooredoo', 'amounts' => [5, 10, 20, 30]],
                    ['id' => 'orange', 'name' => 'Orange', 'amounts' => [5, 10, 20, 30]],
                    ['id' => 'tunisie_telecom', 'name' => 'Tunisie Telecom', 'amounts' => [5, 10, 20, 30]],
                ],
                'commission_rate' => 3.0, // 3%
            ],
            [
                'id' => 'electricity_bill',
                'name' => 'Facture STEG',
                'icon' => '⚡',
                'providers' => [
                    ['id' => 'steg', 'name' => 'STEG'],
                ],
                'commission_rate' => 1.5,
            ],
            [
                'id' => 'water_bill',
                'name' => 'Facture SONEDE',
                'icon' => '💧',
                'providers' => [
                    ['id' => 'sonede', 'name' => 'SONEDE'],
                ],
                'commission_rate' => 1.5,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $services
        ]);
    }

    /**
     * Process service transaction
     */
    public function process(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_type' => 'required|in:mobile_topup,electricity_bill,water_bill,internet_bill,phone_bill,game_card',
            'provider' => 'required|string',
            'recipient_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Calculate commission (example: 3%)
        $commissionRate = $this->getCommissionRate($request->service_type, $request->provider);
        $commission = ($request->amount * $commissionRate) / 100;

        // Create transaction
        $transaction = DigitalServiceTransaction::create([
            'user_id' => auth()->id(),
            'service_type' => $request->service_type,
            'provider' => $request->provider,
            'recipient_number' => $request->recipient_number,
            'amount' => $request->amount,
            'commission' => $commission,
            'status' => 'pending',
        ]);

        // Process with provider (simulated for now)
        try {
            // In production, integrate with actual provider APIs
            $result = $this->processWithProvider($transaction);

            if ($result['success']) {
                $transaction->update([
                    'status' => 'completed',
                    'external_ref' => $result['reference'],
                    'completed_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Transaction réussie',
                    'data' => [
                        'transaction' => $transaction,
                        'commission_earned' => $commission,
                    ]
                ]);
            } else {
                $transaction->update([
                    'status' => 'failed',
                    'error_message' => $result['error'],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Transaction échouée: ' . $result['error']
                ], 400);
            }
        } catch (\Exception $e) {
            $transaction->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get transaction history
     */
    public function history(Request $request)
    {
        $transactions = DigitalServiceTransaction::where('user_id', auth()->id())
            ->when($request->service_type, function($q) use ($request) {
                $q->where('service_type', $request->service_type);
            })
            ->when($request->status, function($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Get statistics
     */
    public function statistics()
    {
        $user = auth()->user();

        $todayTransactions = DigitalServiceTransaction::where('user_id', $user->id)
            ->today()
            ->completed()
            ->count();

        $todayCommission = DigitalServiceTransaction::where('user_id', $user->id)
            ->today()
            ->completed()
            ->sum('commission');

        $monthTransactions = DigitalServiceTransaction::where('user_id', $user->id)
            ->thisMonth()
            ->completed()
            ->count();

        $monthCommission = DigitalServiceTransaction::where('user_id', $user->id)
            ->thisMonth()
            ->completed()
            ->sum('commission');

        return response()->json([
            'success' => true,
            'data' => [
                'today' => [
                    'transactions' => $todayTransactions,
                    'commission' => $todayCommission,
                ],
                'this_month' => [
                    'transactions' => $monthTransactions,
                    'commission' => $monthCommission,
                ]
            ]
        ]);
    }

    /**
     * Helper methods
     */
    private function getCommissionRate($serviceType, $provider)
    {
        // In production, get from database
        $rates = [
            'mobile_topup' => 3.0,
            'electricity_bill' => 1.5,
            'water_bill' => 1.5,
            'internet_bill' => 2.0,
            'phone_bill' => 2.0,
            'game_card' => 5.0,
        ];

        return $rates[$serviceType] ?? 2.0;
    }

    private function processWithProvider($transaction)
    {
        // Simulated provider processing
        // In production, integrate with actual APIs:
        // - Ooredoo/Orange/TT API for mobile topup
        // - STEG API for electricity
        // - SONEDE API for water
        // etc.

        // For now, simulate success
        return [
            'success' => true,
            'reference' => 'EXT-' . strtoupper(\Illuminate\Support\Str::random(10)),
        ];
    }
}
