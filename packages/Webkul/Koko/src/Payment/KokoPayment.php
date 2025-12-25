<?php

namespace Webkul\Koko\Payment;

use Illuminate\Support\Facades\Storage;
use Webkul\Payment\Payment\Payment;

class KokoPayment extends Payment
{
    protected $code = 'koko';

    public function getRedirectUrl()
    {
        return route('koko.redirect');
    }

    public function getTitle()
    {
        return $this->getConfigData('title') ?: 'Koko: Buy Now Pay Later';
    }

    public function getDescription()
    {
        return $this->getConfigData('description') ?: 'Pay in 3 interest free instalments with Koko.';
    }

    public function getImage()
    {
        $image = $this->getConfigData('image');

        if ($image) {
            return Storage::url($image);
        }

        return 'https://paykoko.com/img/logo1.7ff549c0.png';
    }

    /**
     * Get payment method additional information.
     *
     * @param  \Webkul\Sales\Contracts\Order|null  $order
     * @return array
     */
    public function getAdditionalDetails($order = null)
    {
        if (! $order) {
            return [];
        }

        // Get Koko transactions for this order
        $transactions = $order->transactions()
            ->where('payment_method', 'koko')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($transactions->isEmpty()) {
            return [];
        }

        $details = [];
        
        foreach ($transactions as $transaction) {
            $transactionData = $transaction->data ? json_decode($transaction->data, true) : [];
            
            $transactionInfo = [];
            
            // Koko Transaction ID (from Koko's response)
            if (isset($transactionData['trnId'])) {
                $transactionInfo[] = 'Koko Transaction ID: ' . $transactionData['trnId'];
            } elseif ($transaction->transaction_id) {
                $transactionInfo[] = 'Transaction ID: ' . $transaction->transaction_id;
            }
            
            // Status
            if ($transaction->status) {
                $statusLabel = ucfirst(strtolower($transaction->status));
                $transactionInfo[] = 'Status: ' . $statusLabel;
            }
            
            // Amount
            if ($transaction->amount) {
                $transactionInfo[] = 'Amount: ' . core()->currency($transaction->amount);
            }
            
            // Description from Koko
            if (isset($transactionData['desc'])) {
                $transactionInfo[] = 'Description: ' . $transactionData['desc'];
            }
            
            // Order ID from Koko (if available)
            if (isset($transactionData['orderId'])) {
                $transactionInfo[] = 'Koko Order ID: ' . $transactionData['orderId'];
            }
            
            if (! empty($transactionInfo)) {
                $details[] = implode(' | ', $transactionInfo);
            }
        }

        if (empty($details)) {
            return [];
        }

        return [
            'title' => 'Koko Payment Details',
            'value' => implode('<br>', $details),
        ];
    }
}
