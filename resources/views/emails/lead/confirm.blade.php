<!doctype html>
<html>
  <body style="font-family: Arial, sans-serif; color:#111; line-height:1.55; padding:20px;">
    <h2 style="margin:0 0 10px;">Confirm your email</h2>
    <p style="margin:0 0 10px;">Hi {{ $lead->first_name }},</p>
    <p style="margin:0 0 14px;">
      Thanks for getting started with <strong>ClearCash</strong>.
      Please confirm your email address to continue your setup.
    </p>

    <p style="margin:20px 0;">
      <a href="{{ $confirmUrl }}"
         style="background:#14B8A6;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;display:inline-block;">
        Confirm my email
      </a>
    </p>

    <p style="margin:10px 0;">If the button doesn’t work, copy and paste this link:</p>
    <p style="word-break:break-all;"><a href="{{ $confirmUrl }}">{{ $confirmUrl }}</a></p>

    <p style="margin-top:18px;color:#555;">If you didn’t request this, you can ignore this email.</p>
  </body>
</html>
