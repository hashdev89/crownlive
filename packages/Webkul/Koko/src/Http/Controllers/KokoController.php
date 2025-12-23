<?php

namespace Webkul\Koko\Http\Controllers;

use Webkul\Checkout\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KokoController
{
    /**
     * Redirect to KOKO payment page (Browser POST form method)
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        try {
            $cart = Cart::getCart();

            if (! $cart) {
                return redirect()->route('shop.checkout.cart.index');
            }

            // Load config
            $merchant   = trim((string) core()->getConfigData('sales.payment_methods.koko.merchant_id'));
            $apiKey     = trim((string) core()->getConfigData('sales.payment_methods.koko.api_key'));
            $privateKey = core()->getConfigData('sales.payment_methods.koko.private_key');
            $publicKey  = core()->getConfigData('sales.payment_methods.koko.public_key');
            $mobileCfg  = core()->getConfigData('sales.payment_methods.koko.mobile');
            $callbackBaseUrl = trim((string) core()->getConfigData('sales.payment_methods.koko.callback_base_url'));

            if (! $merchant || ! $apiKey || ! $privateKey || ! $publicKey) {
                session()->flash('error', 'KOKO payment configuration is incomplete. Please contact administrator.');
                return redirect()->route('shop.checkout.cart.index');
            }

            // Normalize private key format (ensure proper line breaks)
            // Sometimes keys stored in DB lose their newlines
            $privateKey = str_replace(["\r\n", "\r"], "\n", $privateKey);
            if (strpos($privateKey, '-----BEGIN') === false) {
                // Key might be stored without headers, try to reconstruct
                $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" . 
                             chunk_split(str_replace(["\n", "\r", " ", "-----BEGIN RSA PRIVATE KEY-----", "-----END RSA PRIVATE KEY-----"], "", $privateKey), 64, "\n") . 
                             "-----END RSA PRIVATE KEY-----";
            }

            // Basic order / customer data
            $amount        = number_format((float) $cart->grand_total, 2, '.', '');
            $currency      = 'LKR';
            $pluginName    = 'customapi';
            $pluginVersion = '1.0.1';
            $orderId       = (string) $cart->id;
            $reference     = $merchant . rand(111, 999) . '-' . $orderId;

            // Generate callback URLs
            // If callback_base_url is configured, use it (useful for local testing with ngrok)
            // Otherwise, use the current domain
            if ($callbackBaseUrl) {
                $callbackBaseUrl = rtrim($callbackBaseUrl, '/');
                $returnUrl   = $callbackBaseUrl . '/koko/success';
                $cancelUrl   = $callbackBaseUrl . '/koko/cancel';
                $responseUrl = $callbackBaseUrl . '/koko/response';
            } else {
                $returnUrl   = route('koko.success');
                $cancelUrl   = route('koko.cancel');
                $responseUrl = route('koko.response');
            }
            
            // Log URLs for debugging (especially important for local development)
            Log::info('KOKO: Callback URLs', [
                'returnUrl' => $returnUrl,
                'cancelUrl' => $cancelUrl,
                'responseUrl' => $responseUrl,
                'callbackBaseUrl_configured' => !empty($callbackBaseUrl),
            ]);

            $firstName = $cart->customer_first_name ?? 'Guest';
            $lastName  = $cart->customer_last_name ?? 'User';
            $email     = $cart->customer_email ?? 'guest@example.com';

            // phone fallback chain
            $mobile = $cart->billing_address->phone ?? ($cart->shipping_address->phone ?? $mobileCfg ?? null);
            if ($mobile === null || strtolower((string)$mobile) === 'undefined' || trim((string)$mobile) === '') {
                $mobile = $mobileCfg ?? '0000000000';
            }
            $mobile = preg_replace('/\s+/', '', (string)$mobile);

            $description = 'Bagisto Order';

            // Build dataString EXACTLY in required order (important)
            $dataString =
                  $merchant
                . $amount
                . $currency
                . $pluginName
                . $pluginVersion
                . $returnUrl
                . $cancelUrl
                . $orderId
                . $reference
                . $firstName
                . $lastName
                . $email
                . $description
                . $apiKey
                . $responseUrl;

            // Sign with RSA private key (SHA256)
            $privateKeyResource = @openssl_pkey_get_private($privateKey);
            if (! $privateKeyResource) {
                $opensslErrors = [];
                while ($error = openssl_error_string()) {
                    $opensslErrors[] = $error;
                }
                Log::error('KOKO: Failed to load private key for signing.', [
                    'errors' => $opensslErrors,
                    'key_preview' => substr($privateKey, 0, 50) . '...',
                ]);
                session()->flash('error', 'Payment configuration error (private key). Please contact administrator.');
                return redirect()->route('shop.checkout.cart.index');
            }

            $rawSignature = '';
            $ok = openssl_sign($dataString, $rawSignature, $privateKeyResource, OPENSSL_ALGO_SHA256);
            
            if (! $ok) {
                $opensslErrors = [];
                while ($error = openssl_error_string()) {
                    $opensslErrors[] = $error;
                }
                openssl_free_key($privateKeyResource);
                Log::error('KOKO: openssl_sign failed.', [
                    'errors' => $opensslErrors,
                    'dataString_length' => strlen($dataString),
                ]);
                session()->flash('error', 'Payment signature error. Please contact administrator.');
                return redirect()->route('shop.checkout.cart.index');
            }

            openssl_free_key($privateKeyResource);
            $signatureEncoded = base64_encode($rawSignature);

            // Log signature generation for debugging
            Log::info('KOKO: Signature generated', [
                'dataString_length' => strlen($dataString),
                'signature_length' => strlen($signatureEncoded),
                'signature_preview' => substr($signatureEncoded, 0, 50) . '...',
            ]);

            // merchantPluginDetail must be a raw JSON string (no escaping)
            // Ensure exact format: {"pluginName":"customapi","pluginVersion":"1.0.1"}
            $merchantPluginDetail = '{"pluginName":"customapi","pluginVersion":"1.0.1"}';

            // Fields to be posted by the browser (exact names required by KOKO)
            // IMPORTANT: merchantPluginDetail must be included and properly formatted
            $fields = [
                '_mId'                 => $merchant,
                'api_key'              => $apiKey,
                'merchantPluginDetail' => $merchantPluginDetail, // Must be exact JSON string
                '_returnUrl'           => $returnUrl,
                '_cancelUrl'           => $cancelUrl,
                '_responseUrl'         => $responseUrl,
                '_amount'              => $amount,
                '_currency'            => $currency,
                '_reference'           => $reference,
                '_orderId'             => $orderId,
                '_pluginName'          => $pluginName,
                '_pluginVersion'       => $pluginVersion,
                '_description'         => $description,
                '_firstName'           => $firstName,
                '_lastName'            => $lastName,
                '_email'               => $email,
                '_mobileNumber'        => $mobile,
                'dataString'           => $dataString,
                'signature'            => $signatureEncoded,
            ];

            // Log the exact field being sent for debugging
            Log::info('KOKO: Preparing browser POST form', [
                'orderId' => $orderId,
                'reference' => $reference,
                'amount' => $amount,
                'merchant' => $merchant,
                'merchantPluginDetail_value' => $merchantPluginDetail,
                'merchantPluginDetail_length' => strlen($merchantPluginDetail),
                'merchantPluginDetail_in_fields' => isset($fields['merchantPluginDetail']),
                'all_field_names' => array_keys($fields),
                'dataString_length' => strlen($dataString),
                'dataString_preview' => substr($dataString, 0, 100) . '...',
                'signature_length' => strlen($signatureEncoded),
            ]);

            // KOKO endpoint (production)
            $apiUrl = 'https://prodapi.paykoko.com/api/merchants/orderCreate';

            // Return a view which contains an auto-submitting form (browser will POST to KOKO)
            return view('koko::redirect', [
                'url'    => $apiUrl,
                'fields' => $fields,
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
     * Note: KOKO will redirect the browser to this URL after payment.
     */
    public function success(Request $request)
    {
        try {
            $orderId = $request->input('orderId');
            $trnId   = $request->input('trnId');
            $status  = $request->input('status');

            Log::info('KOKO Return URL', compact('orderId', 'trnId', 'status'));

            if ($status === 'SUCCESS') {
                session()->flash('success', 'Payment completed successfully!');
                return redirect()->route('shop.checkout.success');
            }

            session()->flash('error', 'Payment was not successful. Please try again.');
            return redirect()->route('shop.checkout.cart.index');
        } catch (\Exception $e) {
            Log::error('KOKO Success Handler Error: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while processing the payment return.');
            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Handle KOKO payment cancel URL
     */
    public function cancel(Request $request)
    {
        try {
            $orderId = $request->input('orderId');
            $trnId   = $request->input('trnId');
            $status  = $request->input('status');

            Log::info('KOKO Cancel URL', compact('orderId', 'trnId', 'status'));

            session()->flash('error', 'Payment was cancelled.');
            return redirect()->route('shop.checkout.cart.index');
        } catch (\Exception $e) {
            Log::error('KOKO Cancel Handler Error: ' . $e->getMessage());
            session()->flash('error', 'An error occurred while processing the payment cancellation.');
            return redirect()->route('shop.checkout.cart.index');
        }
    }

    /**
     * Handle KOKO server-to-server response
     *
     * KOKO posts to this endpoint to notify payment outcome.
     * We verify the signature and (optionally) update order state here.
     */
    public function response(Request $request)
    {
        try {
            $orderId   = $request->input('orderId');
            $trnId     = $request->input('trnId');
            $status    = $request->input('status');
            $desc      = $request->input('desc');
            $signature = $request->input('signature');

            Log::info('KOKO Response Callback received', compact('orderId', 'trnId', 'status', 'desc'));

            $publicKey = core()->getConfigData('sales.payment_methods.koko.public_key');

            if (! $publicKey || ! $signature) {
                Log::warning('KOKO Response missing public key or signature.');
                return response()->json(['status' => 'missing_signature_or_key'], 400);
            }

            // Data string used by KOKO for response signature verification: orderId + trnId + status
            $verifyString = (string)$orderId . (string)$trnId . (string)$status;
            $signatureDecoded = base64_decode($signature);

            $publicKeyResource = @openssl_pkey_get_public($publicKey);
            if (! $publicKeyResource) {
                Log::warning('KOKO: Failed to parse public key for verification.');
                return response()->json(['status' => 'invalid_public_key'], 400);
            }

            $verified = openssl_verify($verifyString, $signatureDecoded, $publicKeyResource, OPENSSL_ALGO_SHA256);
            openssl_free_key($publicKeyResource);

            if ($verified === 1) {
                Log::info('KOKO Response signature verified successfully.');

                // ===== Example: update order status in DB (optional) =====
                // NOTE: Adjust to your Bagisto order model and status workflow.
                // The following is a safe, defensive example — uncomment & adapt if desired.
                /*
                try {
                    $order = \Webkul\Sales\Models\Order::find($orderId);
                    if ($order) {
                        // Map KOKO status to Bagisto order status/state
                        if ($status === 'SUCCESS') {
                            // Example: mark as processing/paid
                            $order->status = 'processing';
                            $order->state = 'processing';
                            $order->save();
                        } elseif ($status === 'FAILED' || $status === 'CANCELLED') {
                            $order->status = 'canceled';
                            $order->state = 'canceled';
                            $order->save();
                        }
                    } else {
                        Log::warning("KOKO Response: Order {$orderId} not found.");
                    }
                } catch (\Exception $ex) {
                    Log::error('KOKO Response Order update failed: ' . $ex->getMessage());
                }
                */

                // Acknowledge receipt
                return response()->json(['status' => 'verified'], 200);
            } else {
                Log::warning('KOKO Response signature verification failed.');
                return response()->json(['status' => 'invalid_signature'], 400);
            }
        } catch (\Exception $e) {
            Log::error('KOKO Response Error: ' . $e->getMessage());
            return response()->json(['status' => 'error'], 500);
        }
    }
}
