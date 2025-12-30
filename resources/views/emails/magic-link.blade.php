<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Magic Login Link</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2>Hello, {{ $user->name }}!</h2>
        
        <p>You requested a magic link to login to your account. Click the button below to login:</p>
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $url }}" 
               style="display: inline-block; padding: 12px 30px; background-color: #4F46E5; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Login to Your Account
            </a>
        </div>
        
        <p style="color: #666; font-size: 14px;">
            This link will expire in 30 minutes. If you didn't request this link, you can safely ignore this email.
        </p>
        
        <p style="color: #999; font-size: 12px; border-top: 1px solid #eee; padding-top: 20px; margin-top: 30px;">
            If the button doesn't work, copy and paste this URL into your browser:<br>
            <a href="{{ $url }}" style="color: #4F46E5;">{{ $url }}</a>
        </p>
    </div>
</body>
</html>
