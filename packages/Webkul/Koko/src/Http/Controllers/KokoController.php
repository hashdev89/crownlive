<?php

namespace Webkul\Koko\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KokoController
{
    /**
     * Redirect to KOKO payment page
     *
     * @return \Illuminate\View\View
     */
    public function redirect()
    {
        try {
            $cart = Cart::getCart();

            if (! $cart) {
                return redirect()->route('shop.checkout.cart.index');
            }

            $merchant = trim((string) core()->getConfigData('sales.payment_methods.koko.merchant_id'));
            $apiKey = trim((string) core()->getConfigData('sales.payment_methods.koko.api_key'));
            $privateKey = core()->getConfigData('sales.payment_methods.koko.private_key');
            $publicKey = core()->getConfigData('sales.payment_methods.koko.public_key');
            $mobile = core()->getConfigData('sales.payment_methods.koko.mobile');
            $password = core()->getConfigData('sales.payment_methods.koko.password');

            if (! $merchant || ! $apiKey || ! $privateKey || ! $publicKey || ! $mobile || ! $password) {
                session()->flash('error', 'KOKO payment configuration is incomplete. Please contact administrator.');
                return redirect()->route('shop.checkout.cart.index');
            }

            // Sanitize merchant ID and API key
            $merchant = trim($merchant);
            $apiKey = trim($apiKey);

            // Amount should be in decimal format (e.g., "300.00"), not cents
            $amount = number_format((float) $cart->grand_total, 2, '.', '');
            $currency = 'LKR';
            $pluginName = 'bagisto';
            $pluginVersion = '1.0.1';

            // Order ID as string
            $orderId = (string) $cart->id;
            
            // Generate reference - format: merchant + random(111-999) + '-' + orderId
            $reference = $merchant . rand(111, 999) . '-' . $orderId;
            
            // URLs for different callbacks
            $returnUrl = route('koko.success');
            $cancelUrl = route('koko.cancel');
            $responseUrl = route('koko.response');

            $customerFirstName = $cart->customer_first_name ?? 'Guest';
            $customerLastName = $cart->customer_last_name ?? 'User';
            $customerEmail = $cart->customer_email ?? 'guest@example.com';
            // Get mobile from cart addresses, fallback to config mobile, or default
            $customerMobile = ($cart->billing_address->phone ?? null) 
                ?? ($cart->shipping_address->phone ?? null) 
                ?? $mobile 
                ?? '0000000000';
            $description = 'Bagisto Order';

            // DataString order: merchant + amount + currency + pluginName + pluginVersion + 
            // returnUrl + cancelUrl + orderId + reference + firstName + lastName + email + 
            // description + apiKey + responseUrl
            $dataString = $merchant . $amount . $currency . $pluginName . 
                          $pluginVersion . $returnUrl . $cancelUrl . 
                          $orderId . $reference .
                          $customerFirstName . 
                          $customerLastName . 
                          $customerEmail . 
                          $description .
                          $apiKey . $responseUrl;

            // Create signature using RSA private key
            $signature = '';
            $privateKeyResource = openssl_pkey_get_private($privateKey);
            
            if (! $privateKeyResource) {
                Log::error('KOKO: Failed to load private key');
                session()->flash('error', 'KOKO payment configuration error. Please contact administrator.');
                return redirect()->route('shop.checkout.cart.index');
            }

            if (! openssl_sign($dataString, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256)) {
                $error = openssl_error_string();
                Log::error('KOKO: Failed to create signature - ' . $error);
                openssl_free_key($privateKeyResource);
                session()->flash('error', 'KOKO payment signature error. Please contact administrator.');
                return redirect()->route('shop.checkout.cart.index');
            }

            openssl_free_key($privateKeyResource);
            $signatureEncoded = base64_encode($signature);

            // Prepare fields for the API request - all field names must have underscores
            $fields = [
                '_mId' => $merchant,
                'api_key' => $apiKey,
                '_returnUrl' => $returnUrl,
                '_cancelUrl' => $cancelUrl,
                '_responseUrl' => $responseUrl,
                '_amount' => $amount,
                '_currency' => $currency,
                '_reference' => $reference,
                '_orderId' => $orderId,
                '_pluginName' => $pluginName,
                '_pluginVersion' => $pluginVersion,
                '_description' => $description,
                '_firstName' => $customerFirstName,
                '_lastName' => $customerLastName,
                '_email' => $customerEmail,
                '_mobileNo' => $customerMobile,
                'dataString' => $dataString,
                'signature' => $signatureEncoded,
            ];

            // Log the request for debugging (without sensitive data)
            Log::info('KOKO Payment Request', [
                'merchant' => $merchant,
                'orderId' => $orderId,
                'reference' => $reference,
                'amount' => $amount,
                'currency' => $currency,
            ]);

            // API URL - no merchant ID in path
            $apiUrl = 'https://qaapi.paykoko.com/api/merchants/orderCreate';
            
            return view('koko::redirect', [
                'url' => $apiUrl,
                'fields' => $fields
            ]);
        } catch (\Exception $e) {
            Log::error('KOKO Redirect Error: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while processing your payment. Please try again.');
            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Handle KOKO payment return URL (success/failure)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success(Request $request)
    {
        try {
            $orderId = $request->input('orderId');
            $trnId = $request->input('trnId');
            $status = $request->input('status');

            Log::info('KOKO Return URL Callback', [
                'orderId' => $orderId,
                'trnId' => $trnId,
                'status' => $status,
            ]);

            if ($status === 'SUCCESS') {
                session()->flash('success', 'Payment completed successfully!');
                return redirect()->route('shop.checkout.success');
            } else {
                session()->flash('error', 'Payment was not successful. Please try again.');
                return redirect()->route('shop.checkout.cart.index');
            }
        } catch (\Exception $e) {
            Log::error('KOKO Return URL Error: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while processing your payment.');
            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Handle KOKO payment cancel URL
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Request $request)
    {
        try {
            $orderId = $request->input('orderId');
            $trnId = $request->input('trnId');
            $status = $request->input('status');

            Log::info('KOKO Cancel URL Callback', [
                'orderId' => $orderId,
                'trnId' => $trnId,
                'status' => $status,
            ]);

            session()->flash('error', 'Payment was cancelled.');
            return redirect()->route('shop.checkout.cart.index');
        } catch (\Exception $e) {
            Log::error('KOKO Cancel URL Error: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while processing your payment.');
            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Handle KOKO payment response URL (server-to-server POST)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function response(Request $request)
    {
        try {
            $orderId = $request->input('orderId');
            $trnId = $request->input('trnId');
            $status = $request->input('status');
            $desc = $request->input('desc');
            $signature = $request->input('signature');

            Log::info('KOKO Response URL Callback (POST)', [
                'orderId' => $orderId,
                'trnId' => $trnId,
                'status' => $status,
                'desc' => $desc,
            ]);

            // Verify signature using public key
            $publicKey = core()->getConfigData('sales.payment_methods.koko.public_key');
            if ($publicKey && $signature) {
                // Data string for verification: orderId + trnId + status
                $dataString = $orderId . $trnId . $status;
                $signatureDecoded = base64_decode($signature);
                
                $publicKeyResource = openssl_pkey_get_public($publicKey);
                if ($publicKeyResource) {
                    $verified = openssl_verify($dataString, $signatureDecoded, $publicKeyResource, OPENSSL_ALGO_SHA256);
                    openssl_free_key($publicKeyResource);
                    
                    if ($verified === 1) {
                        Log::info('KOKO Response signature verified successfully');
                        // TODO: Update order status in database based on $status
                    } else {
                        Log::warning('KOKO Response signature verification failed');
                    }
                }
            }

            // Always return 200 OK to acknowledge receipt
            return response()->json(['status' => 'received'], 200);
        } catch (\Exception $e) {
            Log::error('KOKO Response URL Error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
}

