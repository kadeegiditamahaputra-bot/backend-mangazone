<?php
session_start();
require "../config/db.php";

$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hardcode admin dipertahankan sesuai logika bawaan Anda
    if ($username === "admin" && $password === "1234") {
        $_SESSION['admin'] = true;
        header("Location: dashboard.php");
        exit;
    } else {
        $error_message = "Incorrect username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign in to MangaZone · Control Panel</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
/* Modern Minimalist Light Theme (Match with Dashboard) */
body {
    background-color: #fafafa;
    color: #171717;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    font-size: 14px;
    letter-spacing: -0.01em;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

/* Header Brand */
.mz-login-header {
    text-align: center;
    margin-bottom: 24px;
}

.brand-logo-round {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    border: 1px solid #e5e5e5;
    object-fit: cover;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.mz-login-title {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: -0.02em;
    color: #171717;
}

.mz-login-subtitle {
    font-size: 13px;
    color: #737373;
    margin-top: 4px;
    font-weight: 400;
}

/* Container Form Utama */
.mz-auth-box {
    background-color: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 12px;
    padding: 28px;
    width: 100%;
    max-width: 340px; 
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02), 0 1px 2px rgba(0, 0, 0, 0.04);
}

/* Form Input & Label */
.mz-label {
    font-size: 13px;
    font-weight: 500;
    color: #404040;
    margin-bottom: 6px;
    display: block;
}

.mz-input-field {
    background-color: #ffffff;
    border: 1px solid #e5e5e5;
    color: #171717 !important;
    font-size: 14px;
    padding: 8px 14px;
    border-radius: 8px;
    width: 100%;
    transition: all 0.2s ease;
}

.mz-input-field::placeholder {
    color: #a3a3a3;
}

.mz-input-field:focus {
    background-color: #ffffff;
    border-color: #171717;
    outline: none;
    box-shadow: 0 0 0 4px rgba(23, 23, 23, 0.08);
}

/* Tombol Masuk Solid Black */
.btn-mz-submit {
    background-color: #171717;
    color: #ffffff !important;
    border: 1px solid #171717;
    font-size: 14px;
    font-weight: 500;
    padding: 9px 16px;
    border-radius: 8px;
    width: 100%;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-mz-submit:hover {
    background-color: #404040;
    border-color: #404040;
    transform: translateY(-1px);
}

/* Banner Error */
.mz-alert-danger {
    background-color: #fff5f5;
    border: 1px solid #fca5a5;
    color: #ef4444;
    font-size: 13px;
    font-weight: 500;
    border-radius: 8px;
    padding: 12px 14px;
    width: 100%;
    max-width: 340px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Footer Back Link */
.mz-back-callout {
    text-align: center;
    width: 100%;
    max-width: 340px;
    margin-top: 20px;
    font-size: 13px;
    color: #737373;
}

.mz-link {
    color: #171717;
    font-weight: 500;
    text-decoration: none;
    border-bottom: 1px dashed #d4d4d4;
    transition: all 0.15s ease;
}

.mz-link:hover {
    color: #737373;
    border-bottom-color: #737373;
}
</style>
</head>

<body>

  <div class="mz-login-header">
    <img src="../assets/mangazone.png" alt="M" class="brand-logo-round" onerror="this.style.display='none'">
    <h1 class="mz-login-title">MangaZone</h1>
    <div class="mz-login-subtitle">Sign in to access repository dashboard</div>
  </div>

  <?php if (!empty($error_message)): ?>
    <div class="mz-alert-danger">
      <svg aria-hidden="true" height="16" viewBox="0 0 16 16" width="16" fill="currentColor"><path d="M6.457 1.047c.659-1.234 2.427-1.234 3.086 0l6.03 11.3c.66 1.235-.236 2.653-1.544 2.653H1.971C.663 15 1.711-2.43 2.15 2.653l6.03-11.3zM1.97 13.5h12.058L8 2.114 1.971 13.5zM8 11.25a.75.75 0 110-1.5.75.75 0 010 1.5zm0-6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 018 5.25z"></path></svg>
      <div><?= htmlspecialchars($error_message) ?></div>
    </div>
  <?php endif; ?>

  <div class="mz-auth-box">
    <form action="" method="POST">
        
        <div class="mb-3">
            <label for="username" class="mz-label">Username</label>
            <input type="text" 
                   name="username" 
                   id="username" 
                   class="mz-input-field" 
                   placeholder="Enter username"
                   required 
                   autocomplete="off">
        </div>

        <div class="mb-4">
            <label for="password" class="mz-label">Password</label>
            <input type="password" 
                   name="password" 
                   id="password" 
                   class="mz-input-field" 
                   placeholder="Enter password"
                   required>
        </div>

        <button type="submit" class="btn-mz-submit">
            Sign in
        </button>

    </form>
  </div>

  <div class="mz-back-callout">
      Protected area. <a href="../index.php" class="mz-link">Return to homepage</a>
  </div>

</body>
</html>