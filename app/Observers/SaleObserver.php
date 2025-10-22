<?php

namespace App\Observers;

use App\Models\Sale;
use App\Models\User;
use App\Services\NotificationService;
use App\Traits\AdministrativeAlertsTrait;
use Illuminate\Support\Facades\Log;

class SaleObserver
{
    use AdministrativeAlertsTrait;
    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {
        try {
            // Notify admins and managers about new sales
            $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
            
            foreach ($managersAndAdmins as $user) {
                // Don't notify the creator
                if ($user->id !== $sale->user_id) {
                    NotificationService::sendSaleCompletion(
                        $user,
                        $sale->amount,
                        $sale->client->name ?? 'Unknown Client',
                        [
                            'sale' => $sale,
                            'sale_id' => $sale->id,
                            'sale_type' => $sale->type,
                            'sale_date' => $sale->sale_date->format('Y-m-d'),
                            'created_by' => $sale->user->name,
                            'description' => $sale->description,
                        ]
                    );
                }
            }

            // Check if this is a high-value sale (threshold: $1000)
            if ($sale->amount >= 1000) {
                foreach ($managersAndAdmins as $user) {
                    NotificationService::createNotification(
                        $user,
                        'high_value_sale',
                        'High Value Sale!',
                        "New high-value sale of {$sale->amount} recorded by {$sale->user->name}",
                        [
                            'sale_id' => $sale->id,
                            'amount' => $sale->amount,
                            'client_name' => $sale->client->name ?? 'Unknown Client',
                            'sales_person' => $sale->user->name,
                            'threshold' => 1000,
                        ]
                    );
                }
                
                // Trigger high value sale alert
                $this->triggerHighValueSaleAlert(
                    $sale,
                    1000,
                    [
                        'client_name' => $sale->client->name ?? 'Unknown Client',
                        'sales_person' => $sale->user->name,
                        'sale_type' => $sale->type,
                        'sale_date' => $sale->sale_date->format('Y-m-d'),
                        'description' => $sale->description,
                    ]
                );
            }

            // Notify the sales person's manager if they have one
            $salesPerson = $sale->user;
            if ($salesPerson && $salesPerson->role === 'va') {
                $managers = User::where('role', 'manager')->get();
                foreach ($managers as $manager) {
                    NotificationService::sendSaleCompletion(
                        $manager,
                        $sale->amount,
                        $sale->client->name ?? 'Unknown Client',
                        [
                            'sale' => $sale,
                            'sale_id' => $sale->id,
                            'sale_type' => $sale->type,
                            'sale_date' => $sale->sale_date->format('Y-m-d'),
                            'sales_person' => $salesPerson->name,
                            'description' => $sale->description,
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send sale creation notification', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Sale "updated" event.
     */
    public function updated(Sale $sale): void
    {
        try {
            $changes = $sale->getChanges();
            
            // Check if sale amount was updated significantly
            if (isset($changes['amount'])) {
                $oldAmount = $sale->getOriginal('amount');
                $difference = abs($sale->amount - $oldAmount);
                
                // Notify if amount changed by more than $100
                if ($difference >= 100) {
                    $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
                    
                    foreach ($managersAndAdmins as $user) {
                        NotificationService::createNotification(
                            $user,
                            'sale_amount_updated',
                            'Sale Amount Updated',
                            "Sale amount updated from {$oldAmount} to {$sale->amount}",
                            [
                                'sale_id' => $sale->id,
                                'old_amount' => $oldAmount,
                                'new_amount' => $sale->amount,
                                'difference' => $difference,
                                'client_name' => $sale->client->name ?? 'Unknown Client',
                                'updated_by' => auth()->user()->name,
                            ]
                        );
                    }
                }
            }

            // Check if sale status changed (if there's a status field)
            if (isset($changes['status'])) {
                $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
                
                foreach ($managersAndAdmins as $user) {
                    NotificationService::createNotification(
                        $user,
                        'sale_status_updated',
                        'Sale Status Updated',
                        "Sale status changed to {$sale->status}",
                        [
                            'sale_id' => $sale->id,
                            'old_status' => $sale->getOriginal('status'),
                            'new_status' => $sale->status,
                            'client_name' => $sale->client->name ?? 'Unknown Client',
                            'amount' => $sale->amount,
                            'updated_by' => auth()->user()->name,
                        ]
                    );
                }
            }

            // Check if client information was updated
            if (isset($changes['client_id'])) {
                $managersAndAdmins = User::whereIn('role', ['admin', 'manager'])->get();
                
                foreach ($managersAndAdmins as $user) {
                    NotificationService::createNotification(
                        $user,
                        'sale_client_updated',
                        'Sale Client Updated',
                        "Client updated for sale of {$sale->amount}",
                        [
                            'sale_id' => $sale->id,
                            'old_client_id' => $sale->getOriginal('client_id'),
                            'new_client_id' => $sale->client_id,
                            'new_client_name' => $sale->client->name ?? 'Unknown Client',
                            'amount' => $sale->amount,
                            'updated_by' => auth()->user()->name,
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send sale update notification', [
                'sale_id' => $sale->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}