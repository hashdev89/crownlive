# KOKO API Error Explanation

## The Error

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

## What This Means

**Translation:**
- **Status:** `CLIENT_ERROR` - There's an issue with the request you're sending
- **StatusCode:** `400` - Bad Request (invalid request format)
- **Field:** `onlineOrderCreate` - The order creation request
- **Code:** `OnlineOrder.create.merchantPluginDetail.notExists` - The `merchantPluginDetail` field is missing or not recognized

## What's Happening

1. ✅ Your code is sending the request correctly
2. ✅ All fields are being included (including `merchantPluginDetail`)
3. ❌ **But KOKO's API doesn't recognize the `merchantPluginDetail` field in the format/structure you're sending**

## Why This Happens

Possible reasons:
1. **Wrong field name** - Maybe it should be `merchant_plugin_detail` or `pluginDetail` instead
2. **Wrong format** - Maybe it needs to be sent differently (not as JSON string, or nested differently)
3. **Wrong structure** - Maybe it needs to be part of a nested object
4. **API version mismatch** - The API might have changed
5. **Account configuration** - Your merchant account might need special setup

## What We're Currently Sending

```php
'merchantPluginDetail' => '{"pluginName":"customapi","pluginVersion":"1.0.1"}'
```

As a JSON string in form-urlencoded format.

## What KOKO Support Needs to Tell You

1. **What is the correct field name?**
   - Is it `merchantPluginDetail` or something else?

2. **What is the correct format?**
   - Should it be a JSON string?
   - Should it be separate fields?
   - Should it be nested in an object?

3. **What is the exact structure?**
   - Can they provide a working example?

4. **Is there account setup required?**
   - Does the merchant account need to be configured for plugin integration?

## What to Do

### Step 1: Contact KOKO Support

Share with them:
- The error message (above)
- The file: `SHARE_WITH_KOKO_SUPPORT.md` (has complete code and curl examples)
- Ask specifically: "What is the correct format for merchantPluginDetail field?"

### Step 2: Get the Correct Format

They should provide:
- Exact field name
- Exact format/structure
- Working example

### Step 3: Update Your Code

Once you have the correct format, update:
- `KokoController.php` - Update how `merchantPluginDetail` is sent
- Test again

## Current Request Structure

We're sending (form-urlencoded):
```
merchantPluginDetail={"pluginName":"customapi","pluginVersion":"1.0.1"}
```

But the API says it "notExists" - meaning it's not finding/recognizing it.

## Possible Solutions (Until KOKO Responds)

### Try 1: Remove the Field
- Maybe it's optional?
- Test without it (but logs show it's required)

### Try 2: Different Field Name
- `merchant_plugin_detail`
- `pluginDetail`
- `merchantPluginDetails` (plural)

### Try 3: Different Format
- As separate fields: `pluginName` and `pluginVersion`
- As nested object in JSON body
- As URL-encoded differently

**But we've tried most of these already** - that's why you need KOKO support to tell you the exact format.

## Impact

**This error is blocking:**
- ❌ Order creation
- ❌ Payment processing
- ❌ Callbacks (no order = no callback)

**Nothing will work until this is fixed.**

## Summary

**The Error:** API doesn't recognize `merchantPluginDetail` field
**The Cause:** Unknown format/structure required by API
**The Fix:** Get correct format from KOKO support
**The Blocker:** This must be fixed before anything else works

## Next Action

**Contact KOKO Support immediately** with:
1. This error message
2. `SHARE_WITH_KOKO_SUPPORT.md` file
3. Request: "What is the correct format for merchantPluginDetail?"
