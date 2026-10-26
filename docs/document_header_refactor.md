# Prompt GitHub Copilot: Refactoring Shared Document Header untuk Laravel Blade dan Excel

## Role

Bertindaklah sebagai **Senior Laravel Developer** yang berpengalaman dalam Laravel, Blade Components, Filament, Laravel Excel, dan PhpSpreadsheet.

## 1. Konteks dan file yang perlu diperiksa

Periksa terlebih dahulu struktur project dan implementasi aktual pada file berikut:

- `resources/views/purchase-orders/document.blade.php`
- `resources/views/receipt.blade.php`

Periksa juga seluruh file terkait yang digunakan untuk menghasilkan dokumen Excel, termasuk Export class, controller, action Filament, service, dan konfigurasi export yang relevan.

Jangan berasumsi bahwa aplikasi menggunakan implementasi Excel tertentu. Identifikasi library dan pola export yang benar-benar digunakan dalam project sebelum membuat perubahan.

## 2. Tujuan utama

Implementasikan arsitektur header bersama dengan prinsip:

> **Single Source of Truth untuk data perusahaan, reusable component untuk HTML/PDF, dan renderer khusus untuk Excel.**

Target:

- Informasi perusahaan tidak diduplikasi pada banyak file.
- Header memiliki identitas visual yang konsisten pada seluruh dokumen.
- Layout HTML/PDF tetap sesuai desain yang sekarang.
- Header Excel lebih rapi, proporsional, dan mudah dibaca.
- Perubahan header di masa mendatang dapat dilakukan dari satu sumber data.
- Tidak mengubah perhitungan, data transaksi, maupun business logic dokumen.

## 3. Refactoring header Blade

Buat reusable Blade component atau partial pada lokasi yang sesuai dengan konvensi project, misalnya:

`resources/views/components/documents/company-header.blade.php`

Komponen harus mendukung parameter yang diperlukan, minimal:

- `title`, misalnya `INVOICE` atau `RECEIPT`.
- `subtitle` jika memang dibutuhkan oleh dokumen tertentu.
- Data identitas perusahaan yang bersumber dari satu tempat.

Identitas perusahaan meliputi:

- Nama: PT. TANJUNG KARYA JAYA.
- Alamat: Perumahan Bumi Anugrah Sejahtera Blok B4 No.3, Rt.009/Rw.013, Kel. Kebalen, Kec. Babelan - Bekasi, Jawa Barat.
- Telepon: 0811-1020-770 dan 0856-1539-431.
- Email: officetkj@tanjungkaryajaya.co.id dan admin@tanjungkaryajaya.co.id.
- Website: tanjungkaryajaya.co.id.
- Logo yang saat ini digunakan oleh aplikasi.

### Ketentuan implementasi

1. Periksa implementasi aktual, termasuk lokasi file logo dan cara pemuatannya. Jangan mengasumsikan path logo tanpa verifikasi.
2. Pertahankan nama perusahaan, warna, tipografi, alamat, kontak, dan komposisi visual yang sudah digunakan.
3. Pindahkan markup header yang benar-benar sama ke komponen bersama.
4. Gunakan API Blade yang konsisten, misalnya `<x-documents.company-header title="INVOICE" />`, atau partial jika lebih sesuai dengan pola project.
5. Pisahkan CSS header bersama dari CSS spesifik dokumen jika aman dilakukan.
6. Jangan menambahkan definisi `@props` pada partial yang dipanggil menggunakan `@include` tanpa memastikan implementasinya sesuai.
7. Refactor `document.blade.php` dan `receipt.blade.php` agar menggunakan komponen tersebut.
8. Dokumen lain yang memiliki header identik dapat diintegrasikan setelah diperiksa.
9. Jangan memaksakan satu markup untuk bagian yang memang berbeda secara fungsional.

## 4. Perbaikan header Excel

Telusuri alur lengkap export Excel dari action atau controller hingga workbook dihasilkan.

Identifikasi penyebab aktual ketidakrapian header, termasuk kemungkinan:

- Lebar kolom tidak proporsional.
- Tinggi baris tidak memadai.
- Merge cells yang salah atau berlebihan.
- Posisi dan ukuran logo.
- Alignment horizontal dan vertikal.
- Teks alamat atau kontak yang terpotong.
- Border, font, warna, dan spacing yang tidak konsisten.
- Print area, page setup, scaling, serta pengulangan baris header ketika dicetak, jika relevan.

Kemudian implementasikan perbaikan berdasarkan library yang benar-benar digunakan.

Jika project menggunakan Laravel Excel / PhpSpreadsheet, pertimbangkan:

- `WithDrawings` untuk logo.
- `WithStyles` untuk font, alignment, border, dan fill.
- `WithColumnWidths` atau pengaturan lebar kolom melalui PhpSpreadsheet.
- Pengaturan tinggi baris dan merge cells secara eksplisit.
- Page setup, print area, dan repeating rows jika workbook dimaksudkan untuk dicetak.

Gunakan API tersebut hanya jika sesuai dengan arsitektur export yang ditemukan.

### Standar layout Excel yang diinginkan

1. Logo berada pada area tersendiri di sisi kiri header.
2. Nama perusahaan ditampilkan dengan jelas di sebelah logo.
3. Alamat, telepon, email, dan website tersusun rapi di bawah atau di sekitar nama perusahaan.
4. Tidak ada teks penting yang terpotong.
5. Alignment, ukuran font, jarak, dan warna konsisten.
6. Terdapat garis pemisah yang proporsional antara header dan isi dokumen.
7. Judul dokumen seperti `INVOICE` atau `RECEIPT` memiliki posisi dan hierarki visual yang jelas.
8. Header tidak mengganggu tabel data, filter, sorting, formula, maupun struktur worksheet.
9. Lebar kolom dan tinggi baris disesuaikan dengan isi aktual.
10. Jika dokumen ditujukan untuk pencetakan, pastikan header dan isi dokumen tetap terbaca ketika diekspor atau dicetak.

