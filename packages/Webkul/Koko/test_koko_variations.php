<?php
/**
 * Test different variations of the KOKO API request format
 */

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

$baseUrl = 'https://crowngallery.lk';
$amount = 1000.00;
$currency = 'LKR';
$pluginName = 'customapi';
$pluginVersion = '1.0.1';
$orderId = 'TEST-' . time();
$reference = $merchantId . rand(111, 999) . '-' . $orderId;
$returnUrl = $baseUrl . '/koko/success';
$cancelUrl = $baseUrl . '/koko/cancel';
$responseUrl = $baseUrl . '/koko/response';
$firstName = 'John';
$lastName = 'Doe';
$email = 'john.doe@example.com';
$mobile = '0771234567';
$description = 'Bagisto Order';
$amountFormatted = number_format($amount, 2, '.', '');

// Build dataString
$dataString = $merchantId . $amountFormatted . $currency . $pluginName . $pluginVersion . 
              $returnUrl . $cancelUrl . $orderId . $reference . $firstName . 
              $lastName . $email . $description . $apiKey . $responseUrl;

// Generate signature
$privateKeyResource = openssl_pkey_get_private($privateKey);
openssl_sign($dataString, $rawSignature, $privateKeyResource, OPENSSL_ALGO_SHA256);
$signatureEncoded = base64_encode($rawSignature);

$merchantPluginDetail = '{"pluginName":"customapi","pluginVersion":"1.0.1"}';

// Test variations
$variations = [
    'variation1_form_urlencoded' => [
        'method' => 'POST',
        'headers' => ['Content-Type: application/x-www-form-urlencoded'],
        'data' => [
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
        ],
        'encode' => 'form'
    ],
    'variation2_json' => [
        'method' => 'POST',
        'headers' => ['Content-Type: application/json'],
        'data' => [
            '_mId' => $merchantId,
            'api_key' => $apiKey,
            'merchantPluginDetail' => json_decode($merchantPluginDetail, true), // As object
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
        ],
        'encode' => 'json'
    ],
    'variation3_nested' => [
        'method' => 'POST',
        'headers' => ['Content-Type: application/json'],
        'data' => [
            'onlineOrderCreate' => [
                '_mId' => $merchantId,
                'api_key' => $apiKey,
                'merchantPluginDetail' => json_decode($merchantPluginDetail, true),
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
            ]
        ],
        'encode' => 'json'
    ],
    'variation4_pluginDetail_as_string_json' => [
        'method' => 'POST',
        'headers' => ['Content-Type: application/json'],
        'data' => [
            '_mId' => $merchantId,
            'api_key' => $apiKey,
            'merchantPluginDetail' => $merchantPluginDetail, // As JSON string
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
        ],
        'encode' => 'json'
    ],
];

$apiUrl = 'https://prodapi.paykoko.com/api/merchants/orderCreate';

foreach ($variations as $name => $variation) {
    echo "\n=== Testing $name ===\n";
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $variation['headers']);
    
    if ($variation['encode'] === 'json') {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($variation['data']));
    } else {
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($variation['data']));
    }
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode\n";
    echo "Response: " . substr($response, 0, 200) . "\n";
    
    if ($httpCode == 200 || $httpCode == 201) {
        echo "✓ SUCCESS! This variation works!\n";
        break;
    }
}

