<?php
require __DIR__ . '/../src/bootstrap.php';
llpc_page_start('about.title');
?>
<h1><?= th('about.title') ?></h1>
<p><?= th('about.purpose') ?></p>

<h2><?= th('about.status_h') ?></h2>
<p><?= th('about.status') ?></p>

<h2><?= th('about.ai_h') ?></h2>
<p><?= th('about.ai') ?></p>

<h2><?= th('about.privacy_h') ?></h2>
<p><?= th('about.privacy') ?></p>

<h2><?= th('about.license_h') ?></h2>
<p><?= th('about.license', ['license' => llpc_config('data_license')]) ?></p>

<p><a href="<?= h((string) llpc_config('repository_url')) ?>" rel="noopener noreferrer"><?= th('footer.source') ?></a></p>
<?php llpc_page_end();
