<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /**
         * id: Primary key, auto-increment.
         * user_id: Foreign key yang menghubungkan ke tabel users.
         * letter_number: Nomor surat.
         * date: Tanggal surat.
         * sender: Pengirim surat.
         * recipient: Penerima surat.
         * subject: Subjek atau judul surat.
         * content: Isi surat (bisa berupa teks panjang).
         * attachment_path: Path atau lokasi file hasil scan yang diunggah.
         * ocr_result: Hasil OCR yang disimpan dalam bentuk teks.

         */
        Schema::create('surats', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat');
            $table->string('tanggal_surat');
            $table->string('tujuan_surat');
            $table->string('pengirim_surat');
            $table->string('penerima_surat');
            $table->string('subjek_surat');
            $table->text('isi_surat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surats');
    }
};
