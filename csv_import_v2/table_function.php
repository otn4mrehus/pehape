<?php
// Specific import functions
function import_siswa($conn, $file) {
    global $tables;
    return import_csv_to_table($conn, $file, 'siswa', $tables['siswa']);
}

function import_guru($conn, $file) {
    global $tables;
    return import_csv_to_table($conn, $file, 'guru', $tables['guru']);
}

// Tambahkan fungsi import lain sesuai kebutuhan, misal:
// function import_staff($conn, $file) {
//     global $tables;
//     return import_csv_to_table($conn, $file, 'staff', $tables['staff']);
// }
?>
