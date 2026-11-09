# Analisis Kompatibilitas SQLite untuk SLiMS 9 "Bulian" (9.8.0)

> **Untuk siapa dokumen ini?**
> Untuk pengambil keputusan (kepala perpustakaan, manajer TI) **dan** pengembang.
> Setiap bagian teknis diikuti penjelasan sederhana bertanda **💬 Artinya bagi pengguna**.
> Bagian teknis bisa dilewati tanpa kehilangan gambaran besar.

> **Catatan metode:** seluruh angka berasal dari pencarian pola (`grep`) pada kode di repo ini. Itu perkiraan kasar, **bukan audit penuh**. Tidak ada kode yang diubah dan tidak ada pengujian SQLite yang dijalankan. Sebelum keputusan besar, angka perlu diverifikasi per modul.

---

## 1. Ringkasan Eksekutif

| Pertanyaan | Jawaban singkat |
|---|---|
| Apakah SLiMS bisa langsung memakai SQLite? | **Tidak.** Tidak ada dukungan fungsional saat ini. |
| Apakah bisa diubah ke SQLite sepenuhnya (membuang MySQL)? | **Secara teknis bisa, tetapi pekerjaannya sangat besar**, mendekati menulis ulang lapisan data. |
| Apakah itu disarankan? | **Tidak sebagai pengganti total.** Lebih aman sebagai **opsi tambahan** untuk perpustakaan kecil. |
| Risiko terbesar? | (1) Hasil pencarian katalog berubah, (2) perhitungan tanggal/denda sirkulasi bisa salah diam-diam, (3) pengguna lama kehilangan jalur upgrade, (4) database terkunci saat banyak pengguna. |

**💬 Artinya bagi pengguna:** SLiMS ditulis dengan "bahasa" khusus MySQL/MariaDB di ratusan tempat. SQLite berbicara dialek berbeda. Mengganti mesinnya bukan sekadar mencabut satu komponen. Hampir semua fitur yang menyentuh data perlu diperiksa dan disesuaikan.

---

## 2. Latar Belakang Singkat

- **SLiMS** (*SENAYAN Library Management System*) adalah aplikasi web berbasis **PHP ≥ 8.1**, memakai **MySQL 5.7 / MariaDB 10.3+**, lisensi GPL v3.
- **SQLite** adalah database berbentuk **satu file** tanpa server terpisah. Ringan dan mudah dipasang, tetapi lebih terbatas untuk banyak pengguna bersamaan dan fitur lanjutan.

**💬 Artinya bagi pengguna:** MySQL itu seperti gudang dengan petugas khusus yang melayani banyak peminta sekaligus. SQLite itu seperti lemari arsip tunggal yang praktis dibawa, tetapi hanya enak dipakai satu-dua orang pada satu waktu.

---

## 3. Status Dukungan SQLite Saat Ini

### 3.1 Temuan teknis

1. **Ada sisa kode SQLite lama, tetapi mati.**
   - `simbio2/simbio_DB/sqlite/` (`simbio_sqlite.inc.php`, `simbio_sqlite_result.inc.php`).
   - `lib/biblio_list_sqlite.inc.php`.
   - `simbio_sqlite` memanggil `sqlite_open()`, fungsi ekstensi PHP 5 yang **sudah dihapus** dari PHP modern.
   - Tidak ada file aktif yang me-*require* kode itu.
   - `biblio_list_sqlite` masih berisi `SQL_CALC_FOUND_ROWS` dan `MATCH ... AGAINST` (sintaks MySQL), jadi tidak akan jalan di SQLite.
2. **Koneksi dikunci ke MySQL.** `lib/Connection.php` memakai `dsnPrefix = 'mysql'`. Jalur MySQLi memakai `new mysqli(...)`. Wizard instalasi (`install/SLiMS.inc.php`) hanya memeriksa driver PDO `mysql` dan membuat koneksi `mysqli`.
3. **Pembuat skema (DDL) hanya untuk MySQL.** `lib/Table/Grammar/` hanya berisi `Mysql.php`.
4. **Backup memakai alat khusus MySQL:** `ifsnop/mysqldump-php` (`DB::backup()`, `admin/modules/system/backup_proc.php`).

