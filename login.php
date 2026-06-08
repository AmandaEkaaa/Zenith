<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $conn = getDBConnection();
        
        $stmt = $conn->prepare("SELECT id, username, password, nama_lengkap FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // Verify password (dalam contoh ini, password disimpan sebagai hash)
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['nama_lengkap'];
                $_SESSION['admin_username'] = $admin['username'];
                
                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Password salah!';
            }
        } else {
            $error = 'Username tidak ditemukan!';
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - ZENITH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Rajdhani', sans-serif;
            background: linear-gradient(135deg, #0A0E17 0%, #0C2B4E 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #F4F4F4;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 40px;
        }
        
        .login-card {
            background: rgba(26, 61, 100, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            border: 1px solid rgba(0, 201, 255, 0.3);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
            position: relative;
            overflow: hidden;
        }
        
        .login-card::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #00C9FF, #8A2BE2, #FF00FF);
            z-index: -1;
            border-radius: 22px;
            opacity: 0.5;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo {
            font-family: 'Orbitron', sans-serif;
            font-size: 3.5rem;
            font-weight: 900;
            background: linear-gradient(90deg, #00C9FF, #FF00FF);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: 3px;
            margin-bottom: 10px;
        }
        
        .logo-subtitle {
            color: #00C9FF;
            font-size: 1.2rem;
            letter-spacing: 2px;
            text-transform: uppercase;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #00C9FF;
            font-size: 1.1rem;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #00C9FF;
            font-size: 1.2rem;
        }
        
        .input-with-icon input {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border-radius: 10px;
            border: 1px solid rgba(0, 201, 255, 0.3);
            background: rgba(10, 14, 23, 0.7);
            color: #F4F4F4;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .input-with-icon input:focus {
            outline: none;
            border-color: #00C9FF;
            box-shadow: 0 0 15px rgba(0, 201, 255, 0.3);
        }
        
        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(90deg, #00C9FF, #8A2BE2);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Orbitron', sans-serif;
            letter-spacing: 2px;
            margin-top: 10px;
        }
        
        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 201, 255, 0.4);
        }
        
        .error-message {
            background: rgba(255, 85, 85, 0.1);
            border: 1px solid rgba(255, 85, 85, 0.3);
            color: #FF5555;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
            color: rgba(244, 244, 244, 0.7);
        }
        
        .back-link a {
            color: #00C9FF;
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 480px) {
            .login-container {
                padding: 20px;
            }
            
            .login-card {
                padding: 30px 20px;
            }
            
            .logo {
                font-size: 2.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-section">
                <h1 class="logo">ZENITH</h1>
                <p class="logo-subtitle">ADMIN PANEL</p>
            </div>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user-cog"></i> USERNAME ADMIN</label>
                    <div class="input-with-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" id="username" name="username" placeholder="Masukkan username admin" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-key"></i> KATA SANDI</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Masukkan kata sandi" required>
                    </div>
                </div>
                
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> MASUK SEBAGAI ADMIN
                </button>
            </form>
            
            <div class="back-link">
                <a href="index.html"><i class="fas fa-arrow-left"></i> Kembali ke Halaman Utama</a>
            </div>
        </div>
    </div>
</body>
</html>