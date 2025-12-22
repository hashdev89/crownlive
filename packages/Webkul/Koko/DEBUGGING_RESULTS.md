# KOKO Payment API Debugging Results

## Test Results Summary

### What We Tested:
1. ✅ Private key format - **WORKS** (key loads successfully)
2. ✅ Signature generation - **WORKS** (signature is generated)
3. ⚠️ Signature verification with public key - **FAILED** (but this might be expected if using different key pair)
4. ❌ API Request - **FAILS** with validation error

### API Response:
```json
{
  "status": "CLIENT_ERROR",
  "statusCode": 400,
  "validationFailures": [
    {
      "field": "onlineOrderCreate",
      "code": "OnlineOrder.create.merchantPluginDetail.notExists"
    }
  ]
}
```

### What We Discovered:

1. **API Only Accepts Form-URLEncoded**
   - JSON requests return 415 (Unsupported Media Type)
   - Must use `application/x-www-form-urlencoded`

2. **merchantPluginDetail Field Issue**
   - API requires this field but doesn't recognize it in any format we tried:
     - As JSON string: `{"pluginName":"customapi","pluginVersion":"1.0.1"}`
     - URL-encoded JSON string
     - Different field names (merchantplugindetail, merchant_plugin_detail, pluginDetail)
     - Without the field (still gets same error)

3. **Signature Generation Works**
   - Private key loads correctly
   - Signature is generated successfully
   - Data string is built in correct order

### Tested Variations:
- ✅ Standard form-urlencoded with merchantPluginDetail as JSON string
- ✅ URL-encoded JSON string
- ✅ Without merchantPluginDetail field
- ✅ Different field name variations
- ✅ JSON content-type (rejected with 415)

## Possible Issues:

### 1. API Documentation Mismatch
The API might expect:
- A different field structure
- Nested data format
- Different authentication method
- Additional headers

### 2. Account/Configuration Issue
- Merchant account might not be fully activated
- API access might need to be enabled for your account
- Plugin registration might be required first

### 3. Signature Verification Failure
The signature verification with the public key failed, which suggests:
- The public key provided might not match the private key
- Or the signature format is incorrect
- However, this might be expected if they're from different key pairs

## Next Steps:

### 1. Contact KOKO Support
**Required Information to Provide:**
- Merchant ID: `95d2a4277cc39434353924821c23ac4c`
- API Endpoint: `https://prodapi.paykoko.com/api/merchants/orderCreate`
- Error Code: `OnlineOrder.create.merchantPluginDetail.notExists`
- Request Format: `application/x-www-form-urlencoded`
- Sample Request (from test script output)

**Questions to Ask:**
1. What is the correct format for the `merchantPluginDetail` field?
2. Is the field name correct, or should it be different?
3. Does the merchant account need to be configured for plugin integration?
4. Is there a registration process for custom plugins?
5. Can you provide a working example request?

### 2. Check API Documentation
- Look for updated API documentation
- Check if there's a different endpoint for plugin integrations
- Verify if there's a sandbox/test environment to test with

### 3. Verify Account Status
- Confirm merchant account is active
- Check if API access is enabled
- Verify credentials are correct

## Current Implementation Status:

✅ **Working:**
- Private key loading and normalization
- Data string construction (correct order)
- Signature generation
- Request formatting

❌ **Not Working:**
- API validation (merchantPluginDetail field)
- Signature verification (might be expected)

## Files Created for Testing:

1. `test_koko_order.php` - Main test script with your credentials
2. `test_koko_variations.php` - Tests different content types
3. `test_field_variations.php` - Tests different field name formats
4. `KOKO_ORDER_CREATION.md` - Documentation
5. `TROUBLESHOOTING_UNAUTHORIZED.md` - Troubleshooting guide

## Recommendation:

**Contact KOKO Payment Support** with the error code and request details. The API is clearly expecting the `merchantPluginDetail` field but in a format or structure that's not documented or different from what we're sending.

The error message `OnlineOrder.create.merchantPluginDetail.notExists` suggests the API is looking for this field in a specific location or format that we haven't identified yet.

