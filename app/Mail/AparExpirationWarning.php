<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AparExpirationWarning extends Mailable
{
    use Queueable, SerializesModels;

    protected $customer;
    protected $apar;
    /**
     * Create a new message instance.
     */
    public function __construct($customer,$apar)
    {
        $this->customer = $customer;
        $this->apar = $apar;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Apar Expiration Warning',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.emailAparExpaired',
            with:[
                'customer' => $this->customer,
                'apars' => $this->apar
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