### 💬 Artinya bagi pengguna
Ada "puing" dukungan SQLite dari masa lalu, tetapi tidak berfungsi. Instalasi saat ini hanya menerima MySQL/MariaDB. Tidak ada tombol atau pengaturan untuk beralih ke SQLite.

---

## 4. Titik Inkompatibilitas Secara Rinci

Setiap baris: **apa yang bermasalah**, **seberapa banyak**, **kenapa**, dan **dampak ke pengguna**.

### 4.1 Pencarian katalog (paling kritis)

| | |
|---|---|
| **Teknis** | `MATCH(...) AGAINST(... IN BOOLEAN MODE)` dan indeks `FULLTEXT`. Sekitar **34 query `MATCH(`** (di luar installer) dan **51 kemunculan** pola FULLTEXT/MATCH di **9 file**. Lokasi utama: `lib/biblio_list.inc.php`, `lib/biblio_list_index.inc.php`, `simbio2/simbio_UTILS/simbio_qparser.inc.php`, `admin/modules/system/content.php`. Indeks FULLTEXT ada di tabel pencarian (`title`, `author`, `topic`, `location`, `items`, `collection_types`, `labels`, dll.). |
| **Masalah** | SQLite tidak punya `MATCH ... AGAINST`. Pengganti utamanya adalah **FTS5**, yaitu tabel virtual terpisah dengan sintaks dan perilaku peringkat sendiri. Mode *boolean* (`+kata -kata "frasa"`) harus diterjemahkan. `simbio_qparser` (pembangun query pencarian) perlu ditulis ulang. |
| **Dampak** | Urutan dan jumlah hasil pencarian bisa **berbeda** dari sebelumnya. |

**💬 Artinya bagi pengguna:** Kotak pencarian di OPAC adalah fitur yang paling sering dipakai pemustaka. Setelah pindah, hasil untuk kata kunci yang sama bisa muncul dalam urutan lain atau ada yang hilang/bertambah, sampai disetel ulang dan diuji.

### 4.2 Penomoran halaman daftar (`SQL_CALC_FOUND_ROWS`)

| | |
|---|---|
| **Teknis** | **10 kemunculan**: `simbio_dbgrid.inc.php` (komponen tabel admin yang dipakai hampir semua modul), `biblio_list.inc.php`, `biblio_list_index.inc.php`, dan salinan SQLite lama. |
| **Masalah** | Tidak didukung SQLite. Harus diganti `COUNT(*)` terpisah. |
| **Dampak** | Satu perubahan di `simbio_dbgrid` memengaruhi **seluruh daftar** di panel admin. Kesalahan di sini terlihat di mana-mana, tetapi perbaikannya juga terpusat. |

**💬 Artinya bagi pengguna:** Tulisan "Halaman 1 dari 25" dan tombol ganti halaman di semua daftar admin bergantung pada fungsi ini.

### 4.3 Fungsi tanggal dan waktu (risiko diam-diam)

| | |
|---|---|
| **Teknis** | Sekitar **291 kemunculan** `NOW()`, `CURDATE`, `DATE_ADD`, `DATE_SUB`, `TIMESTAMPDIFF` di **20 file**. Tambahan: `DATE_FORMAT` (7), `UNIX_TIMESTAMP`/`FROM_UNIXTIME` (10), dan sekitar 116 pemakaian `YEAR(`, `MONTH(`, `DAY(`, `LEFT(`, `MID(`, `STR_TO_DATE`, dll. |
| **Masalah** | Fungsi-fungsi ini tidak ada atau berbeda di SQLite (`datetime('now')`, `strftime`, `julianday`). Format string berbeda. |
| **Dampak** | Perhitungan **jatuh tempo, denda, hari libur, dan laporan sirkulasi** bisa salah **tanpa pesan error**. |

**💬 Artinya bagi pengguna:** Ini risiko paling berbahaya karena tidak terlihat. Aplikasi tetap jalan, tetapi tanggal kembali atau besaran denda bisa keliru. Anggota bisa ditagih salah atau lolos dari denda.

### 4.4 Fungsi teks dan pola

