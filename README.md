## PHP Tools
### 1. CSV IMPORT
#### Fitur:
```
1. Multi-Select dan Multi-Upload:
   - Tetap mendukung pemilihan beberapa tabel dan beberapa file CSV dalam satu form,
   - Setiap file CSV akan diproses untuk setiap tabel yang dipilih.
2. Validasi Header:
   - Sistem tetap memeriksa apakah header CSV sesuai dengan kolom yang didefinisikan di $tables.
   - Jika header tidak cocok, proses import akan gagal dengan pesan error.
3. Pembuatan Tabel Dinamis:
   - Fungsi import_csv_to_table menggunakan definisi tipe data dari $tables untuk membuat query CREATE TABLE yang sesuai.
   - Kolom dibuat sesuai tipe data yang ditentukan, sehingga data dari CSV akan disimpan dengan tipe yang benar di database.
4. Penanganan Data CSV:
   - Data dari CSV dianggap sebagai string saat diunggah, tetapi MySQL akan mengonversi data ke tipe yang sesuai
     (misalnya, string "123" ke INT, "2025-07-08" ke DATE) selama tipe kolom di tabel sudah benar.
   - Nilai kosong di CSV ("") diubah menjadi NULL untuk menghindari error pada kolom dengan tipe data non-string.
5. Definisi Tabel dengan Tipe Data:
   - Array $tables sekarang mendefinisikan tipe data untuk setiap kolom,
   - Tipe data yang didukung bisa disesuaikan (misalnya, INT, BIGINT, VARCHAR, DATE, DECIMAL, dll.).
```

#### A. Konfigurasi Server Databse
```
// Database configuration
$db_config = [
    'host' => 'localhost',     //atau Host Conainer DB yang aktif jika menggunkaan Docker Server 
    'user' => 'root',
    'pass' => '',
    'db' => 'csv_import_db'   // Nama Database
];
```

#### B. Persiapkan Tabel dan Field yang dihutuhkan
```
// Table definitions with column names and their data types
$tables = [
    'siswa' => [
        'nama' => 'VARCHAR(100)',
        'nis' => 'INT',
        'kelas' => 'VARCHAR(20)',
        'tanggal_lahir' => 'DATE',
        'nilai' => 'DECIMAL(5,2)'
    ],
```

#### C. Duplikasikan sesuai fungsi Tabel yang dibutuhkan 
```
// Specific import functions
function import_siswa($conn, $file) {
    global $tables;
    return import_csv_to_table($conn, $file, 'siswa', $tables['siswa']);
}

```
#### D. Jalankan dari URL Browser

```
http://127.0.0.1/csv_import/index.php
```
