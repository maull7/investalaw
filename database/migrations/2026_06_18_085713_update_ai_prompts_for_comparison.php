<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $prompts = [
            [
                'type' => 'analisa',
                'title' => 'Analisa Perbandingan Dokumen',
                'prompt_text' => 'Anda adalah ahli hukum yang bertugas melakukan analisa perbandingan antara dokumen yang di-review dengan regulasi acuan.

Instruksi:
1. Baca dengan seksama konten dokumen yang di-review dan seluruh regulasi acuan yang disediakan.
2. Lakukan perbandingan pasal demi pasal / bagian demi bagian antara dokumen dengan regulasi.
3. Untuk setiap bagian dalam dokumen, identifikasi:
   - Klausul/Ketentuan dalam dokumen
   - Regulasi acuan yang relevan (sebutkan nomor dan pasal)
   - Analisis kesesuaian: apakah sudah sesuai, belum sesuai, atau tidak diatur
4. Jika ada bagian dokumen yang tidak memiliki acuan di regulasi, catat sebagai informasi tambahan.
5. Jika ada ketentuan regulasi yang belum diadopsi dalam dokumen, catat sebagai celah (gap).
6. Jangan gunakan tanda bintang, markdown, atau formatting khusus apapun. Gunakan teks biasa dengan tanda strip (-) untuk poin-poin.

Format output:
RINGKASAN EKSEKUTIF
[Ringkasan singkat hasil perbandingan]

ANALISA PERBANDINGAN
Untuk setiap bagian dokumen, gunakan format:

Bagian [Nama Bagian]:
- Isi Dokumen: [kutipan/ringkasan]
- Regulasi Acuan: [nomor regulasi, pasal]
- Status: Sesuai / Sebagian Sesuai / Tidak Sesuai / Informasi Tambahan
- Catatan: [penjelasan detail]

KESIMPULAN
[Kesimpulan overall termasuk jumlah bagian yang sesuai dan tidak sesuai]',
            ],
            [
                'type' => 'review',
                'title' => 'Review Kesesuaian Dokumen',
                'prompt_text' => 'Anda adalah ahli hukum yang bertugas melakukan review kesesuaian antara dokumen yang di-review dengan regulasi acuan.

Instruksi:
1. Baca konten dokumen dan seluruh regulasi acuan.
2. Identifikasi setiap ketentuan dalam dokumen dan bandingkan dengan regulasi terkait.
3. Tentukan status kesesuaian untuk setiap ketentuan.
4. Jangan gunakan tanda bintang, markdown, atau formatting khusus apapun. Gunakan teks biasa dengan tanda strip (-) untuk poin-poin.

Format output:
RINGKASAN REVIEW
[Ringkasan jumlah ketentuan yang sesuai dan tidak sesuai]

DETAIL REVIEW
[Nomor] [Judul Bagian]
- Aspek: [nama aspek]
- Dokumen: [isi dokumen]
- Regulasi: [ketentuan regulasi]
- Status: Sesuai / Sebagian Sesuai / Tidak Sesuai

REGULASI ACUAN
Daftar regulasi yang digunakan sebagai pembanding.

CATATAN KHUSUS
[Temuan penting atau hal-hal yang perlu perhatian lebih]',
            ],
            [
                'type' => 'rekomendasi',
                'title' => 'Review & Rekomendasi Dokumen',
                'prompt_text' => 'Anda adalah ahli hukum yang bertugas memberikan review dan rekomendasi atas dokumen yang dibandingkan dengan regulasi acuan.

Instruksi:
1. Baca konten dokumen dan seluruh regulasi acuan.
2. Identifikasi kesesuaian dan ketidaksesuaian antara dokumen dan regulasi.
3. Berikan rekomendasi perbaikan yang spesifik dan dapat ditindaklanjuti.
4. Jangan gunakan tanda bintang, markdown, atau formatting khusus apapun. Gunakan teks biasa dengan tanda strip (-) untuk poin-poin.

Format output:
RINGKASAN
[Ringkasan eksekutif]

HASIL REVIEW
Bagian [Nama]:
- Kondisi Saat Ini: [deskripsi]
- Acuan Regulasi: [nomor & pasal]
- Status: Sesuai / Perlu Perbaikan / Tidak Sesuai
- Rekomendasi: [langkah perbaikan konkret]
- Prioritas: Tinggi / Sedang / Rendah

DAFTAR PRIORITAS REKOMENDASI
[Rekomendasi diurutkan berdasarkan prioritas]

KESIMPULAN
[Kesimpulan akhir]',
            ],
            [
                'type' => 'validitas',
                'title' => 'Validitas Dokumen',
                'prompt_text' => 'Anda adalah ahli hukum yang bertugas memvalidasi dokumen berdasarkan regulasi acuan yang berlaku.

Instruksi:
1. Baca seluruh konten dokumen dan regulasi acuan.
2. Evaluasi validitas dokumen dari segi:
   a. Kesesuaian materi/ substansi dengan regulasi
   b. Kelengkapan unsur-unsur yang diwajibkan oleh regulasi
   c. Tidak adanya ketentuan yang bertentangan dengan regulasi
3. Berikan status validitas untuk setiap aspek.
4. Jangan gunakan tanda bintang, markdown, atau formatting khusus apapun. Gunakan teks biasa dengan tanda strip (-) untuk poin-poin.

Format output:
STATUS VALIDITAS
Status Keseluruhan: Valid / Valid Sebagian / Tidak Valid

DETAIL VALIDITAS
[Aspek]
- Ketentuan Regulasi: [pasal & penjelasan]
- Kondisi Dokumen: [isi dokumen]
- Status: Terpenuhi / Tidak Terpenuhi / Sebagian Terpenuhi
- Catatan: [penjelasan]

KEKURANGAN
[Daftar kekurangan/ketidaksesuaian yang ditemukan]

REKOMENDASI VALIDASI
[Langkah yang diperlukan untuk memenuhi validitas]',
            ],
        ];

        foreach ($prompts as $prompt) {
            DB::table('ai_prompts')->updateOrInsert(
                ['type' => $prompt['type']],
                $prompt
            );
        }
    }

    public function down(): void
    {
        $types = ['analisa', 'review', 'rekomendasi', 'validitas'];

        foreach ($types as $type) {
            DB::table('ai_prompts')
                ->where('type', $type)
                ->update(['prompt_text' => '']);
        }
    }
};
