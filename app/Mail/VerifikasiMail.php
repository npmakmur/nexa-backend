<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifikasiMail extends Mailable
{
    use Queueable, SerializesModels;
    protected $nama;
    protected $code;
    protected $teks;
    protected $sub;

    public function __construct($nama, $code, $teks, $sub)
    {
       $this->nama = $nama;
       $this->code = $code;
       $this->teks = $teks;
       $this->sub = $sub;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->sub,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.emailVerifikasi',
            with: [
                'nama' => $this->nama,
                'code' => $this->code,
                'teks' => $this->teks,
                'sub' => $this->sub,
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
