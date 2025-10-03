<?php

namespace App\Jobs;

use App\Mail\AparExpirationWarning; // Ganti dengan nama Mailable Anda
use App\Models\Apar; // Ganti dengan model APAR Anda
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendAparExpirationNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $apar;

    /**
     * Buat instance job baru.
     * @param Apar $apar
     */
    public function __construct($apar)
    {
        $this->apar = $apar;
    }

    /**
     * Jalankan job.
     */
    public function handle(): void
    {
        // Ganti 'customer@example.com' dengan logika pengambilan email customer dari relasi APAR
        $recipientEmail = $this->apar->customer->email ?? 'admin@example.com';
        
        Mail::to($recipientEmail)->send(new AparExpirationWarning($this->apar));
    }
}