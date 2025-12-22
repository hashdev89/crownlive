<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to KOKO Payment…</title>
    <style>
        body {
            font-family: Arial;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: #f5f5f5;
        }
        .spinner {
            border: 4px solid #ddd;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            animation: spin 1s linear infinite;
            margin: 0 auto 15px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .loader { text-align: center; }
    </style>
</head>
<body>

<div class="loader">
    <div class="spinner"></div>
    <p>Redirecting to KOKO Payment…</p>
    <p style="font-size:12px;color:#666;">Please wait…</p>
</div>

<form id="koko_form" action="{{ $url }}" method="POST" enctype="application/x-www-form-urlencoded">
    @foreach ($fields as $k => $v)
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
    @endforeach
</form>

<!-- Debug: Show what's being sent (remove in production) -->
<script>
    console.log('KOKO Form Fields:', {
        @foreach ($fields as $k => $v)
        '{{ $k }}': '{{ strlen($v) > 100 ? substr($v, 0, 100) . "..." : $v }}',
        @endforeach
    });
</script>

<script>
    document.getElementById("koko_form").submit();
</script>

</body>
</html>