Jangan memaksakan pixel-perfect layout HTML ke Excel. Keseragaman yang diutamakan adalah identitas perusahaan, informasi, warna, dan hierarki visual; struktur layout boleh berbeda mengikuti karakteristik setiap format.

## 5. Arsitektur data yang disarankan

Gunakan satu sumber data perusahaan, sesuai pola yang paling tepat untuk project saat ini.

Prioritas:

1. Gunakan konfigurasi atau service perusahaan yang sudah tersedia jika ada.
2. Jika belum ada, pertimbangkan configuration file atau class khusus yang menyediakan profil perusahaan.
3. Gunakan database hanya jika kebutuhan aplikasi memang mengharuskan data perusahaan dapat diubah secara dinamis melalui admin panel.

Hindari membuat tabel baru, migration, atau pengaturan Filament baru tanpa kebutuhan yang jelas.

Pastikan Blade component dan Excel export menggunakan sumber informasi yang sama, bukan mendefinisikan ulang alamat, telepon, email, dan nama perusahaan secara terpisah.

Jangan mencampur data perusahaan dengan data transaksi seperti nomor invoice, tanggal, nama klien, atau total pembayaran. Informasi transaksi harus tetap berasal dari record dokumen masing-masing.

## 6. Batasan perubahan

Ini adalah refactoring terkontrol, bukan redesign total.

- Jangan mengubah business logic transaksi.
- Jangan mengubah perhitungan subtotal, pajak, diskon, DPP, atau grand total.
- Jangan mengubah relasi Eloquent, struktur database, maupun skema tabel tanpa alasan teknis yang kuat.
- Jangan menghapus fitur yang sudah tersedia.
- Jangan mengubah nama field, route, method, atau parameter yang digunakan bagian lain tanpa memastikan semua dependensi diperbarui.
- Pertahankan layout, warna, ukuran, dan komposisi dokumen HTML/PDF semirip mungkin dengan kondisi sebelum refactoring.
- Hindari duplikasi CSS dan markup baru.
- Jangan menambahkan dependency baru jika library yang tersedia sudah mencukupi.
- Pastikan file logo dapat dimuat pada lingkungan development maupun production.
- Pertahankan kompatibilitas dengan versi Laravel, PHP, Filament, dan library export yang terpasang.

## 7. Prosedur pengerjaan

### Tahap A — Audit

- Baca kedua Blade template secara lengkap.
- Identifikasi bagian header dan CSS yang duplikatif.
- Telusuri export Excel beserta seluruh dependensinya.
- Identifikasi library, versi, serta penyebab masalah layout.
- Sampaikan ringkasan temuan sebelum melakukan perubahan besar.

### Tahap B — Implementasi

- Buat sumber data perusahaan bersama.
- Buat reusable Blade component atau partial.
- Refactor template terkait.
- Perbaiki layout header Excel menggunakan mekanisme yang sesuai.
- Pertahankan bagian dokumen yang tidak berkaitan dengan header.
- Periksa seluruh pemanggil komponen dan export yang terdampak.

### Tahap C — Validasi

Jalankan pemeriksaan yang relevan dan tersedia dalam project, seperti:

- Pemeriksaan sintaks PHP.
- Pemeriksaan Blade dan konfigurasi terkait.
- Unit test atau feature test yang relevan.
- Pengujian render dokumen HTML/PDF.
- Pengujian export Excel dan pemeriksaan hasil workbook jika memungkinkan.

Pastikan:

- Header tampil benar pada kedua dokumen.
- Tidak terjadi error karena parameter komponen yang hilang.
- Logo tidak rusak atau hilang.
- Informasi perusahaan konsisten.
- Isi, perhitungan, dan data transaksi tidak berubah.
- Excel tidak memiliki teks penting yang terpotong atau layout yang tumpang tindih.
- Tidak ada regression pada export atau dokumen lain yang menggunakan komponen terkait.

Jangan menyatakan pengujian berhasil jika pengujian tersebut belum benar-benar dijalankan.

### Tahap D — Laporan akhir

Setelah implementasi selesai, laporkan:

1. Penyebab utama masalah header Excel.
2. File yang dibuat dan diubah.
3. Penjelasan arsitektur reusable header yang diterapkan.
4. Perbaikan spesifik yang dilakukan pada Excel.
5. Pemeriksaan dan pengujian yang berhasil dijalankan.
6. Pengujian yang belum bisa dilakukan beserta alasannya.
7. Langkah manual untuk memverifikasi hasil pada aplikasi.

## 8. Instruksi penting

Utamakan membaca dan memahami implementasi yang ada sebelum menulis kode.

Jangan mengarang nama class, method, route, dependency, atau struktur file yang belum diverifikasi.

Jika ditemukan beberapa pilihan arsitektur, pilih solusi paling sederhana yang sesuai dengan project saat ini.

Jika ada risiko perubahan akan merusak layout dokumen yang sudah stabil, pisahkan perubahan menjadi tahap kecil dan validasi setiap tahap sebelum melanjutkan.

## Target akhir

Header perusahaan yang reusable, konsisten, mudah dipelihara, dan tampil profesional pada HTML/PDF serta Excel, tanpa menduplikasi source code atau merusak fungsionalitas yang sudah ada.
