<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $token;
    protected string $apiUrl;

    public function __construct()
    {
        // Simpan token di .env (FONNTE_TOKEN)
        $this->token  = env('FONNTE_TOKEN', '');
        $this->apiUrl = 'https://api.fonnte.com/send';
    }

    /**
     * Kirim pesan WhatsApp.
     *
     * @param string $phone Nomor HP (format 08xxx atau 628xxx)
     * @param string $message Isi pesan
     * @return bool
     */
    public function send(string $phone, string $message): bool
    {
        if (empty($this->token)) {
            Log::warning('WhatsApp token is not set.');
            return false;
        }

        // Bersihkan nomor (ubah 08 menjadi 628)
        $phone = preg_replace('/^0/', '62', $phone);

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post($this->apiUrl, [
                'target'  => $phone,
                'message' => $message,
            ]);

            if ($response->successful()) {
                return true;
            }

            Log::error('WA Send Failed: ' . $response->body());
            return false;

        } catch (\Throwable $e) {
            Log::error('WA Error: ' . $e->getMessage());
            return false;
        }
    }
}