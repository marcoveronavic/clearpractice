<?php

namespace App\Mail;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Task $task, public string $whenLabel) {}

    public function build()
    {
        return $this->subject('Reminder: '.$this->task->title.' ('.$this->whenLabel.')')
            ->view('emails.task_reminder');
    }
}