| Fungsi MySQL | Jumlah (perkiraan) | Di SQLite |
|---|---|---|
| `REGEXP`, `RLIKE`, `IFNULL`, `FIND_IN_SET` (gabungan) | ~133 hit, 35 file | `REGEXP` tidak bawaan (perlu didaftarkan lewat PHP). `IFNULL` ada. `FIND_IN_SET` tidak ada (0 pemakaian terdeteksi di luar installer). |
| `CONCAT(...)` | ~19 | Tidak ada, diganti operator `\|\|`. |
| `GROUP_CONCAT` | ~9 | Ada, tetapi sintaks pemisah berbeda. |
| `LIMIT a, b` | 2 | SQLite memakai `LIMIT b OFFSET a`. (Sintaks `LIMIT a, b` sebenarnya diterima SQLite, tetapi tetap perlu verifikasi.) |
| `RAND()` | 1 | Diganti `RANDOM()`. |
| `CAST(... AS UNSIGNED)`, `SUBSTRING_INDEX`, `LPAD` | 3 | Tidak ada atau berbeda. |

**💬 Artinya bagi pengguna:** Fitur seperti filter lanjutan, penggabungan nama, dan pengurutan acak (misalnya "koleksi acak" di beranda) bisa gagal sampai disesuaikan satu per satu.

### 4.5 Sisip data tanpa duplikat

| | |
|---|---|
| **Teknis** | `INSERT IGNORE`: **29 hit** di luar installer (44 total). `REPLACE INTO`: 4. |
| **Masalah** | SQLite memakai `INSERT OR IGNORE` / `INSERT OR REPLACE`. Perilakunya mirip tetapi ada perbedaan semantik, terutama pada kunci unik dan pada penanganan kolom `NOT NULL`. |
| **Dampak** | Data duplikat atau data yang diam-diam tidak tersimpan jika perilakunya tidak sama. |

**💬 Artinya bagi pengguna:** Misalnya saat impor atau penyimpanan data pengarang dan subjek, entri bisa dobel atau terlewat tanpa peringatan.

### 4.6 Struktur database (skema)

| | |
|---|---|
| **Teknis** | **61 tabel** di `install/install.sql.php`. Memakai `ENGINE=MyISAM/InnoDB` (83 kemunculan), `AUTO_INCREMENT` (127), `ENUM(` (1), `collate utf8_unicode_ci`. Tidak ada `FOREIGN KEY` yang terdeteksi. |
| **Masalah** | Semua DDL harus ditulis ulang. `ENUM` tidak ada di SQLite. **Collation Unicode tidak ada** secara bawaan: `LIKE` dan pengurutan teks non-ASCII (nama Arab, Thai, huruf beraksen) berperilaku berbeda. |
| **Dampak** | Pencarian tidak peka huruf besar/kecil dan urutan alfabet bisa berubah, terutama untuk bahasa non-Latin. |

**💬 Artinya bagi pengguna:** Daftar pengarang atau judul berbahasa non-Latin bisa terurut tidak sesuai harapan.

### 4.7 Trigger (otomatisasi di dalam database)

| | |
|---|---|
| **Teknis** | **3 trigger** riwayat pinjaman di `install/Upgrade.inc.php`: `insert_loan_history`, `update_loan_history`, `delete_loan_history`. |
| **Masalah** | Sintaks trigger SQLite berbeda dan harus ditulis ulang dan diuji. |

**💬 Artinya bagi pengguna:** Ini yang otomatis mencatat riwayat peminjaman. Kalau tidak ikut dipindahkan dengan benar, riwayat pinjaman anggota bisa tidak tercatat.

### 4.8 Pengecekan struktur database (introspeksi)

| | |
|---|---|
| **Teknis** | `SHOW ...` (~1 hit di luar installer), `INFORMATION_SCHEMA` (6 hit), `SELECT @@sql_mode` di installer. |
| **Masalah** | Tidak ada di SQLite. Gantinya `sqlite_master` / `PRAGMA`. |

**💬 Artinya bagi pengguna:** Fitur yang "mengecek apakah tabel/kolom sudah ada" (terutama saat instal/upgrade) harus diganti.

### 4.9 Backup

