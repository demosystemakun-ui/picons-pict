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
        // Pastikan FONNTE_TOKEN diisi di file .env
        $this->token  = env('FONNTE_TOKEN', '');
        $this->apiUrl = 'https://api.fonnte.com/send';
    }

    /**
     * Kirim pesan WhatsApp menggunakan Fonnte.
     *
     * @param string $phone Nomor HP (format 08xxx atau 628xxx)
     * @param string $message Isi pesan
     * @return bool
     */
    public function send(string $phone, string $message): bool
    {
        if (empty($this->token)) {
            Log::warning('WhatsApp token is not set in .env.');
            return false;
        }

        // Bersihkan nomor (ubah 08 menjadi 628)
        $phone = preg_replace('/^0/', '62', $phone);
        $phone = preg_replace('/[^0-9]/', '', $phone); // Hapus spasi/tanda hubung

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->post($this->apiUrl, [
                'target'  => $phone,
                'message' => $message,
            ]);

            $json = $response->json();

            // ═══ CEK RESPONS ASLI DARI FONNTE ═══
            // Fonnte mengembalikan {"status": true/false, ...}
            if ($response->successful() && isset($json['status']) && $json['status'] === true) {
                Log::info("WA terkirim ke {$phone}: " . $response->body());
                return true;
            }

            // Jika Fonnte menolak (status false atau error)
            Log::error("WA Send Gagal ke {$phone}. Respons Fonnte: " . $response->body());
            return false;

        } catch (\Throwable $e) {
            Log::error('WA Error: ' . $e->getMessage());
            return false;
        }
    }
}