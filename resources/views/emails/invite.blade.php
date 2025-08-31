<!doctype html>
<html>
<body>
<p>Hello{{ $invitation->first_name ? ' '.$invitation->first_name : '' }},</p>
<p>You’ve been invited to join <strong>{{ $invitation->practice->name }}</strong> on ClearPractice.</p>
<p>Click the button below to accept the invitation and set your password.</p>
<p>
    <a href="{{ route('invites.show', $invitation->token) }}"
       style="background:#111827;color:#fff;padding:10px 14px;border-radius:8px;text-decoration:none">
        Accept invitation
    </a>
</p>
<p>This link will expire on {{ optional($invitation->expires_at)->timezone(config('app.timezone'))->format('d/m/Y H:i') }}.</p>
</body>
</html>
