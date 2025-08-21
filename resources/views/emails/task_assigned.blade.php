@php
  $fmt = function($d){
    if(empty($d)) return '—';
    try { return \Illuminate\Support\Carbon::parse($d)->format('d/m/Y'); }
    catch (\Throwable $e) { return $d; }
  };
  $toName = trim(($assignedTo['name'] ?? '').' '.($assignedTo['surname'] ?? ''));
  $byName = trim(($assignedBy['name'] ?? '').' '.($assignedBy['surname'] ?? ''));
@endphp
<!doctype html>
<html>
  <body style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;color:#111">
    <h2 style="margin:0 0 8px">You have a new task</h2>
    <p style="margin:0 0 12px">
      <strong>{{ e($toName ?: 'User') }}</strong>, a new task has been assigned to you.
    </p>

    <table role="presentation" style="border-collapse:collapse;width:100%;max-width:640px">
      <tr>
        <td style="padding:8px 0;width:120px;color:#555">Title</td>
        <td style="padding:8px 0"><strong>{{ e($task['title'] ?? '') }}</strong></td>
      </tr>
      <tr>
        <td style="padding:8px 0;color:#555">Assigned by</td>
        <td style="padding:8px 0">{{ e($byName ?: '—') }}</td>
      </tr>
      <tr>
        <td style="padding:8px 0;color:#555">Due date</td>
        <td style="padding:8px 0">{{ $fmt($task['due_date'] ?? null) }}</td>
      </tr>
      <tr>
        <td style="padding:8px 0;color:#555">Status</td>
        <td style="padding:8px 0">{{ e($task['status'] ?? 'todo') }}</td>
      </tr>
    </table>

    <p style="margin-top:16px">
      Open your task list: <a href="{{ url('/tasks') }}">{{ url('/tasks') }}</a>
    </p>

    <p style="color:#6b7280">This is an automated notification from ClearPractice.</p>
  </body>
</html>