| | |
|---|---|
| **Teknis** | `ifsnop/mysqldump-php` (`DB::backup()`, `backup_proc.php`). Perintah khusus MySQL seperti `SHOW CREATE TRIGGER` dan `/*!40000 ALTER TABLE ... DISABLE KEYS */`. |
| **Masalah** | Tidak berlaku untuk SQLite. |
| **Catatan positif** | Backup SQLite sebenarnya lebih sederhana (salin file), tetapi fitur di panel admin harus dibuat ulang. |

**💬 Artinya bagi pengguna:** Tombol "Backup Database" di panel admin perlu diganti. Tanpa itu, tidak ada cadangan lewat aplikasi.

### 4.10 Upgrade dari versi lama

| | |
|---|---|
| **Teknis** | `install/Upgrade.inc.php` (>1.000 baris SQL MySQL), `lib/Table/Grammar/Mysql.php`, `ALTER TABLE` (2 hit di luar installer; 85 hit total). SQLite hanya mendukung sebagian `ALTER TABLE`. |
| **Masalah** | Jalur upgrade bertahap dari versi lama harus dibuat ulang. Perubahan struktur sering butuh "buat tabel baru, salin data, hapus yang lama". |

**💬 Artinya bagi pengguna:** Perpustakaan yang sudah memakai SLiMS bertahun-tahun belum punya jalan mulus untuk berpindah ke SQLite.

---

## 5. Lapisan Akses Data (Sumber Kompleksitas)

### 5.1 Temuan teknis

- Ada **dua gaya akses** yang berjalan bersamaan:
  1. **Objek bergaya MySQLi `$dbs`**: dipakai di **±157 file**.
  2. **PDO lewat `DB::getInstance()`**: ±93 pemanggilan.
- API `$dbs` memakai `->query()`, `num_rows`, `affected_rows` (24), `insert_id` (47 total), `escape_string`. PDO SQLite tidak menyediakan semuanya dengan bentuk sama.
- Total sekitar **930 pernyataan SQL mentah** (`SELECT/INSERT/UPDATE/DELETE`) tersebar di `admin/modules`, `lib/`, `api/`.

### 5.2 Konsekuensi

Solusi realistis adalah membuat **adaptor** yang membuat PDO SQLite "meniru" API `$dbs`. Adaptor itu pekerjaan tersendiri dan sering memunculkan bug halus (tipe data, `num_rows`, escaping).

**💬 Artinya bagi pengguna:** Kode aplikasi ibarat berbicara ke database lewat dua penerjemah berbeda. Kalau mesinnya diganti, kedua penerjemah itu harus diajari bahasa baru, dan di sanalah banyak kesalahan kecil biasanya muncul.

---

## 6. Risiko dan Potensi Regresi

Skala: 🔴 tinggi, 🟠 sedang, 🟡 rendah.

| # | Risiko | Level | Penjelasan teknis | 💬 Artinya bagi pengguna |
|---|---|---|---|---|
| 1 | Hasil pencarian katalog berubah | 🔴 | FTS5 ≠ MySQL FULLTEXT: tokenisasi, stopword, ranking, boolean mode berbeda. | Pemustaka mungkin tidak menemukan buku yang biasanya muncul. |
| 2 | Angka sirkulasi salah diam-diam | 🔴 | Pemetaan fungsi tanggal tidak sempurna → kalkulasi jatuh tempo/denda keliru tanpa error. | Denda atau tanggal kembali bisa salah. |
| 3 | Tidak ada jalur migrasi data | 🔴 | Perlu alat konversi MySQL→SQLite untuk 61 tabel, termasuk trigger. | Pengguna lama berisiko kehilangan data saat pindah. |
| 4 | Penulisan bersamaan (concurrency) | 🟠 | SQLite mengunci per file saat menulis → `database is locked`. Mode WAL membantu, tetapi bukan jaminan. Lebih parah jika file di NFS/folder jaringan. | Saat banyak petugas meminjamkan buku bersamaan, bisa muncul error "database terkunci". |
| 5 | Skala data | 🟠 | Puluhan ribu hingga jutaan rekaman dengan FTS dan join banyak bisa lebih lambat daripada MariaDB yang disetel. | Aplikasi bisa melambat pada koleksi besar. |
| 6 | Plugin & tema pihak ketiga | 🟠 | Plugin yang menulis SQL MySQL langsung akan rusak. | Fitur tambahan yang sudah dipasang bisa berhenti bekerja. |
| 7 | Integrasi lain | 🟠 | OAI-PMH (`oai.php`, `oai2.php`, `lib/oaipmh`), REST API (`api/`), chat server perlu diuji. Elasticsearch (`indexing_engine/`) kemungkinan mandiri, tetapi belum diverifikasi. | Penerbitan metadata ke portal nasional/ repositori bisa terganggu. |
| 8 | Keamanan file database | 🟠 | File `.sqlite` yang berada di web root bisa diunduh langsung jika tidak diblokir. | Seluruh data anggota dan koleksi bisa bocor jika file diletakkan di tempat yang salah. |
| 9 | Dependensi server | 🟡 | Perlu `pdo_sqlite` dan SQLite dengan **FTS5**. Sebagian shared hosting tidak menjamin. | Beberapa layanan hosting tidak bisa menjalankannya. |
| 10 | Pengurutan teks non-Latin | 🟡 | Tidak ada collation `utf8_unicode_ci` bawaan. | Urutan abjad untuk bahasa non-Latin bisa berantakan. |

