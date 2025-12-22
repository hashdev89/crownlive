<?php
/**
 * KOKO Payment Order Creation Test Script
 * 
 * This script tests the order creation with your actual credentials
 * Run: php test_koko_order.php
 */

// Your credentials
$merchantId = '95d2a4277cc39434353924821c23ac4c';
$apiKey = '3jR7Sa2ET98NTtcFu5LotCm6Ok6UzZdE';

$privateKey = '-----BEGIN RSA PRIVATE KEY-----
MIICWwIBAAKBgH2KHR3lZCxzQNja17USpTN5za+bnaJoHjSKvs2y/da/IBNA4sAM
OPKqyQZmoe6qLUpHTqgxZ5j+CvdyXAXLxPk2siFXgpLcSwC8S+KyDkvGLI48UZSe
TLuRMhaI6EQk98q8mfOZ8bEl2Ooh2i64J9eF2Ci6/auf9Zsv4xn2ftHfAgMBAAEC
gYA4j/n+dlRkEqmtCy0VUIlOYBrkfGDRw8eYNMszMupbz1FMW3fOv7MI4xof4C4l
slclmGtALMJYlF2sj8IWenEMlkJcNV9sXTL2FfsO6uBMJ9Yyiyr8+O9ERIk02Qsw
ShkOlYH7zHIB2anC83QRyvzBZyp6G/2BQ2Qshh5NTX8RAQJBAMAQTn+al2Xiu5nt
BXHH5YiiBcj8n7qmQGA4Jq9QLCR8fjbJ3faQadM6S3tJnilDIP8pAMeRB5D+gHbD
TPkqqy8CQQCnVJtcAGGWLdecJzJfAeHVwuZ87LXg1tkRpgB58wKpxciMDNSp2WvX
r6RK84/xL6ab8YVAIzbzYg4Ug3A5odhRAkAxq8XxYFSpR+MGovLWg0EMfgKLATJ5
/gcGG1991XklEoE9wCVEYALOWvQsdVSPDUpaUwtdkVdomzkz/bxJcEyHAkEAnCj/
nIrWh/tcXuTNw5DUHFR6GlgnHSAlEK1lgGnkMGDe2qUWyzSXyoCmyTQpP6OCz8JE
4yh6HgWCsm5AC+kZIQJAMwOua+2y7700nGAI38cPvr6pcLr/oJO1TkYHg9jp1YN8
fDbr1J009rFBvxmsKU8A2m+fvXK3Hup3u4/bGzWCVQ==
-----END RSA PRIVATE KEY-----';

// Test order data
$amount = 1000.00;
$currency = 'LKR';
$pluginName = 'customapi';
$pluginVersion = '1.0.1';
$orderId = 'TEST-' . time();
$reference = $merchantId . rand(111, 999) . '-' . $orderId;

// URLs - Using your actual domain
$baseUrl = 'https://crowngallery.lk';
$returnUrl = $baseUrl . '/koko/success';
$cancelUrl = $baseUrl . '/koko/cancel';
$responseUrl = $baseUrl . '/koko/response';

// Customer data
$firstName = 'John';
$lastName = 'Doe';
$email = 'john.doe@example.com';
$mobile = '0771234567';
$description = 'Bagisto Order';

// Format amount
$amountFormatted = number_format($amount, 2, '.', '');

echo "=== KOKO Payment Order Creation Test ===\n\n";
echo "Merchant ID: $merchantId\n";
echo "Order ID: $orderId\n";
echo "Reference: $reference\n";
echo "Amount: $amountFormatted $currency\n\n";

// Build dataString EXACTLY in required order
$dataString = $merchantId
    . $amountFormatted
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

echo "Data String (for signature):\n";
echo $dataString . "\n\n";
echo "Data String Length: " . strlen($dataString) . "\n\n";

// Verify private key format
echo "=== Private Key Validation ===\n";
$privateKeyResource = @openssl_pkey_get_private($privateKey);
if (!$privateKeyResource) {
    $errors = [];
    while ($error = openssl_error_string()) {
        $errors[] = $error;
    }
    echo "ERROR: Failed to load private key!\n";
    echo "OpenSSL Errors:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
    exit(1);
}
echo "✓ Private key loaded successfully\n";

// Get key details
$keyDetails = openssl_pkey_get_details($privateKeyResource);
echo "Key Type: " . $keyDetails['type'] . "\n";
echo "Key Bits: " . $keyDetails['bits'] . "\n\n";

// Generate signature
echo "=== Signature Generation ===\n";
$rawSignature = '';
$ok = openssl_sign($dataString, $rawSignature, $privateKeyResource, OPENSSL_ALGO_SHA256);
openssl_free_key($privateKeyResource);

