<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LeadConfirmMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead) {}

    public function build()
    {
        $confirmUrl = route('lead.confirm', ['token' => $this->lead->token]);
        return $this->subject('Confirm your email for ClearCash')
            ->view('emails.lead.confirm', [
                'lead' => $this->lead,
                'confirmUrl' => $confirmUrl,
            ]);
    }
}
