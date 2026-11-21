<?php
require __DIR__ . '/../src/bootstrap.php';

if (llpc_is_moderator()) {
    llpc_redirect('dashboard.php');
}

$error      = '';
$configured = llpc_moderator_configured();
$rl         = llpc_config('rate_limit.login');

if (llpc_is_post()) {
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';

    if (!llpc_csrf_valid()) {
        $error = 'err.csrf';
    } elseif (!$configured) {
        $error = 'login.not_configured';
    } elseif (!llpc_rate_allow('login', (int) $rl['limit'], (int) $rl['window'], false)) {
        $error = 'login.throttled';
    } elseif (password_verify($password, (string) llpc_config('moderator_password_hash'))) {
        session_regenerate_id(true);
        $_SESSION['llpc_moderator'] = true;
        $_SESSION['llpc_last']      = time();
        llpc_audit('login_success');
        llpc_redirect('dashboard.php');
    } else {
        llpc_rate_record('login');
        llpc_audit('login_failed');
        $error = 'login.bad_password';
    }
}

llpc_page_start('login.title');
?>
<h1><?= th('login.title') ?></h1>
<p><?= th('login.intro') ?></p>

<?php if (!$configured): ?>
  <p class="notice info"><?= th('login.setup_required') ?> <code>php bin/set-moderator-password.php</code></p>
<?php endif; ?>
<?php if ($error !== '') { llpc_error_box($error); } ?>

<form method="post" class="panel stacked narrow">
  <?= llpc_csrf_field() ?>
  <label for="password"><?= th('login.password') ?></label>
  <input id="password" type="password" name="password" required autocomplete="current-password">
  <button type="submit"><?= th('login.submit') ?></button>
</form>
<?php llpc_page_end();
