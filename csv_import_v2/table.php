<?php
// Table definitions with column names and their data types
$tables = [
    'siswa' => [
        'nama' => 'VARCHAR(100)',
        'nis' => 'INT',
        'kelas' => 'VARCHAR(20)',
        'tanggal_lahir' => 'DATE',
        'nilai' => 'DECIMAL(5,2)'
    ],
    'guru' => [
        'nama' => 'VARCHAR(100)',
        'nip' => 'BIGINT',
        'mata_pelajaran' => 'VARCHAR(50)',
        'tanggal_masuk' => 'DATE'
    ]
    // Tambahkan tabel lain, misal:
    // 'staff' => [
    //     'nama' => 'VARCHAR(100)',
    //     'id_staff' => 'INT',
    //     'jabatan' => 'VARCHAR(50)'
    // ]
];
?>
