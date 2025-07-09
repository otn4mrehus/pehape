<?php
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
