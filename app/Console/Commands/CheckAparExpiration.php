<?php

namespace App\Console\Commands;

use App\Mail\AparExpirationWarning;
use App\Mail\VerifikasiMail;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CheckAparExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-apar-expiration';
     protected $description = 'Mengecek APAR yang kedaluwarsa H-30 dan mengirim email.';

    /**
     * The console command description.
     *
     * @var string
     */

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateThreshold = Carbon::now()->addDays(30);
        $today = Carbon::now();
        $customers = DB::table('tabel_master_customer')->get();
        $customersWithExpiringApar = $customers->map(function ($customer) use ($today, $dateThreshold) {
            $apars = DB::table('tabel_produk')
                ->where("kode_customer", $customer->kode_customer)
                ->where('tgl_kadaluarsa', '>=', $today)
                ->where('tgl_kadaluarsa', '<', $dateThreshold)
                ->get();
            $customer->apar = $apars;
            return $customer;
        })->filter(function ($customer) {
            return $customer->apar->isNotEmpty();
        })->values();
        $customersToNotify = $customersWithExpiringApar;
        $this->info('Hari Ini: ' . $today->toDateString());
        $this->info('Batas Atas (H-30): ' . $dateThreshold->toDateString());
        foreach ($customersToNotify as $customer) {
             if ($customer->apar->isNotEmpty()) {
                    Mail::to($customer->email)->send(new AparExpirationWarning($customer, $customer->apar));
                      $this->info("Notifikasi dikirim ke Customer: {$customer->kode_customer}");
            }
        }

    }
}
