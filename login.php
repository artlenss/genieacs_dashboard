<?php
// C:\xampp\htdocs\genieacs\login.php
session_start();

// Jika sudah login, langsung ke index
if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Load Config
    define('GENIE_ACCESS', true);
    // Cek apakah file config ada
    if(file_exists('include/config.php')) {
        require_once 'include/config.php';
        
        $u = $_POST['username'] ?? '';
        $p = $_POST['password'] ?? '';

        // Validasi User/Pass dari Config
        if ($u === DASH_USER && $p === DASH_PASS) {
            $_SESSION['is_logged_in'] = true;
            header("Location: index.php");
            exit;
        } else {
            $error = "Username atau Password salah!";
        }
    } else {
        $error = "File include/config.php tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GenieACS Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card-login {
            width: 100%;
            max-width: 400px;
            border: none;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .card-header {
            background: white;
            border-bottom: none;
            padding-top: 30px;
            text-align: center;
        }
        /* Style untuk Logo */
        .login-logo {
            max-width: 180px;  /* Lebar maksimal logo */
            max-height: 100px; /* Tinggi maksimal logo */
            width: auto;
            height: auto;
            margin-bottom: 10px;
            display: inline-block;
        }
        .btn-login {
            background: #1e3c72;
            border: none;
            font-weight: bold;
            letter-spacing: 1px;
            padding: 12px;
        }
        .btn-login:hover {
            background: #162b55;
        }
        .form-control {
            border-radius: 8px;
            padding: 20px 15px; /* Padding input lebih besar biar enak */
            background-color: #f8f9fa;
            border: 1px solid #eee;
        }
        .form-control:focus {
            background-color: #fff;
            box-shadow: none;
            border-color: #1e3c72;
        }
        .input-group-text {
            background: #f8f9fa;
            border: 1px solid #eee;
            border-radius: 8px 0 0 8px;
            color: #1e3c72;
        }
    </style>
</head>
<body>

<div class="card card-login bg-white animate__animated animate__fadeInDown">
    <div class="card-header">
        <img src="logo.png" alt="ISP Logo" class="login-logo" onerror="this.style.display='none'">
        
        <h5 class="font-weight-bold text-dark mt-2">GenieACS Manager</h5>
        <p class="text-muted small mb-0">Silahkan login untuk mengakses dashboard</p>
    </div>
    
    <div class="card-body px-4 pb-5 pt-2">
        <?php if($error): ?>
            <div class="alert alert-danger text-center small py-2 rounded-pill shadow-sm">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group mb-3">
                <label class="small font-weight-bold text-muted ml-1">USERNAME</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                    </div>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username..." required autofocus>
                </div>
            </div>

            <div class="form-group mb-4">
                <label class="small font-weight-bold text-muted ml-1">PASSWORD</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    </div>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password..." required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block btn-login shadow">
                MASUK <i class="fas fa-arrow-right ml-2"></i>
            </button>
        </form>
    </div>
    <div class="card-footer text-center bg-light small text-muted py-3">
        &copy; <?php echo date('Y'); ?> Network Management System By: ITMAGELANG
    </div>
</div>

</body>
</html>