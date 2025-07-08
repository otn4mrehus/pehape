<?php
session_start();

// Database configuration
$db_config = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'db' => 'csv_import_db'
];

// Database connection
function db_connect($config) {
    $conn = mysqli_connect($config['host'], $config['user'], $config['pass'], $config['db']);
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    return $conn;
}

// Login check
function is_logged_in() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}

// Login handler
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['loggedin'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = "Kredensial salah!";
    }
}

// Logout handler
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

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

// Generic CSV import function
function import_csv_to_table($conn, $file, $table, $fields) {
    $result = ['success' => 0, 'error' => 0, 'messages' => []];
    
    if (($handle = fopen($file['tmp_name'], "r")) !== FALSE) {
        $header = fgetcsv($handle);
        $csv_fields = array_map('trim', $header);
        $defined_fields = array_keys($fields);
        
        // Validate CSV headers match expected fields
        if (array_diff($csv_fields, $defined_fields) || array_diff($defined_fields, $csv_fields)) {
            $result['messages'][] = "Kolom CSV tidak sesuai dengan definisi tabel $table!";
            return $result;
        }
        
        // Create table if not exists
        $columns = [];
        foreach ($fields as $field => $type) {
            $columns[] = "$field $type";
        }
        $create_query = "CREATE TABLE IF NOT EXISTS $table (
            id INT AUTO_INCREMENT PRIMARY KEY,
            " . implode(', ', $columns) . "
        )";
        
        if (!mysqli_query($conn, $create_query)) {
            $result['messages'][] = "Error creating table: " . mysqli_error($conn);
            return $result;
        }
        
        // Prepare insert query
        $placeholders = implode(',', array_fill(0, count($defined_fields), '?'));
        $insert_query = "INSERT INTO $table (" . implode(',', $defined_fields) . ") VALUES ($placeholders)";
        $stmt = mysqli_prepare($conn, $insert_query);
        
        while (($data = fgetcsv($handle)) !== FALSE) {
            $params = [];
            foreach ($data as $value) {
                $params[] = $value === '' ? NULL : $value; // Handle empty values as NULL
            }
            
            mysqli_stmt_bind_param($stmt, str_repeat('s', count($defined_fields)), ...$params);
            
            if (mysqli_stmt_execute($stmt)) {
                $result['success']++;
            } else {
                $result['error']++;
                $result['messages'][] = "Error inserting row: " . mysqli_error($conn);
            }
        }
        
        mysqli_stmt_close($stmt);
        fclose($handle);
    }
    
    return $result;
}

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

// Process CSV upload
$upload_results = [];
if (is_logged_in() && isset($_FILES['csv_files']) && !empty($_FILES['csv_files']['name'][0]) && isset($_POST['tables'])) {
    $conn = db_connect($db_config);
    
    foreach ($_POST['tables'] as $table) {
        if (!array_key_exists($table, $tables)) {
            $upload_results[$table] = ['messages' => ["Tabel $table tidak didefinisikan!"]];
            continue;
        }
        
        foreach ($_FILES['csv_files']['tmp_name'] as $index => $tmp_name) {
            if ($_FILES['csv_files']['error'][$index] === UPLOAD_ERR_OK) {
                $func_name = "import_$table";
                if (function_exists($func_name)) {
                    $result = $func_name($conn, [
                        'tmp_name' => $tmp_name,
                        'name' => $_FILES['csv_files']['name'][$index]
                    ]);
                    $upload_results[$table] = $result;
                }
            }
        }
    }
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic CSV Import System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .error { color: red; }
        .success { color: green; }
        .table-option { margin-bottom: 1rem; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">


<?php if (!is_logged_in()): ?>
<div class="max-w-md mx-auto mt-10 p-6 bg-white rounded-lg shadow-xl">
    <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>
    <?php if (isset($error)): ?>
        <p class="error mb-4"><?php echo $error; ?></p>
    <?php endif; ?>
    <form method="POST" class="space-y-4">
        <div>
            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
            <input type="text" name="username" id="username" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" id="password" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        </div>
        <button type="submit" name="login"
                class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">
            Login
        </button>
    </form>
</div>
<?php else: ?>
<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Sistem Import CSV Dinamis</h2>
        <a href="?logout=1" class="text-red-600 hover:text-red-800">Logout</a>
    </div>
    
    <?php if (!empty($upload_results)): ?>
        <div class="mb-6 p-4 bg-white rounded-lg shadow">
            <h3 class="text-lg font-semibold">Hasil Import</h3>
            <?php foreach ($upload_results as $table => $result): ?>
                <h4 class="font-medium mt-4">Tabel: <?php echo $table; ?></h4>
                <p class="success">Berhasil diimpor: <?php echo $result['success']; ?> baris</p>
                <p class="error">Error: <?php echo $result['error']; ?></p>
                <?php foreach ($result['messages'] as $message): ?>
                    <p class="error"><?php echo $message; ?></p>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="bg-white p-6 rounded-lg shadow">
        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Pilih Tabel</label>
                <?php foreach ($tables as $table => $fields): ?>
                    <div class="table-option">
                        <input type="checkbox" name="tables[]" value="<?php echo $table; ?>"
                               id="table_<?php echo $table; ?>"
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <label for="table_<?php echo $table; ?>" class="ml-2 text-sm text-gray-700">
                            <?php echo ucfirst($table); ?> (Kolom: <?php echo implode(', ', array_map(function($field, $type) { return "$field ($type)"; }, array_keys($fields), $fields)); ?>)
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
            <div>
                <label for="csv_files" class="block text-sm font-medium text-gray-700">Pilih File CSV</label>
                <input type="file" name="csv_files[]" id="csv_files" multiple accept=".csv" required
                       class="mt-1 block w-full text-sm text-gray-500
                              file:mr-4 file:py-2 file:px-4
                              file:rounded-full file:border-0
                              file:text-sm file:font-semibold
                              file:bg-indigo-50 file:text-indigo-700
                              hover:file:bg-indigo-100">
            </div>
            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700">
                Import CSV
            </button>
        </form>
    </div>
</div>
<?php endif; ?>
</body>
</html>
