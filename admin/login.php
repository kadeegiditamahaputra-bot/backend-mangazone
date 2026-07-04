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
<title>Sign in to MangaZone · GitHub Style</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=SF+Pro+Display:-apple-system,BlinkMacSystemFont,Segoe+UI,Helvetica,Arial,sans-serif&display=swap" rel="stylesheet">

<style>
/* GitHub Dark Theme Core */
body {
    background-color: #0d1117;
    color: #e6edf3;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Helvetica, Arial, sans-serif;
    font-size: 14px;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    padding-top: 5vh;
}

/* Header Brand minimalis atas */
.gh-login-header {
    text-align: center;
    margin-bottom: 16px;
}

.gh-svg-logo {
    color: #f0f6fc;
    margin-bottom: 24px;
}

.gh-login-title {
    font-size: 24px;
    font-weight: 300;
    letter-spacing: -0.5px;
    color: #e6edf3;
}

/* Kotak Form Utama */
.gh-auth-box {
    background-color: #161b22;
    border: 1px solid #30363d;
    border-radius: 6px;
    padding: 20px;
    width: 100%;
    max-width: 308px; /* Lebar box login standar GitHub */
}

/* Input & Label ala Primer Design */
.gh-label {
    font-size: 14px;
    font-weight: 400;
    color: #e6edf3;
    margin-bottom: 6px;
    display: block;
}

.gh-input-field {
    background-color: #0d1117;
    border: 1px solid #30363d;
    color: #e6edf3 !important;
    font-size: 14px;
    padding: 5px 12px;
    border-radius: 6px;
    width: 100%;
    line-height: 20px;
    transition: border-color 0.2s cubic-bezier(0.3, 0, 0.5, 1);
}

.gh-input-field:focus {
    background-color: #0d1117;
    border-color: #1f6feb;
    outline: none;
    box-shadow: 0 0 0 3px rgba(31, 111, 235, 0.3);
}

/* Tombol Masuk Hijau Solid */
.btn-gh-submit {
    background-color: #238636;
    color: #ffffff !important;
    border: 1px solid rgba(240, 246, 252, 0.1);
    font-size: 14px;
    font-weight: 500;
    padding: 5px 16px;
    border-radius: 6px;
    width: 100%;
    text-align: center;
    cursor: pointer;
    line-height: 20px;
}

.btn-gh-submit:hover {
    background-color: #2ea043;
}

.btn-gh-submit:active {
    background-color: #238636;
}

/* Banner Error Khas GitHub */
.gh-alert-danger {
    background-color: rgba(248, 81, 73, 0.1);
    border: 1px solid rgba(248, 81, 73, 0.4);
    color: #ff7b72;
    font-size: 13px;
    border-radius: 6px;
    padding: 16px;
    width: 100%;
    max-width: 308px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Footer Tambahan Bawah Box */
.gh-create-account-callout {
    border: 1px solid #30363d;
    border-radius: 6px;
    padding: 16px;
    text-align: center;
    width: 100%;
    max-width: 308px;
    margin-top: 16px;
    font-size: 14px;
    color: #8b949e;
}

.gh-link {
    color: #58a6ff;
    text-decoration: none;
}

.gh-link:hover {
    text-decoration: underline;
}
</style>
</head>

<body>

  <div class="gh-login-header">
    <svg class="gh-svg-logo" height="48" viewBox="0 0 16 16" width="48" fill="currentColor">
      <path d="M8 0c4.42 0 8 3.58 8 8a8.013 8.013 0 01-5.45 7.59c-.4.08-.55-.17-.55-.38 0-.27.01-1.13.01-2.2 0-.75-.25-1.23-.54-1.48 1.78-.2 3.65-.88 3.65-3.95 0-.88-.31-1.59-.82-2.15.08-.2.36-1.02-.08-2.12 0 0-.67-.22-2.2.82A7.48 7.48 0 008 3c-.68 0-1.36.09-2 .27-1.53-1.03-2.2-.82-2.2-.82-.44 1.1-.16 1.92-.08 2.12-.51.56-.82 1.28-.82 2.15 0 3.06 1.86 3.75 3.64 3.95-.23.2-.44.55-.51 1.07-.46.21-1.61.55-2.33-.66-.15-.24-.6-.83-1.23-.82-.67.01-.27.38.01.53.34.19.73.9.82 1.13.16.45.68 1.31 2.69.94 0 .67.01 1.3.01 1.49 0 .21-.15.45-.55.38A7.995 7.995 0 010 8c0-4.42 3.58-8 8-8z"></path>
    </svg>
    <h1 class="gh-login-title">Sign in to MangaZone</h1>
  </div>

  <?php if (!empty($error_message)): ?>
    <div class="gh-alert-danger">
      <svg aria-hidden="true" height="16" viewBox="0 0 16 16" width="16" fill="currentColor"><path d="M6.457 1.047c.659-1.234 2.427-1.234 3.086 0l6.03 11.3c.66 1.235-.236 2.653-1.544 2.653H1.971C.663 15 1.711-2.43 2.15 2.653l6.03-11.3zM1.97 13.5h12.058L8 2.114 1.971 13.5zM8 11.25a.75.75 0 110-1.5.75.75 0 010 1.5zm0-6a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 018 5.25z"></path></svg>
      <div><?= htmlspecialchars($error_message) ?></div>
    </div>
  <?php endif; ?>

  <div class="gh-auth-box">
    <form action="" method="POST">
        
        <div class="mb-3">
            <label for="username" class="gh-label">Username</label>
            <input type="text" 
                   name="username" 
                   id="username" 
                   class="gh-input-field" 
                   required 
                   autocomplete="off">
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="password" class="gh-label m-0">Password</label>
            </div>
            <input type="password" 
                   name="password" 
                   id="password" 
                   class="gh-input-field" 
                   required>
        </div>

        <button type="submit" class="btn-gh-submit mt-2">
            Sign in
        </button>

    </form>
  </div>

  <div class="gh-create-account-out">
      <div class="gh-create-account-callout">
          Protected area. <a href="../index.php" class="gh-link">Return to homepage</a>
      </div>
  </div>

</body>
</html>