if (!$ok) {
    $errors = [];
    while ($error = openssl_error_string()) {
        $errors[] = $error;
    }
    echo "ERROR: Failed to generate signature!\n";
    echo "OpenSSL Errors:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
    exit(1);
}

$signatureEncoded = base64_encode($rawSignature);
echo "✓ Signature generated successfully\n";
echo "Signature (base64): " . substr($signatureEncoded, 0, 50) . "...\n";
echo "Signature Length: " . strlen($signatureEncoded) . "\n\n";

// Verify signature with public key (optional test)
$publicKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDwDt4Q9B+MEAcxP8pPeTYGh22
lvCOxxKEwDuJPAvTtYpfiqU1Ip//njnMgWIpFcpIcqabALPrkHW8eD37SBzQ6R5l
fr01xf7lBG3bGqNXZkdXb0txnoXSmPya+B4oGqZc+KWNrKTntY3sNKD6k4tdOeoX
83rxb/gnZR5v7WP7WQIDAQAB
-----END PUBLIC KEY-----';

echo "=== Signature Verification Test ===\n";
$publicKeyResource = @openssl_pkey_get_public($publicKey);
if ($publicKeyResource) {
    $verified = openssl_verify($dataString, $rawSignature, $publicKeyResource, OPENSSL_ALGO_SHA256);
    openssl_free_key($publicKeyResource);
    if ($verified === 1) {
        echo "✓ Signature verified successfully with public key\n\n";
    } else {
        echo "✗ Signature verification FAILED!\n\n";
    }
} else {
    echo "⚠ Could not load public key for verification\n\n";
}

// Prepare POST data
// Try different formats for merchantPluginDetail
$merchantPluginDetail = '{"pluginName":"customapi","pluginVersion":"1.0.1"}';

// Try with merchantPluginDetail included (API seems to require it)
$postData = [
    '_mId' => $merchantId,
    'api_key' => $apiKey,
    'merchantPluginDetail' => $merchantPluginDetail,
    '_returnUrl' => $returnUrl,
    '_cancelUrl' => $cancelUrl,
    '_responseUrl' => $responseUrl,
    '_amount' => $amountFormatted,
    '_currency' => $currency,
    '_reference' => $reference,
    '_orderId' => $orderId,
    '_pluginName' => $pluginName,
    '_pluginVersion' => $pluginVersion,
    '_description' => $description,
    '_firstName' => $firstName,
    '_lastName' => $lastName,
    '_email' => $email,
    '_mobileNumber' => $mobile,
    'dataString' => $dataString,
    'signature' => $signatureEncoded,
];

echo "=== POST Data ===\n";
foreach ($postData as $key => $value) {
    if (in_array($key, ['dataString', 'signature'])) {
        echo "$key: " . substr($value, 0, 50) . "... (length: " . strlen($value) . ")\n";
    } else {
        echo "$key: $value\n";
    }
}
echo "\n";

// Make the API request
echo "=== Making API Request ===\n";
$apiUrl = 'https://prodapi.paykoko.com/api/merchants/orderCreate';

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_POST, true);

// Try as JSON instead of form-urlencoded
// curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
// curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

// Or try form-urlencoded (current)
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_VERBOSE, true);

// Capture verbose output
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

rewind($verbose);
$verboseLog = stream_get_contents($verbose);
fclose($verbose);

echo "HTTP Status Code: $httpCode\n";
if ($curlError) {
    echo "CURL Error: $curlError\n";
}
echo "\nResponse:\n";
echo $response . "\n\n";

if ($httpCode == 401 || $httpCode == 403) {
    echo "=== TROUBLESHOOTING ===\n";
    echo "Unauthorized error detected. Common issues:\n\n";
    echo "1. Check if private key format is correct (must include BEGIN/END lines)\n";
    echo "2. Verify the dataString is built in the exact order required\n";
    echo "3. Ensure all fields are being sent correctly\n";
    echo "4. Check if merchant ID and API key are correct\n";
    echo "5. Verify the signature is being generated correctly\n\n";
    
    echo "=== Debug Info ===\n";
    echo "Data String:\n$dataString\n\n";
    echo "Signature (first 100 chars): " . substr($signatureEncoded, 0, 100) . "\n\n";
    
    // Try alternative: check if private key needs different format
    echo "=== Testing Alternative Private Key Format ===\n";
    $privateKeyAlt = str_replace(["\r\n", "\n"], "\n", $privateKey);
    $privateKeyResource2 = @openssl_pkey_get_private($privateKeyAlt);
    if ($privateKeyResource2) {
        echo "✓ Alternative format works\n";
        openssl_free_key($privateKeyResource2);
    } else {
        echo "✗ Alternative format also fails\n";
    }
}

echo "\n=== Verbose CURL Output ===\n";
echo $verboseLog . "\n";

