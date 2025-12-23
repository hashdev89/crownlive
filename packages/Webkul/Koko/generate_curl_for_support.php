<?php
/**
 * Generate a complete CURL command with signature for KOKO Support
 * Run: php generate_curl_for_support.php
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

// Test order data
$amount = 1000.00;
$currency = 'LKR';
$pluginName = 'customapi';
$pluginVersion = '1.0.1';
$orderId = '12345';
$reference = $merchantId . '123-' . $orderId;

$baseUrl = 'https://crowngallery.lk';
$returnUrl = $baseUrl . '/koko/success';
$cancelUrl = $baseUrl . '/koko/cancel';
$responseUrl = $baseUrl . '/koko/response';

$firstName = 'John';
$lastName = 'Doe';
$email = 'john.doe@example.com';
$mobile = '0771234567';
$description = 'Bagisto Order';

$amountFormatted = number_format($amount, 2, '.', '');

// Build dataString in exact order
$dataString = $merchantId . $amountFormatted . $currency . $pluginName . $pluginVersion . 
              $returnUrl . $cancelUrl . $orderId . $reference . $firstName . 
              $lastName . $email . $description . $apiKey . $responseUrl;

// Generate signature
$privateKeyResource = openssl_pkey_get_private($privateKey);
openssl_sign($dataString, $rawSignature, $privateKeyResource, OPENSSL_ALGO_SHA256);
openssl_free_key($privateKeyResource);
$signatureEncoded = base64_encode($rawSignature);

$merchantPluginDetail = '{"pluginName":"customapi","pluginVersion":"1.0.1"}';

echo "=== KOKO Payment - CURL Command for Support ===\n\n";
echo "Copy and paste this CURL command:\n\n";
echo "curl -X POST 'https://prodapi.paykoko.com/api/merchants/orderCreate' \\\n";
echo "  -H 'Content-Type: application/x-www-form-urlencoded' \\\n";
echo "  -d '_mId=" . $merchantId . "' \\\n";
echo "  -d 'api_key=" . $apiKey . "' \\\n";
echo "  -d 'merchantPluginDetail=" . urlencode($merchantPluginDetail) . "' \\\n";
echo "  -d '_returnUrl=" . urlencode($returnUrl) . "' \\\n";
echo "  -d '_cancelUrl=" . urlencode($cancelUrl) . "' \\\n";
echo "  -d '_responseUrl=" . urlencode($responseUrl) . "' \\\n";
echo "  -d '_amount=" . $amountFormatted . "' \\\n";
echo "  -d '_currency=" . $currency . "' \\\n";
echo "  -d '_reference=" . $reference . "' \\\n";
echo "  -d '_orderId=" . $orderId . "' \\\n";
echo "  -d '_pluginName=" . $pluginName . "' \\\n";
echo "  -d '_pluginVersion=" . $pluginVersion . "' \\\n";
echo "  -d '_description=" . urlencode($description) . "' \\\n";
echo "  -d '_firstName=" . urlencode($firstName) . "' \\\n";
echo "  -d '_lastName=" . urlencode($lastName) . "' \\\n";
echo "  -d '_email=" . urlencode($email) . "' \\\n";
echo "  -d '_mobileNumber=" . $mobile . "' \\\n";
echo "  -d 'dataString=" . urlencode($dataString) . "' \\\n";
echo "  -d 'signature=" . urlencode($signatureEncoded) . "'\n\n";

echo "=== Debug Information ===\n\n";
echo "Data String (for signature):\n";
echo $dataString . "\n\n";
echo "Data String Length: " . strlen($dataString) . "\n\n";
echo "Signature (base64):\n";
echo $signatureEncoded . "\n\n";
echo "Signature Length: " . strlen($signatureEncoded) . "\n\n";
echo "Merchant Plugin Detail:\n";
echo $merchantPluginDetail . "\n\n";

echo "=== Test the Request ===\n\n";
echo "To test this request, run the curl command above.\n";
echo "Expected error (current):\n";
echo '{"status":"CLIENT_ERROR","statusCode":400,"validationFailures":[{"field":"onlineOrderCreate","code":"OnlineOrder.create.merchantPluginDetail.notExists"}]}' . "\n\n";

