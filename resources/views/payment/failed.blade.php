<!DOCTYPE html>
<html>
<head>
    <title>Payment Failed</title>
</head>
<body style="text-align:center; padding:50px; font-family:sans-serif;">
    <h1 style="color:red;">❌ Payment Failed</h1>
    <p>Something went wrong. Please try again.</p>

    <a href="{{ route('checkout') }}"
       style="padding:10px 20px; background:black; color:white; text-decoration:none;">
        Try Again
    </a>
</body>
</html>