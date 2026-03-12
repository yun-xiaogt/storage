<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
$file_name = $_GET['file'] ?? '';

// Security
$file_name = basename($file_name);
$file_path = $user['folder'] . '/storage/' . $file_name;

if (file_exists($file_path)) {
    unlink($file_path);
    
    // Update data.json
    $user_file = $user['folder'] . '/data.json';
    if (file_exists($user_file)) {
        $data = json_decode(file_get_contents($user_file), true);
        if (isset($data['files'])) {
            $data['files'] = array_filter($data['files'], fn($f) => $f['name'] !== $file_name);
            $data['total_files'] = count($data['files']);
            file_put_contents($user_file, json_encode($data, JSON_PRETTY_PRINT));
        }
    }
}

header("Location: dashboard.php");
exit();
?>
