<?php

namespace Webkul\Onepay\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class OnepayController
{
    /**
     * Redirect to Onepay payment page
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        try {
            $cart = Cart::getCart();

            if (! $cart) {
                return redirect()->route('shop.checkout.cart.index');
            }

            // Get configuration values (explicitly pass null for channel/locale since these fields are not channel/locale based)
            $appId = core()->getConfigData('sales.payment_methods.onepay.app_id', null, null);
            $hashSalt = core()->getConfigData('sales.payment_methods.onepay.hash_salt', null, null);
            $appToken = core()->getConfigData('sales.payment_methods.onepay.app_token', null, null);

            if (empty($appId) || empty($hashSalt) || empty($appToken)) {
                session()->flash('error', 'Onepay payment configuration is incomplete. Please contact administrator.');
                return redirect()->route('shop.checkout.cart.index');
            }

            // Get customer information
            $customerFirstName = $cart->customer_first_name ?? 'Guest';
            $customerLastName = $cart->customer_last_name ?? 'User';
            $customerEmail = $cart->customer_email ?? 'guest@example.com';
            $customerPhone = $cart->billing_address->phone ?? $cart->shipping_address->phone ?? '0000000000';

            // Format amount to 2 decimal places
            $amount = number_format((float)$cart->grand_total, 2, '.', '');
            $currency = 'LKR';

            // Generate reference (must be at least 10 characters but max 21 characters)
            // Format: OP (2) + Cart ID (padded to 10) + Random (9) = 21 chars total
            $cartIdPadded = str_pad($cart->id, 10, '0', STR_PAD_LEFT);
            $randomSuffix = str_pad(rand(0, 999999999), 9, '0', STR_PAD_LEFT);
            $reference = 'OP' . $cartIdPadded . $randomSuffix;

            // Generate hash: app_id + currency + amount + hash_salt
            $hashString = $appId . $currency . $amount . $hashSalt;
            $hash = hash('sha256', $hashString);

            // Build the request payload
            $requestPayload = [
                'currency' => $currency,
                'amount' => (float)$amount,
                'app_id' => $appId,
                'reference' => $reference,
                'customer_first_name' => $customerFirstName,
                'customer_last_name' => $customerLastName,
                'customer_phone_number' => $customerPhone,
                'customer_email' => $customerEmail,
                'transaction_redirect_url' => route('onepay.callback'),
                'hash' => $hash,
                'additional_data' => 'Bagisto Order #' . $cart->id,
            ];

            // Log the request payload for debugging
            Log::info('Onepay Request Payload: ' . json_encode($requestPayload));

            // Make API request to Onepay
            $response = Http::withHeaders([
                'Authorization' => $appToken,
                'Content-Type' => 'application/json',
            ])->post('https://api.onepay.lk/v3/checkout/link/', $requestPayload);

            $result = $response->json();
            $statusCode = $response->status();

            // Log the full response for debugging
            Log::info('Onepay API Response: ' . json_encode([
                'status_code' => $statusCode,
                'response' => $result
            ]));

            // Check if we got a redirect URL
            if (isset($result['data']['gateway']['redirect_url'])) {
                return redirect($result['data']['gateway']['redirect_url']);
            } else {
                $errorMessage = $result['message'] ?? 'An unexpected error occurred while processing your payment.';
                if (isset($result['error'])) {
                    $errorMessage .= ' - ' . $result['error'];
                }
                Log::error('Onepay Redirect Error: ' . json_encode($result));
                session()->flash('error', $errorMessage);
                return redirect()->route('shop.checkout.cart.index');
            }
        } catch (\Exception $e) {
            Log::error('Onepay Redirect Error: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while processing your payment. Please try again.');
            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Handle Onepay payment callback
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function callback(Request $request)
    {
        try {
            // Get the transaction status from the request (check both input and JSON)
            $status = $request->input('status') ?? ($request->json('status') ?? null);
            $reference = $request->input('reference') ?? ($request->json('reference') ?? null);
            $transactionId = $request->input('transaction_id') ?? ($request->json('transaction_id') ?? null);
            $message = $request->input('message') ?? ($request->json('message') ?? null);

            Log::info('Onepay Callback: ' . json_encode($request->all()));

            // Prepare callback data for JavaScript events
            $onePayData = [
                'status' => $status,
                'reference' => $reference,
                'transaction_id' => $transactionId,
                'message' => $message,
            ];

            // If this is an AJAX/JSON request, return JSON response
            if ($request->wantsJson() || $request->isJson()) {
                if ($status === 'success' || $status === 'SUCCESS' || $status === 'completed') {
                    return response()->json([
                        'success' => true,
                        'message' => 'Payment completed successfully!',
                        'data' => $onePayData
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => $message ?? 'Payment was not successful.',
                        'data' => $onePayData
                    ], 400);
                }
            }

            // If status is available, handle server-side redirect
            if ($status) {
                if ($status === 'success' || $status === 'SUCCESS' || $status === 'completed') {
                    // Payment successful - redirect to success page
                    session()->flash('success', 'Payment completed successfully!');
                    return redirect()->route('shop.checkout.onepage.success');
                } else {
                    // Payment failed or cancelled - redirect to checkout onepage
                    $errorMessage = $message ?? 'Payment was cancelled or not successful. Please try again.';
                    session()->flash('error', $errorMessage);
                    return redirect()->route('shop.checkout.onepage.index');
                }
            }

            // If no status in URL, show callback page that listens for JavaScript events
            return view('onepay::callback', [
                'onePayData' => $onePayData
            ]);
        } catch (\Exception $e) {
            Log::error('Onepay Callback Error: ' . $e->getMessage());
            
            // Return JSON for AJAX requests
            if ($request->wantsJson() || $request->isJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing your payment.'
                ], 500);
            }
            
            session()->flash('error', 'An error occurred while processing your payment.');
            return redirect()->route('shop.checkout.cart.index');
        }
    }
}

