<?php

namespace App\Jobs;

use App\Mail\TaskReminderMail;
use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendTaskReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $taskId, public string $whenLabel) {}

    public function handle(): void
    {
        $task = Task::with('user')->find($this->taskId);
        if (!$task || !$task->user || !$task->user->email) return;

        Mail::to($task->user->email)->send(new TaskReminderMail($task, $this->whenLabel));
    }
}
