<?php

namespace App\Mail;

use App\Models\Market;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VendorApplicationReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly Vendor $vendor,
        public readonly Market $market,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Application Received — ' . $this->market->name . ' — E-MORS',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor-application-received',
        );
    }
}