---

## 7. Keuntungan (Supaya Seimbang)

| Keuntungan | 💬 Artinya bagi pengguna |
|---|---|
| Tidak perlu server database terpisah | Pasang lebih mudah, cocok perpustakaan sekolah kecil atau demo. |
| Backup dan pemindahan = salin satu file | Cadangan dan pindah komputer jadi sederhana. |
| Cocok untuk Docker atau paket portabel (mirip XAMPP) | Bisa dibawa dalam flash disk atau dijalankan di laptop tanpa instalasi rumit. |
| Biaya operasional lebih rendah | Tidak perlu mengelola layanan MySQL. |

---

## 8. Sugesti Lengkap

Setiap sugesti memuat **tindakan teknis** dan **penjelasan sederhana**.

### Sugesti 1. Jangan membuang MySQL/MariaDB

- **Teknis:** Pertahankan MySQL/MariaDB sebagai driver utama. Menghapusnya memutus seluruh basis instalasi yang ada, jalur upgrade, dan dukungan koleksi besar.
- **💬 Sederhananya:** Jangan membongkar mesin yang sudah dipakai ribuan perpustakaan hanya demi satu opsi baru. Tambahkan pilihan, jangan menghapus yang lama.

### Sugesti 2. Jadikan SQLite driver opsional lewat lapisan abstraksi

- **Teknis:**
  - Perluas `lib/Connection.php` agar `driver` benar-benar menentukan DSN (bukan hanya `mysql`).
  - Tambahkan `lib/Table/Grammar/Sqlite.php` di samping `Mysql.php`.
  - Buat adaptor yang menyediakan API bergaya `$dbs` di atas PDO SQLite (`query`, `num_rows`, `affected_rows`, `insert_id`, `escape_string`).
  - Fondasinya sudah ada sebagian (`Connection` dan `Grammar` sudah dipisah per driver).
- **💬 Sederhananya:** Bangun "colokan universal" agar aplikasi bisa dicolokkan ke MySQL atau SQLite tanpa mengubah isi aplikasinya.

### Sugesti 3. Buat lapisan kompatibilitas SQL, bukan mengedit 930 query satu per satu

- **Teknis:**
  - Daftarkan fungsi pengguna di SQLite lewat `PDO::sqliteCreateFunction`: `REGEXP`, `CONCAT`, `NOW`, `CURDATE`, `DATE_FORMAT`, `UNIX_TIMESTAMP`, `FROM_UNIXTIME`, `DATE_ADD/DATE_SUB`, `RAND`.
  - Pertimbangkan penerjemah SQL ringan (penulis ulang `INSERT IGNORE`→`INSERT OR IGNORE`, `CONCAT`→`||`) untuk pola yang sering muncul, sementara kasus rumit ditulis manual.
  - Risiko: penerjemah berbasis regex rawan salah pada SQL kompleks. Perlu cakupan uji yang kuat.
- **💬 Sederhananya:** Alih-alih menulis ulang ribuan kalimat, ajari SQLite "kosakata" MySQL yang dipakai SLiMS, lalu hanya perbaiki kalimat yang benar-benar tidak bisa diterjemahkan otomatis.

### Sugesti 4. Ganti pencarian FULLTEXT dengan FTS5 secara terisolasi

