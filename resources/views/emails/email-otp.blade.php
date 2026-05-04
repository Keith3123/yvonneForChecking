<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #fff6f6; margin: 0; padding: 0; }
        .container { max-width: 480px; margin: 40px auto; background: #fff; border-radius: 16px; padding: 32px; border: 1px solid #fde8e8; }
        .logo { font-size: 22px; font-weight: bold; color: #ec4899; margin-bottom: 24px; }
        h2 { color: #1f2937; font-size: 20px; margin-bottom: 8px; }
        p { color: #6b7280; font-size: 14px; line-height: 1.6; }
        .otp-box { background: #fdf2f8; border: 2px dashed #f9a8d4; border-radius: 12px; padding: 20px; text-align: center; margin: 24px 0; }
        .otp-code { font-size: 36px; font-weight: bold; letter-spacing: 8px; color: #ec4899; }
        .expiry { color: #9ca3af; font-size: 12px; margin-top: 8px; }
        .footer { margin-top: 24px; font-size: 12px; color: #9ca3af; border-top: 1px solid #f3f4f6; padding-top: 16px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">✿ Yvonne's</div>

        <h2>Verify Your New Email Address</h2>
        <p>You requested to update your email to <strong>{{ $newEmail }}</strong>. Use the code below to complete verification.</p>

        <div class="otp-box">
            <div class="otp-code">{{ $otp }}</div>
            <div class="expiry">This code expires in 10 minutes</div>
        </div>

        <p>If you did not request this change, please ignore this email or contact support immediately.</p>

        <div class="footer">
            &copy; {{ date('Y') }} Yvonne's. All rights reserved.
        </div>
    </div>
</body>
</html>