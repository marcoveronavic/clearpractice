<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public array $task;
    public ?array $assignedTo;
    public ?array $assignedBy;

    public function __construct(array $task, ?array $assignedTo, ?array $assignedBy)
    {
        $this->task       = $task;
        $this->assignedTo = $assignedTo;
        $this->assignedBy = $assignedBy;
    }

    public function build()
    {
        $subject = 'New task assigned: ' . ($this->task['title'] ?? 'Task');
        return $this->subject($subject)
                    ->view('emails.task_assigned');
    }
}
