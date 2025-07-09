<?php 
session_start(); 
require_once('config.php');
require_once('login.php');
require_once('logout.php');
require_once('table.php');
require_once('table_function.php');
require_once('import_function.php');
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

<!-- Cek (Gagal Login) -->
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
<!-- Cek ( Login Sukses) -->
<?php else: ?>
<div class="container mx-auto p-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Sistem Import CSV Dinamis</h2>
        <a href="?logout=1" class="text-red-600 hover:text-red-800">Logout</a>
    </div>
    <!-- Cek ( Data ) -->
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
                Import Data (CSV)
            </button>
        </form>
    </div>
</div>
<?php endif; ?>
</body>
</html>
