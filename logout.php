
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// پاک کردن معلومات نشست کاربر
$_SESSION = [];

// پاک کردن کوکی نشست
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $params['path'] ?: '/',
        'domain'   => $params['domain'],
        'secure'   => (bool) $params['secure'],
        'httponly' => (bool) $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax'
    ]);
}

session_destroy();

// برگشت به صفحه ورود
header('Location: login.php');
exit;
