<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorNotice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class VendorComplianceNotice extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param Collection<int, VendorNotice> $notices
     */
    public function __construct(
        public readonly User $user,
        public readonly Vendor $vendor,
        public readonly Collection $notices,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Action Required: Vendor Compliance Notice — E-MORS',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.vendor-compliance-notice',
        );
    }
}
