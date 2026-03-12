<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];
$storage_path = $user['folder'] . '/storage';
$user_file = $user['folder'] . '/data.json';

// Baca data user
$user_data = [];
$total_size = 0;
$file_count = 0;
$files = [];

if (file_exists($user_file)) {
    $user_data = json_decode(file_get_contents($user_file), true);
    $total_size = $user_data['total_size'] ?? 0;
    $file_count = $user_data['total_files'] ?? 0;
    $files = $user_data['files'] ?? [];
}

// Format ukuran storage
function formatSize($bytes) {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes/1024, 1) . ' KB';
    if ($bytes < 1073741824) return round($bytes/1048576, 1) . ' MB';
    if ($bytes < 1099511627776) return round($bytes/1073741824, 1) . ' GB';
    return round($bytes/1099511627776, 1) . ' TB';
}

$used_storage = formatSize($total_size);
$max_storage = '100 TB';
$max_storage_bytes = 100 * 1099511627776;
$usage_percent = min(round(($total_size / $max_storage_bytes) * 100), 100);

// Handle upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['files'])) {
    $files_upload = $_FILES['files'];
    $success_count = 0;
    
    for ($i = 0; $i < count($files_upload['name']); $i++) {
        if ($files_upload['error'][$i] == 0) {
            $file_name = $files_upload['name'][$i];
            $file_tmp = $files_upload['tmp_name'][$i];
            $file_size = $files_upload['size'][$i];
            
            $file_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file_name);
            $destination = $storage_path . '/' . $file_name;
            
            if (file_exists($destination)) {
                $path_parts = pathinfo($file_name);
                $file_name = $path_parts['filename'] . '_' . time() . '.' . $path_parts['extension'];
                $destination = $storage_path . '/' . $file_name;
            }
            
            if (move_uploaded_file($file_tmp, $destination)) {
                $success_count++;
                
                if (file_exists($user_file)) {
                    $data = json_decode(file_get_contents($user_file), true);
                    if (!isset($data['files'])) $data['files'] = [];
                    
                    $data['files'][] = [
                        'name' => $file_name,
                        'size' => $file_size,
                        'type' => $files_upload['type'][$i],
                        'uploaded_at' => date('Y-m-d H:i:s')
                    ];
                    $data['total_files'] = count($data['files']);
                    $data['total_size'] = ($data['total_size'] ?? 0) + $file_size;
                    
                    file_put_contents($user_file, json_encode($data, JSON_PRETTY_PRINT));
                }
            }
        }
    }
    
    if ($success_count > 0) {
        echo "<script>alert('✅ $success_count file berhasil diupload'); window.location.href='?view=home';</script>";
    }
}

