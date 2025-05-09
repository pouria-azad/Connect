<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class LogWalletTransaction
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(WalletTransactionCreated $event)
    {
        Log::info('Wallet Transaction:', [
            'user_id' => $event->transaction->user_id,
            'amount' => $event->transaction->amount,
            'type' => $event->transaction->type,
            'description' => $event->transaction->description,
        ]);
    }
}
