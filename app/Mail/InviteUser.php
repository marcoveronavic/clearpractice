<?php

namespace App\Mail;

use App\Models\Invitation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InviteUser extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Invitation $inv) {}

    public function build()
    {
        $url = route('invites.show', $this->inv->token);

        return $this->subject('You’re invited to '.$this->inv->practice->name)
            ->view('emails.invite-user')
            ->with([
                'inv' => $this->inv,
                'url' => $url,
            ]);
    }
}
