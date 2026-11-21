<?php
require __DIR__ . '/../src/bootstrap.php';

// Logout requires POST + CSRF so a third-party page cannot log a moderator out.
if (!llpc_is_post() || !llpc_csrf_valid()) {
    llpc_redirect('index.php');
}

if (llpc_is_moderator()) {
    llpc_audit('logout');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires'  => time() - 42000,
        'path'     => $params['path'],
        'domain'   => $params['domain'],
        'secure'   => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => 'Lax',
    ]);
}
session_destroy();
header('Location: index.php', true, 303);
exit;
