<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
$file_name = $_GET['file'] ?? '';

// Security: cegah path traversal
$file_name = basename($file_name);
$file_path = $user['folder'] . '/storage/' . $file_name;

if (file_exists($file_path)) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit();
} else {
    die("File tidak ditemukan");
}
?>
