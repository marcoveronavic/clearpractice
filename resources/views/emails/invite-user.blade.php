<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invitation</title>
</head>
<body>
<p>Hello {{ $inv->first_name }} {{ $inv->surname }},</p>

<p>You’ve been invited to join <strong>{{ $inv->practice->name }}</strong> on {{ config('app.name') }}.</p>

<p>
    <a href="{{ $url }}" style="display:inline-block;padding:10px 16px;text-decoration:none;border-radius:6px;background:#111;color:#fff;">
        Accept invitation
    </a>
</p>

<p>This link lets you set your password and finish setup. It expires {{ $inv->expires_at->diffForHumans() }}.</p>

<p style="color:#666">If you didn’t expect this, you can ignore this email.</p>
</body>
</html>
