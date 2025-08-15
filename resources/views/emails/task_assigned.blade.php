<p>Hi {{ $task->user?->name ?? 'there' }},</p>
<p>You have a new task:</p>
<ul>
  <li><strong>Title:</strong> {{ $task->title }}</li>
  <li><strong>Deadline:</strong> {{ optional($task->deadline)->format('d/m/Y') ?: '—' }}</li>
</ul>
<p>Thanks.</p>
