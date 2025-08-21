<p>Hi {{ $task->user?->name ?? 'there' }},</p>
<p>Reminder ({{ $whenLabel }}):</p>
<ul>
  <li><strong>Task:</strong> {{ $task->title }}</li>
  <li><strong>Deadline:</strong> {{ optional($task->deadline)->format('d/m/Y') ?: '—' }}</li>
</ul>
<p>Please make sure this is on track.</p>