- **Teknis:**
  - Buat tabel virtual FTS5 untuk judul, pengarang, subjek, catatan, dsb.
  - Tulis ulang `simbio_qparser` untuk menghasilkan sintaks FTS5 (`AND`, `OR`, `NOT`, `"frasa"`, `kata*`).
  - Sinkronkan indeks lewat trigger atau saat proses indexing.
  - Pastikan `pdo_sqlite` dengan FTS5 tersedia (cek saat instalasi).
- **💬 Sederhananya:** Bangun "buku indeks" baru untuk pencarian, lalu sesuaikan cara aplikasi bertanya ke buku indeks itu.

### Sugesti 5. Ganti `SQL_CALC_FOUND_ROWS` dengan hitung terpisah, lakukan di satu tempat

- **Teknis:** Ubah `simbio_dbgrid.inc.php`, `biblio_list.inc.php`, dan `biblio_list_index.inc.php` agar menjalankan `SELECT COUNT(*)` dengan kriteria yang sama. Ini juga bermanfaat untuk MySQL 8.0.17+ yang sudah menandai `SQL_CALC_FOUND_ROWS` sebagai usang.
- **💬 Sederhananya:** Hitung jumlah total hasil dengan satu pertanyaan terpisah. Cukup diperbaiki di beberapa file inti, dan semua daftar ikut membaik.

### Sugesti 6. Tulis ulang skema dan trigger khusus SQLite

- **Teknis:**
  - Buat skema SQLite untuk 61 tabel (hilangkan `ENGINE`, `ENUM` → `TEXT` dengan `CHECK`, `AUTO_INCREMENT` → `INTEGER PRIMARY KEY AUTOINCREMENT`).
  - Tulis ulang 3 trigger riwayat pinjaman.
  - Daftarkan collation kustom (mis. lewat `sqliteCreateCollation`) agar urutan teks Unicode dapat diterima.
- **💬 Sederhananya:** Siapkan "denah gudang" versi SQLite, termasuk aturan urutan abjad yang sopan untuk huruf non-Latin, dan petugas otomatis pencatat riwayat pinjam.

### Sugesti 7. Siapkan alat migrasi MySQL → SQLite (dan sebaliknya) bila dibutuhkan

- **Teknis:** Skrip konversi data per tabel dengan validasi jumlah baris dan *checksum*, serta mode simulasi (*dry-run*). Jangan menimpa database sumber.
- **💬 Sederhananya:** Sediakan "kendaraan pindahan" yang aman, dengan daftar periksa sebelum dan sesudah pindah, supaya tidak ada data yang tercecer.

### Sugesti 8. Aktifkan WAL dan atur *timeout* untuk mengurangi "database locked"

- **Teknis:** `PRAGMA journal_mode=WAL;`, `PRAGMA busy_timeout=<ms>;`, `PRAGMA synchronous=NORMAL;` dan simpan file di disk lokal (bukan NFS). Batasi transaksi tulis agar singkat.
- **💬 Sederhananya:** Atur agar saat satu petugas sedang menyimpan data, petugas lain menunggu sebentar, bukan langsung menemui pesan error.

### Sugesti 9. Amankan lokasi file database

- **Teknis:** Letakkan file `.sqlite` **di luar web root** (mis. `/var/lib/slims/`), atau blokir akses dengan aturan server (Apache/Nginx). Atur izin file hanya untuk user web server.
- **💬 Sederhananya:** Jangan taruh brankas data di teras. Simpan di ruangan yang tidak bisa dimasuki sembarang orang lewat internet.

### Sugesti 10. Ganti fitur backup

- **Teknis:** Untuk SQLite, gunakan `VACUUM INTO 'file'` atau `sqlite3_backup` (konsisten saat ada penulisan) alih-alih menyalin file mentah saat aplikasi aktif. Hubungkan ke UI Backup yang ada.
- **💬 Sederhananya:** Tombol "Backup" tetap ada, hanya cara kerjanya di belakang layar yang berbeda.

### Sugesti 11. Strategi pengujian untuk mencegah regresi

