<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PARDS Registration OTP</title>
</head>
<body style="margin: 0; padding: 24px; background-color: #f8fafc; font-family: Arial, sans-serif; color: #1f2937;">
    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 32px; box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);">
        <h1 style="margin-top: 0; font-size: 24px;">PARDS Registration OTP</h1>
        <p style="line-height: 1.6; margin-bottom: 16px;">Hello {{ $name }},</p>
        <p style="line-height: 1.6; margin-bottom: 16px;">
            Use the OTP below to finish creating your PARDS account for <strong>{{ $email }}</strong>.
        </p>
        <div style="margin: 24px 0; padding: 18px 24px; border-radius: 12px; background: #eff6ff; text-align: center;">
            <div style="font-size: 32px; letter-spacing: 10px; font-weight: 700; color: #1d4ed8;">{{ $otpCode }}</div>
        </div>
        <p style="line-height: 1.6; margin-bottom: 16px;">
            This code will expire in {{ $expiresInMinutes }} minute{{ $expiresInMinutes === 1 ? '' : 's' }}.
        </p>
        <p style="line-height: 1.6; margin-bottom: 0;">
            If you did not request this registration, you can safely ignore this email.
        </p>
    </div>
</body>
</html>
