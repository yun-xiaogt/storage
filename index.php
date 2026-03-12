<?php
session_start();
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FileBank · Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: -apple-system, 'Segoe UI', Roboto, sans-serif;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .login-card {
            background: white;
            border-radius: 30px;
            padding: 40px 25px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }
        h1 {
            font-size: 32px;
            color: #1e293b;
            text-align: center;
            margin-bottom: 10px;
        }
        .subtitle {
            text-align: center;
            color: #64748b;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }
        input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            font-size: 16px;
        }
        input:focus {
            border-color: #667eea;
            outline: none;
        }
        button {
            width: 100%;
            background: #667eea;
            color: white;
            border: none;
            padding: 16px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 18px;
            cursor: pointer;
            margin: 10px 0;
        }
        button:hover {
            background: #5a67d8;
        }
        .error {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
        }
        .info {
            text-align: center;
            color: #64748b;
            font-size: 13px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>📁 FileBank</h1>
        <p class="subtitle">1 Email = 1 Storage Unlimited</p>
        
        <div id="errorMsg" class="error"></div>
        
        <form id="loginForm">
            <div class="form-group">
                <label>📧 Email</label>
                <input type="email" id="email" required placeholder="contoh@gmail.com">
            </div>
            <div class="form-group">
                <label>🔑 Password</label>
                <input type="password" id="password" required placeholder="Minimal 6 karakter">
            </div>
            <button type="submit">🚀 MASUK / DAFTAR</button>
        </form>
        
        <p class="info">✅ Auto daftar kalau email belum terdaftar<br>📦 Bisa upload FOTO, VIDEO, APK, PDF, ZIP, dll</p>
    </div>

    <script>
    document.getElementById('loginForm').onsubmit = async (e) => {
        e.preventDefault();
        
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        
        if (password.length < 6) {
            document.getElementById('errorMsg').style.display = 'block';
            document.getElementById('errorMsg').textContent = 'Password minimal 6 karakter';
            return;
        }
        
        const res = await fetch('login_process.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({email, password})
        });
        
        const data = await res.json();
        
        if (data.success) {
            window.location.href = 'dashboard.php';
        } else {
            document.getElementById('errorMsg').style.display = 'block';
            document.getElementById('errorMsg').textContent = data.message;
        }
    };
    </script>
</body>
</html>
