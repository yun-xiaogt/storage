<?php
session_start();
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$email = $input['email'];
$password = $input['password'];

// Validasi email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email tidak valid']);
    exit();
}

// Buat folder users kalau belum ada
if (!file_exists('users')) {
    mkdir('users', 0777, true);
}

// Nama folder dari email (aman untuk file system)
$folder_name = preg_replace('/[^a-zA-Z0-9]/', '_', $email);
$user_folder = 'users/' . $folder_name;
$user_file = $user_folder . '/data.json';

// Cek user udah ada apa belum
if (file_exists($user_file)) {
    // Login
    $data = json_decode(file_get_contents($user_file), true);
    if (password_verify($password, $data['password'])) {
        $_SESSION['user'] = [
            'email' => $email,
            'folder' => $user_folder,
            'folder_name' => $folder_name
        ];
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Password salah']);
    }
} else {
    // Register user baru
    mkdir($user_folder, 0777, true);
    mkdir($user_folder . '/storage', 0777, true); // FOLDER STORAGE UNTUK FILE
    
    // Simpan data user
    $user_data = [
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
        'created_at' => date('Y-m-d H:i:s'),
        'total_files' => 0,
        'total_size' => 0
    ];
    
    file_put_contents($user_file, json_encode($user_data, JSON_PRETTY_PRINT));
    
    // Auto login
    $_SESSION['user'] = [
        'email' => $email,
        'folder' => $user_folder,
        'folder_name' => $folder_name
    ];
    
    echo json_encode(['success' => true]);
}
?>
