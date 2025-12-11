<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #f5f5f5;
        }
        .loader {
            text-align: center;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="loader">
        <div class="spinner"></div>
        <p>Processing your payment...</p>
        <p style="font-size: 12px; color: #666;">Please wait, do not close this page.</p>
    </div>

    <script>
        // Convert the PHP array to a JSON object
        window.onePayData = @json($onePayData ?? [], JSON_UNESCAPED_SLASHES);

        // Listen for Onepay success event
        window.addEventListener("onePaySuccess", function (e) {
            const successData = e.detail;
            console.log("Payment SUCCESS (from Onepay):", successData);
            
            // Send AJAX request to update order status if needed
            if (successData.reference) {
                fetch("{{ route('onepay.callback') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: 'success',
                        reference: successData.reference,
                        transaction_id: successData.transaction_id
                    })
                }).then(() => {
                    // Redirect to success page
                    window.location.href = "{{ route('shop.checkout.onepage.success') }}";
                }).catch(() => {
                    // Still redirect even if AJAX fails
                    window.location.href = "{{ route('shop.checkout.onepage.success') }}";
                });
            } else {
                // Redirect to success page
                window.location.href = "{{ route('shop.checkout.onepage.success') }}";
            }
        });

        // Listen for Onepay fail event
        window.addEventListener("onePayFail", function (e) {
            const failData = e.detail;
            console.log("Payment FAIL (from Onepay):", failData);
            
            // Send AJAX request to log failure if needed
            if (failData.reference) {
                fetch("{{ route('onepay.callback') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: 'failed',
                        reference: failData.reference,
                        message: failData.message || 'Payment was not successful'
                    })
                }).then(() => {
                    // Redirect to checkout onepage when payment is cancelled/failed
                    window.location.href = "{{ route('shop.checkout.onepage.index') }}";
                }).catch(() => {
                    // Still redirect even if AJAX fails
                    window.location.href = "{{ route('shop.checkout.onepage.index') }}";
                });
            } else {
                // Redirect to checkout onepage when payment is cancelled/failed
                window.location.href = "{{ route('shop.checkout.onepage.index') }}";
            }
        });

        // Also handle URL parameters in case Onepay redirects with query params
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            const reference = urlParams.get('reference');
            const transactionId = urlParams.get('transaction_id');

            if (status) {
                if (status === 'success' || status === 'SUCCESS' || status === 'completed') {
                    // Payment successful
                    window.location.href = "{{ route('shop.checkout.onepage.success') }}";
                } else {
                    // Payment failed or cancelled - redirect to checkout onepage
                    window.location.href = "{{ route('shop.checkout.onepage.index') }}";
                }
            }
        });
    </script>
</body>
</html>

