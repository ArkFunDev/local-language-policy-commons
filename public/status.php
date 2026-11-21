<?php
require __DIR__ . '/../src/bootstrap.php';
llpc_require_moderator();

$storage   = llpc_storage_dir();
$docRoot   = isset($_SERVER['DOCUMENT_ROOT']) ? realpath((string) $_SERVER['DOCUMENT_ROOT']) : false;
$storageRp = realpath($storage);

$weak = false;
if (llpc_moderator_configured()) {
    foreach (llpc_weak_passwords() as $candidate) {
        if (password_verify($candidate, (string) llpc_config('moderator_password_hash'))) {
            $weak = true;
            break;
        }
    }
}

$checks = [
    'status.check.php'            => version_compare(PHP_VERSION, '7.4.0', '>='),
    'status.check.json'           => extension_loaded('json'),
    'status.check.mbstring'       => extension_loaded('mbstring'),
    'status.check.curl'           => extension_loaded('curl'),
    'status.check.sample'         => is_readable(LLPC_ROOT . '/sample-data/proposals.json'),
    'status.check.storage'        => is_dir($storage) && is_writable($storage),
    'status.check.storage_hidden' => ($docRoot && $storageRp) ? (strpos($storageRp, $docRoot) !== 0) : false,
    'status.check.password_set'   => llpc_moderator_configured(),
    'status.check.password_strong' => llpc_moderator_configured() && !$weak,
    'status.check.https'          => llpc_is_https(),
    'status.check.debug_off'      => !llpc_config('debug'),
];

llpc_page_start('status.title');
?>
<h1><?= th('status.title') ?></h1>
<p><?= th('status.php_version') ?>: <?= h(PHP_VERSION) ?> · <?= th('footer.version', ['version' => LLPC_VERSION]) ?></p>
<ul class="checks">
  <?php foreach ($checks as $label => $ok): ?>
    <li class="<?= $ok ? 'ok' : 'bad' ?>"><strong><?= $ok ? th('status.pass') : th('status.check') ?></strong> — <?= th($label) ?></li>
  <?php endforeach; ?>
</ul>
<p class="notice info"><?= th('status.note') ?></p>
<?php llpc_page_end();
