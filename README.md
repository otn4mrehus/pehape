## PHP Tools
### 1. CSV IMPORT
#### A. Persiapkan Tabel dan Field yang dihutuhkan
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

#### B. Duplikasikan sesuai fungsi Tabel yang dibutuhkan 
```
// Specific import functions
function import_siswa($conn, $file) {
    global $tables;
    return import_csv_to_table($conn, $file, 'siswa', $tables['siswa']);
}

```