// Tentukan view aktif
$view = $_GET['view'] ?? 'home';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Texas · 100TB Storage</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
        }

        body {
            background: #0a0e1a;
            color: #e0e5f0;
            height: 100vh;
            overflow: hidden;
        }

        /* FULL SCREEN */
        .phone-frame {
            width: 100%;
            height: 100vh;
            background: #0f1420;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Scrollable content */
        .content {
            flex: 1;
            overflow-y: auto;
            padding: 16px 16px 10px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .content::-webkit-scrollbar {
            display: none;
        }

        /* Welcome Card dengan VIDEO BACKGROUND */
        .welcome-card {
            position: relative;
            border-radius: 24px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 10px 0 #0b0f1a;
            min-height: 180px;
        }

        .welcome-bg-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            z-index: 0;
            pointer-events: none;
        }

        .welcome-content {
            position: relative;
            z-index: 1;
            background: rgba(10, 15, 30, 0.7);
            backdrop-filter: blur(3px);
            padding: 22px;
            border: 1px solid rgba(255, 215, 0, 0.3);
            border-radius: 24px;
            height: 100%;
        }

        .greeting {
            color: #ffd966;
            font-size: 13px;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .email-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(15, 25, 50, 0.8);
            backdrop-filter: blur(5px);
            padding: 15px 18px;
            border-radius: 18px;
            margin: 12px 0 0;
            border: 1px solid rgba(255, 215, 0, 0.5);
        }

        .email {
            font-weight: 700;
            font-size: 16px;
            color: #ffffff;
            word-break: break-all;
            text-shadow: 0 2px 5px rgba(0,0,0,0.5);
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-change, .btn-logout {
            text-decoration: none;
            padding: 8px 18px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 13px;
            transition: 0.2s;
        }

        .btn-change {
            background: rgba(37, 59, 110, 0.9);
            color: #ffd966;
            border: 1px solid #5b7ac0;
        }

        .btn-logout {
            background: rgba(61, 42, 58, 0.9);
            color: #ffb6b6;
            border: 1px solid #a05a5a;
        }

        /* Storage Card - 100TB */
        .storage-card {
            background: #1a2235;
            border-radius: 24px;
            padding: 22px;
            margin-bottom: 20px;
            border: 1px solid #2f3b5a;
            box-shadow: 0 10px 0 #0b0f1a;
        }

        .storage-label {
            display: flex;
            justify-content: space-between;
            color: #b2c6ff;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .storage-bar-bg {
            background: #1e2842;
            height: 16px;
            border-radius: 30px;
            overflow: hidden;
            border: 1px solid #3b4870;
        }

        .storage-bar-fill {
            width: <?= $usage_percent ?>%;
            height: 100%;
            background: linear-gradient(90deg, #4b7bec, #a55eea);
            border-radius: 30px;
        }

        .storage-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            color: #8a9cdb;
            font-size: 13px;
        }

        /* Latest News - HANYA 2 SLIDE */
        .news-section {
            margin: 20px 0 25px;
        }

        .news-title {
            font-size: 18px;
            font-weight: 700;
            color: #f0e6c5;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .news-title i {
            color: #ffd966;
        }

        .news-slider {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            padding: 5px 0 15px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .news-slider::-webkit-scrollbar {
            display: none;
        }

        .news-slide {
            flex: 0 0 280px;
            scroll-snap-align: start;
            background: #1a2235;
            border-radius: 24px;
            padding: 18px;
            border: 1px solid #2f3b5a;
            box-shadow: 0 8px 0 #0b0f1a;
        }

        .video-container {
            background: #0f172f;
            border-radius: 18px;
            padding: 12px;
            border: 1px solid #3e5090;
        }

        .video-wrapper {
            position: relative;
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
            background: #000;
            aspect-ratio: 16/9;
            pointer-events: none;
        }

        video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            pointer-events: none;
        }

        .video-caption {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 15px;
        }

        .video-icon {
            background: #2b3b6e;
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .video-text h4 {
            color: #ffd966;
            font-size: 16px;
        }

        .video-text p {
            color: #9bb0f0;
            font-size: 13px;
        }

        /* Bottom Navigation */
        .bottom-nav {
            background: #0c1122;
            border-top: 2px solid #25304a;
            display: flex;
            justify-content: space-around;
            padding: 12px 8px 20px;
            box-shadow: 0 -5px 15px rgba(0,0,0,0.5);
        }

        .nav-item {
            text-decoration: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            color: #6f7da0;
            font-size: 12px;
            transition: 0.2s;
            flex: 1;
        }

        .nav-item.active {
            color: #ffd966;
        }

        .nav-item i {
            font-size: 22px;
        }

        /* File Grid */
        .file-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 15px;
        }

        .file-card {
            background: #1a2235;
            border-radius: 16px;
            padding: 12px;
            text-align: center;
            border: 1px solid #31415e;
        }

        .file-icon {
            font-size: 30px;
            margin-bottom: 5px;
        }

        .file-name {
            font-size: 11px;
            color: #cbd5e1;
            word-break: break-all;
        }

        .empty-state {
            text-align: center;
            color: #6b7b9c;
            padding: 40px 0;
        }

        .upload-btn {
            background: #1e2a47;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 50px;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            border: 1px solid #4b6190;
            margin-bottom: 20px;
        }
    </style>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="phone-frame">
        <!-- Content area (scrollable) -->
        <div class="content">
            <?php if ($view == 'home'): ?>
                <!-- WELCOME CARD dengan VIDEO BACKGROUND -->
                <div class="welcome-card">
                    <video class="welcome-bg-video" autoplay muted loop playsinline disablePictureInPicture>
                        <source src="https://h.top4top.io/m_3720adzfe1.mp4" type="video/mp4">
                    </video>
                    
                    <div class="welcome-content">
                        <div class="greeting">WELCOME BACK,</div>
                        
                        <div class="email-box">
                            <span class="email"><?= htmlspecialchars($user['email']) ?></span>
                            <div class="action-buttons">
                                <a href="change_password.php" class="btn-change"><i class="fas fa-key"></i> Change</a>
                                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STORAGE CARD 100TB -->
                <div class="storage-card">
                    <div class="storage-label">
                        <span><i class="fas fa-database"></i> Storage Terpakai</span>
                        <span><?= $used_storage ?> / <?= $max_storage ?></span>
                    </div>
                    <div class="storage-bar-bg">
                        <div class="storage-bar-fill"></div>
                    </div>
                    <div class="storage-stats">
                        <span><i class="far fa-file"></i> <?= $file_count ?> File</span>
                        <span><?= $usage_percent ?>% of 100TB</span>
                    </div>
                </div>

                <!-- LATEST NEWS - HANYA 2 SLIDE -->
                <div class="news-section">
                    <div class="news-title">
                        <i class="fas fa-newspaper"></i> Latest News
                    </div>
                    
                    <div class="news-slider">
                        <!-- Slide 1: Storage Xiao Beta -->
                        <div class="news-slide">
                            <div class="video-container">
                                <div class="video-wrapper">
                                    <video autoplay muted loop playsinline disablePictureInPicture>
                                        <source src="https://k.top4top.io/m_372048u4l1.mp4" type="video/mp4">
                                    </video>
                                </div>
                                <div class="video-caption">
                                    <div class="video-icon">📱</div>
                                    <div class="video-text">
                                        <h4>Storage Xiao Beta</h4>
                                        <p>Update terbaru dengan fitur storage</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Slide 2: New! Templar -->
                        <div class="news-slide">
                            <div class="video-container">
                                <div class="video-wrapper">
                                    <video autoplay muted loop playsinline disablePictureInPicture>
                                        <source src="https://k.top4top.io/m_3718omy9z1.mp4" type="video/mp4">
                                    </video>
                                </div>
                                <div class="video-caption">
                                    <div class="video-icon">⚔️</div>
                                    <div class="video-text">
                                        <h4>Xiao</h4>
                                        <p>Create By Yun Xiao</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload button -->
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="file" name="files[]" id="fileInput" multiple style="display: none;">
                    <button type="button" class="upload-btn" onclick="document.getElementById('fileInput').click()">
                        <i class="fas fa-cloud-upload-alt"></i> Upload File (100TB Available)
                    </button>
                    <button type="submit" style="display: none;" id="submitUpload"></button>
                </form>

            <?php elseif ($view == 'files'): ?>
                <h3 style="color: #ffd966; margin-bottom: 15px;"><i class="fas fa-folder"></i> Semua File</h3>
                
                <?php if (empty($files)): ?>
                    <div class="empty-state">
                        <i class="fas fa-folder-open" style="font-size: 50px;"></i>
                        <p>Belum ada file</p>
                        <p style="font-size: 12px; margin-top: 10px;">100TB storage siap pakai</p>
                    </div>
                <?php else: ?>
                    <div class="file-grid">
                        <?php foreach ($files as $file): 
                            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                            $icon = '📄';
                            if (in_array($ext, ['jpg','png','gif'])) $icon = '🖼️';
                            elseif (in_array($ext, ['mp4','avi'])) $icon = '🎬';
                            elseif ($ext == 'apk') $icon = '📱';
                        ?>
                            <div class="file-card">
                                <div class="file-icon"><?= $icon ?></div>
                                <div class="file-name"><?= htmlspecialchars($file['name']) ?></div>
                                <div style="margin-top: 8px;">
                                    <a href="download.php?file=<?= urlencode($file['name']) ?>" style="color: #4b7bec;">⬇️</a>
                                    <a href="delete.php?file=<?= urlencode($file['name']) ?>" style="color: #ff6b6b; margin-left: 10px;" onclick="return confirm('Hapus?')">🗑️</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Bottom Navigation -->
        <div class="bottom-nav">
            <a href="?view=home" class="nav-item <?= $view == 'home' ? 'active' : '' ?>">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
            <a href="?view=files" class="nav-item <?= $view == 'files' ? 'active' : '' ?>">
                <i class="fas fa-folder"></i>
                <span>Files</span>
            </a>
            <a href="?view=gallery" class="nav-item">
                <i class="fas fa-images"></i>
                <span>Gallery</span>
            </a>
            <a href="?view=apps" class="nav-item">
                <i class="fas fa-robot"></i>
                <span>Apps</span>
            </a>
        </div>
    </div>

    <script>
    document.getElementById('fileInput')?.addEventListener('change', function() {
        if (this.files.length > 0) {
            document.getElementById('submitUpload')?.click();
        }
    });

    // Fallback video autoplay
    document.addEventListener('DOMContentLoaded', function() {
        const videos = document.querySelectorAll('video');
        videos.forEach(video => {
            video.addEventListener('pause', function(e) {
                this.play();
            });
            
            video.addEventListener('click', function(e) {
                e.preventDefault();
                return false;
            });
        });
    });
    </script>
</body>
</html>