- **Teknis:**
  - Bangun set uji perbandingan: jalankan skenario yang sama di MySQL dan SQLite, lalu bandingkan hasilnya.
  - Prioritas uji: pencarian OPAC, perhitungan denda/jatuh tempo, pagination admin, impor/ekspor, trigger riwayat pinjam, OAI-PMH, REST API.
  - Pakai dataset nyata (anonim) dengan variasi bahasa dan aksara.
  - Tambahkan CI yang menjalankan keduanya. Saat ini tidak terlihat test suite atau CI di root repo.
- **💬 Sederhananya:** Uji dua mesin dengan soal yang sama, lalu cocokkan jawabannya. Perbedaan sekecil apa pun dicatat sebelum dirilis.

### Sugesti 12. Bertahap, mulai dari yang paling aman

1. **Tahap 0: Inventarisasi.** Daftar semua SQL MySQL-spesifik per modul (file dan jumlah). *Sederhananya:* buat peta lebih dulu sebelum membangun.
2. **Tahap 1: Fondasi.** Driver di `Connection`, adaptor `$dbs`, fungsi kompatibilitas. *Sederhananya:* pasang colokan universal.
3. **Tahap 2: Skema dan FTS5.** *Sederhananya:* siapkan denah gudang dan buku indeks baru.
4. **Tahap 3: Katalog dan OPAC (baca-saja).** Risiko rendah karena hampir tanpa penulisan bersamaan. *Sederhananya:* mulai dari bagian yang hanya dibaca pengunjung.
5. **Tahap 4: Keanggotaan dan master data.**
6. **Tahap 5: Sirkulasi (terakhir).** Tunda sampai fungsi tanggal teruji. *Sederhananya:* bagian yang menyangkut uang dan tenggat dikerjakan paling akhir dan paling hati-hati.
7. **Tahap 6: Upgrade, backup, migrasi.**

### Sugesti 13. Kriteria kapan SQLite layak dipakai

| Situasi | Rekomendasi | 💬 Sederhananya |
|---|---|---|
| Perpustakaan kecil (ratusan sampai beberapa ribu koleksi), 1–3 petugas | SQLite cocok | Seperti perpustakaan sekolah atau komunitas kecil. |
| Demo, pelatihan, pengembangan lokal | SQLite sangat cocok | Cepat dipasang untuk belajar atau mencoba. |
| Perpustakaan menengah/besar, banyak petugas, akses publik ramai | **Tetap MySQL/MariaDB** | Banyak orang menulis bersamaan, butuh mesin yang lebih kokoh. |
| Instalasi lama yang sudah berjalan | **Tetap MySQL/MariaDB** | Jangan pindah tanpa alasan kuat dan rencana migrasi. |

### Sugesti 14. Alternatif tanpa menulis ulang

- **Teknis:** Jika tujuan sebenarnya adalah **kemudahan instalasi**, gunakan paket **Docker** (sudah ada `Dockerfile`, `docker-compose.yml`) atau paket XAMPP/Laragon yang membundel MariaDB.
- **💬 Sederhananya:** Kalau tujuannya "mudah dipasang", ada jalan yang jauh lebih murah daripada mengganti mesin database.

---

## 9. Hal yang Belum Diverifikasi

- Jumlah pasti query yang perlu diubah (angka di atas dari `grep`, bisa meleset karena SQL dibangun dinamis).
- Apakah Elasticsearch dan OAI-PMH berjalan mandiri dari MySQL.
- Perilaku nyata FTS5 terhadap data berbahasa non-Latin pada koleksi nyata.
- Ketersediaan FTS5 di lingkungan hosting target.
- Dampak pada plugin dan tema yang dipasang pengguna (tidak ada di repo ini).
- Kinerja pada koleksi besar. Belum ada benchmark.

---

## 10. Kesimpulan Akhir

> **SQLite bisa menjadi pilihan tambahan yang baik untuk perpustakaan kecil, tetapi bukan pengganti MySQL.**
> Mengganti total berarti menulis ulang cara aplikasi bertanya dan menyimpan data di hampir semua modul, dengan risiko tertinggi pada **pencarian** dan **perhitungan denda/tanggal**.

**💬 Pesan utama:** Tambahkan jalur SQLite secara bertahap dan terukur, jangan mencabut MySQL. Dengan begitu perpustakaan kecil mendapat kemudahan, sementara pengguna lama tetap aman.
