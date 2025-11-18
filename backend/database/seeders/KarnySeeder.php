<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\KarnyCustomer;
use App\Models\KarnyTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KarnySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔥 Seeding Karny (credit customers)...');

        // Get first 3 grocers
        $grocers = User::role('grocer')->limit(3)->get();

        if ($grocers->isEmpty()) {
            $this->command->warn('No grocers found. Skipping Karny seeding.');
            return;
        }

        $customerNames = [
            'Ahmed Ben Ali',
            'Mohamed Trabelsi',
            'Fatma Gharbi',
            'Salah Ben Salah',
            'Leila Mansour',
            'Karim Bouazizi',
            'Amira Sassi',
            'Youssef Benothman',
            'Samia Jalloul',
            'Hichem Chebbi',
        ];

        $totalCustomers = 0;
        $totalTransactions = 0;

        foreach ($grocers as $grocer) {
            // Create 5-8 customers per grocer
            $numCustomers = rand(5, 8);

            for ($i = 0; $i < $numCustomers; $i++) {
                if (empty($customerNames)) break;

                $name = array_shift($customerNames);

                $customer = KarnyCustomer::create([
                    'user_id' => $grocer->id,
                    'name' => $name,
                    'phone' => '+216' . rand(20000000, 99999999),
                    'qr_code' => 'KARNY-' . strtoupper(Str::random(12)),
                    'credit_limit' => collect([500, 1000, 1500, 2000])->random(),
                    'current_balance' => 0,
                ]);

                $totalCustomers++;

                // Create 2-5 transactions per customer
                $numTransactions = rand(2, 5);

                for ($j = 0; $j < $numTransactions; $j++) {
                    $isCredit = rand(0, 100) > 40; // 60% credits, 40% payments

                    if ($isCredit) {
                        // Add credit
                        $amount = rand(10, 150);
                        $dueDate = now()->addDays(rand(7, 30));

                        $transaction = KarnyTransaction::create([
                            'karny_customer_id' => $customer->id,
                            'user_id' => $grocer->id,
                            'type' => 'credit',
                            'amount' => $amount,
                            'description' => collect([
                                'Pain, lait, café',
                                'Cigarettes et boissons',
                                'Produits d\'épicerie',
                                'Pain et fromage',
                                'Café et sucre',
                            ])->random(),
                            'due_date' => $dueDate,
                            'status' => collect(['pending', 'pending', 'overdue'])->random(),
                        ]);

                        // Update customer balance
                        $customer->increment('current_balance', $amount);
                    } else {
                        // Add payment
                        if ($customer->current_balance > 0) {
                            $amount = rand(10, min(100, $customer->current_balance));

                            KarnyTransaction::create([
                                'karny_customer_id' => $customer->id,
                                'user_id' => $grocer->id,
                                'type' => 'payment',
                                'amount' => $amount,
                                'status' => 'completed',
                                'paid_at' => now()->subDays(rand(1, 15)),
                            ]);

                            // Update customer balance
                            $customer->decrement('current_balance', $amount);
                        }
                    }

                    $totalTransactions++;
                }

                // Refresh customer balance
                $customer->refresh();
            }
        }

        $this->command->info("✅ Created {$totalCustomers} Karny customers with {$totalTransactions} transactions");
    }
}
