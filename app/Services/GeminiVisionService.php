<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiVisionService
{
    private string $apiKey;
    private string $baseUrl;
    private string $model;

    public function __construct()
    {
        $this->apiKey  = (string) (config('services.gemini.api_key') ?? env('GEMINI_API_KEY', ''));
        $this->model   = (string) (config('services.gemini.model') ?? env('GEMINI_MODEL', 'gemini-3.5-flash'));
        $this->baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';
    }

    /**
     * @param  string  $mode  'monotaro' | 'others' | 'auto'
     */
    public function extractVendors(array $screenshots, string $mode = 'auto'): array
    {
        if (empty($this->apiKey)) {
            throw new \RuntimeException('GEMINI_API_KEY belum di-set di .env');
        }

        $prompt = match ($mode) {
            'monotaro' => $this->buildMonotaroPrompt(),
            'others'   => $this->buildOthersPrompt(),
            default    => $this->buildGenericPrompt(),
        };

        $parts = [['text' => $prompt]];

        foreach ($screenshots as $file) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $file->getMimeType(),
                    'data'      => base64_encode(file_get_contents($file->getRealPath())),
                ],
            ];
        }

        $payload = [
            'contents'         => [['parts' => $parts]],
            'generationConfig' => ['temperature' => 0.1],
        ];

        // Daftar model yang akan dicoba (Fallback)
        $modelsToTry = [$this->model];
        // Jika model utama bukan 1.5-flash, tambahkan sebagai cadangan
        if ($this->model !== 'gemini-1.5-flash') {
            $modelsToTry[] = 'gemini-1.5-flash';
        }

        $lastException = null;

        foreach ($modelsToTry as $currentModel) {
            // Coba maksimal 3 kali per model dengan Exponential Backoff
            $maxRetries = 3;
            $retryDelay = 2; // Mulai dari 2 detik

            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    $url = "{$this->baseUrl}/{$currentModel}:generateContent?key={$this->apiKey}";

                    $response = Http::timeout(120)
                        ->withHeaders(['Content-Type' => 'application/json'])
                        ->post($url, $payload);

                    if ($response->failed()) {
                        $body = $response->json() ?: $response->body();
                        $status = $response->status();
                        $message = data_get($body, 'error.message') ?? 'Gemini API gagal: ' . $status;

                        // Jika error 503 (High Demand) atau 429 (Too Many Requests)
                        if ($status === 503 || $status === 429 || str_contains($message, 'high demand')) {
                            Log::warning("Gemini API High Demand (Model: {$currentModel}, Attempt: {$attempt}). Mencoba lagi dalam {$retryDelay} detik.");
                            
                            if ($attempt === $maxRetries) {
                                throw new \RuntimeException($message);
                            }

                            sleep($retryDelay);
                            $retryDelay *= 2; // 2s -> 4s -> 8s
                            continue; // Lanjut ke percobaan berikutnya
                        }

                        // Jika error lain (misal API Key salah), langsung gagalkan tanpa retry
                        Log::error('Gemini API error', [
                            'status' => $status,
                            'model'  => $currentModel,
                            'mode'   => $mode,
                            'body'   => $body,
                        ]);
                        throw new \RuntimeException($message);
                    }

                    // Jika sukses, proses response
                    $text = data_get($response->json(), 'candidates.0.content.parts.0.text', '');
                    $text = trim(str_replace(['```json', '```'], '', $text));

                    $decoded = json_decode($text, true);
                    if (! is_array($decoded)) {
                        Log::warning('Gemini response bukan JSON', ['raw' => $text]);
                        return [];
                    }

                    $vendors = $decoded['vendors'] ?? $decoded;
                    return array_values(array_filter($vendors, fn ($v) => is_array($v)));

                } catch (\Throwable $e) {
                    $lastException = $e;
                    
                    // Jika ini bukan error High Demand, langsung lempar keluar loop model
                    if (!str_contains($e->getMessage(), 'high demand') && !str_contains($e->getMessage(), '503')) {
                        throw $e;
                    }
                }
            }
            
            Log::warning("Model {$currentModel} gagal setelah {$maxRetries} percobaan. Beralih ke model fallback...");
        }

        // Jika semua model dan retry gagal
        throw new \RuntimeException("Server AI sedang sibuk. Silakan coba beberapa saat lagi. Detail: " . ($lastException?->getMessage() ?? 'Unknown error'));
    }

    /* ═══════════════════════════════════════════════════════════════
       PROMPT 1: MONOTARO (khusus)
    ═══════════════════════════════════════════════════════════════ */
    private function buildMonotaroPrompt(): string
    {
        return <<<PROMPT
        Kamu adalah AI yang menganalisis screenshot dari website **monotaro.id** (Monotaro Indonesia).

        Ciri khas layout Monotaro:
        - Header ada logo "monotaro.id"
        - Ada section "Review Pesanan" dengan tabel produk (Produk | Jumlah | Waktu persiapan barang | Total Harga (Sebelum PPN))
        - Ada bagian "Rangkuman Pesanan" (kanan): Subtotal Sebelum PPN, Biaya Pengiriman, PPN
        - Ada "Total Pesanan" dalam huruf besar
        - Metode pembayaran misalnya "BCA Virtual Account"
        - Ada nomor PO dan opsi "Butuh Faktur Pajak?"

        Ekstrak data berikut dari screenshot monotaro.id:

        ═══ DATA VENDOR ═══
        - vendor_name: default "Monotaro" (atau nama toko kalau ada)
        - marketplace: "Monotaro"
        - company_name: "PT Monotaro Indonesia" (kalau terlihat)
        - store_url: URL Monotaro kalau terlihat

        ═══ RINCIAN BIAYA (angka saja, tanpa titik/koma) ═══
        - subtotal: "Subtotal Sebelum PPN"
        - shipping_cost: "Biaya Pengiriman"
        - discount_voucher: diskon kalau ada (default 0)
        - tax_ppn: "PPN" (biasanya 11% atau 12%)
        - total_before_tax: subtotal + shipping - diskon
        - total_price: "Total Pesanan" (angka besar di kanan bawah)
        - tax_note: "Exclude Tax" (karena Monotaro biasa exclude PPN)

        ═══ METODE PEMBAYARAN ═══
        - payment_method: "Virtual Account" / "Transfer Bank" / "COD"
        - payment_bank: mis. "BCA Virtual Account", "Mandiri", "BNI"

        ═══ PENGIRIMAN ═══
        - estimated_delivery: gabungan dari "Waktu persiapan barang" + kurir, mis. "4-5 Sep"

        ═══ DETAIL PRODUK (items array) ═══
        Untuk setiap baris di tabel "Review Pesanan":
        - product_name: nama produk (mis. "Kantong Plastik...")
        - variant: "-"
        - sku: "No. SKU: S034771419" → isi "S034771419"
        - quantity: kolom "Jumlah"
        - unit_price: harga satuan (dari "Rp28.900")
        - subtotal: quantity × unit_price
        - stock_status: "Ready Stock" / dll
        - weight: dari "Berat = 0.83 kg" per unit

        ═══ CATATAN ═══
        - notes: info penting (nomor PO, promo, dll)

        Balas HANYA dengan JSON MURNI (tanpa markdown):
        {
          "vendors": [
            {
              "vendor_name": "Monotaro",
              "marketplace": "Monotaro",
              "company_name": "PT Monotaro Indonesia",
              "store_url": "https://www.monotaro.id",
              "subtotal": 0,
              "shipping_cost": 0,
              "discount_voucher": 0,
              "tax_ppn": 0,
              "total_before_tax": 0,
              "total_price": 0,
              "tax_note": "Exclude Tax",
              "payment_method": "Virtual Account",
              "payment_bank": "BCA Virtual Account",
              "estimated_delivery": "...",
              "notes": "...",
              "items": [
                {
                  "product_name": "...",
                  "variant": null,
                  "sku": "...",
                  "quantity": 1,
                  "unit_price": 0,
                  "subtotal": 0,
                  "stock_status": "Ready Stock",
                  "weight": 0
                }
              ]
            }
          ]
        }

        ATURAN:
        1. Semua harga HARUS angka bulat (integer), tanpa titik/koma/Rp.
        2. Prioritas angka "Total Pesanan" sebagai total_price.
        3. Kalau beberapa screenshot, GABUNG jadi 1 vendor Monotaro (merge items).
        4. Jangan tambahkan apapun di luar JSON.
        PROMPT;
    }

    /* ═══════════════════════════════════════════════════════════════
       PROMPT 2: OTHERS (Shopee, Tokopedia, Lazada, dll)
    ═══════════════════════════════════════════════════════════════ */
    private function buildOthersPrompt(): string
    {
        return <<<PROMPT
        Kamu adalah AI yang menganalisis screenshot dari marketplace Indonesia **SELAIN Monotaro**.

        Marketplace yang didukung: **Shopee, Tokopedia, Lazada, Bukalapak, Blibli, dan lainnya**.

        Ciri khas tiap marketplace:
        - **Shopee**: warna orange, "Total Pesanan", "Voucher Toko", "Opsi Pengiriman", kurir (Reguler, Hemat Kargo)
        - **Tokopedia**: warna hijau, "Total Bayar", "Asuransi Pengiriman"
        - **Lazada**: warna biru-ungu, "Total Pembayaran"
        - **Bukalapak**: merah, "Total Harga"
        - **Blibli**: biru, "Total Pembayaran"

        Ekstrak data berikut:

        ═══ DATA VENDOR ═══
        - vendor_name: nama toko/vendor (mis. "Pusat Grosir ATK Murah Bdg")
        - marketplace: platformnya ("Shopee" / "Tokopedia" / "Lazada" / "Bukalapak" / "Blibli")
        - company_name: nama PT/CV kalau terlihat
        - store_url: URL toko kalau terlihat

        ═══ RINCIAN BIAYA (angka saja, tanpa titik/koma) ═══
        - subtotal: total harga produk sebelum ongkir
        - shipping_cost: "Ongkir" / "Biaya Pengiriman" / "Opsi Pengiriman"
        - discount_voucher: "Voucher Toko" / "Diskon" / "Kupon" (angka positif)
        - tax_ppn: "PPN" kalau ada
        - total_before_tax: subtotal + ongkir - diskon
        - total_price: "Total Pesanan" / "Total Bayar" / "Total Pembayaran"
        - tax_note: "Include Tax" atau "Exclude Tax"

        ═══ METODE PEMBAYARAN ═══
        - payment_method: "Transfer Bank" / "COD" / "QRIS" / "Virtual Account"
        - payment_bank: nama bank kalau ada (BCA, Mandiri, BNI, dll)

        ═══ PENGIRIMAN ═══
        - estimated_delivery: mis. "4-5 Sep dengan Reguler"

        ═══ DETAIL PRODUK (items array) ═══
        Untuk setiap produk:
        - product_name: nama produk lengkap
        - variant: "Variasi: Spiderman" → isi "Spiderman"
        - sku: nomor SKU kalau ada
        - quantity: jumlah
        - unit_price: harga satuan
        - subtotal: quantity × unit_price
        - stock_status: "Ready Stock" / "Habis"
        - weight: berat per unit kalau ada

        ═══ CATATAN ═══
        - notes: info penting

        Balas HANYA dengan JSON MURNI (tanpa markdown):
        {
          "vendors": [
            {
              "vendor_name": "...",
              "marketplace": "...",
              "company_name": "...",
              "store_url": "...",
              "subtotal": 0,
              "shipping_cost": 0,
              "discount_voucher": 0,
              "tax_ppn": 0,
              "total_before_tax": 0,
              "total_price": 0,
              "tax_note": "Include Tax",
              "payment_method": "...",
              "payment_bank": "...",
              "estimated_delivery": "...",
              "notes": "...",
              "items": [
                {
                  "product_name": "...",
                  "variant": "...",
                  "sku": "...",
                  "quantity": 1,
                  "unit_price": 0,
                  "subtotal": 0,
                  "stock_status": "...",
                  "weight": 0
                }
              ]
            }
          ]
        }

        ATURAN PENTING:
        1. Semua harga HARUS angka bulat (integer), tanpa titik/koma/Rp.
        2. Kalau ada beberapa screenshot dari vendor SAMA, GABUNG jadi 1 vendor.
        3. Kalau beda vendor, hasilkan multiple entries.
        4. Field yang tidak terlihat: null atau 0.
        5. Jangan tambahkan apapun di luar JSON.
        PROMPT;
    }

    /* ═══════════════════════════════════════════════════════════════
       PROMPT 3: GENERIC (fallback)
    ═══════════════════════════════════════════════════════════════ */
    private function buildGenericPrompt(): string
    {
        return <<<PROMPT
        Kamu adalah AI yang menganalisis screenshot dari marketplace Indonesia.

        Untuk SETIAP screenshot, ekstrak:
        - vendor_name, marketplace, company_name, store_url
        - subtotal, shipping_cost, discount_voucher, tax_ppn, total_before_tax, total_price, tax_note
        - payment_method, payment_bank, estimated_delivery, notes
        - items (array): product_name, variant, sku, quantity, unit_price, subtotal, stock_status, weight

        Balas HANYA dengan JSON MURNI:
        {
          "vendors": [
            {
              "vendor_name": "...",
              "marketplace": "...",
              "total_price": 0,
              "tax_note": "Include Tax",
              "items": [
                { "product_name": "...", "quantity": 1, "unit_price": 0, "subtotal": 0 }
              ]
            }
          ]
        }

        ATURAN: harga angka bulat, gabung vendor sama, jangan tambahkan apapun di luar JSON.
        PROMPT;
    }
}