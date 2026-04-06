<?php

namespace App\Mail;

use App\Models\Stall;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StallAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly Vendor $vendor,
        public readonly Stall $stall,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Stall Has Been Assigned — E-MORS',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.stall-assigned',
        );
    }
}
