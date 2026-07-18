<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LostCustomerPromotionalMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly Customer $customer) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We miss you, ' . $this->customer->name . '! Here\'s a special offer just for you',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.customers.lost-customer-promotional',
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
