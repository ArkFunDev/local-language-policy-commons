<?php
require __DIR__ . '/../src/bootstrap.php';

$samples     = llpc_load_samples();
$submissions = llpc_load_submissions();
$feedback    = llpc_load_feedback();

$accepted = $revision = $unreviewed = $verified = 0;
foreach ($submissions as $p) {
    $status = isset($p['status']) ? $p['status'] : 'unreviewed';
    if ($status === 'accepted') {
        $accepted++;
    } elseif ($status === 'needs_revision') {
        $revision++;
    } else {
        $unreviewed++;
    }
    if ((isset($p['verification_status']) ? $p['verification_status'] : 'pending') === 'verified') {
        $verified++;
    }
}

$fbApproved = $fbPending = 0;
foreach ($feedback as $f) {
    $st = isset($f['status']) ? $f['status'] : 'pending';
    if ($st === 'approved') {
        $fbApproved++;
    } elseif ($st === 'pending') {
        $fbPending++;
    }
}

$langs = [];
foreach (array_merge($samples, $submissions) as $p) {
    $name = llpc_language_name_of($p);
    $langs[$name] = (isset($langs[$name]) ? $langs[$name] : 0) + 1;
}
arsort($langs);

$totalCommunity = count($submissions);
$totalReviewed  = $accepted + $revision;
$percent        = $totalCommunity ? (int) round($totalReviewed / $totalCommunity * 100) : 0;
$totalAll       = count($samples) + $totalCommunity;

$cards = [
    ['dash.samples', count($samples)],
    ['dash.community', $totalCommunity],
    ['dash.languages', count($langs)],
    ['dash.accepted', $accepted],
    ['dash.needs_revision', $revision],
    ['dash.unreviewed', $unreviewed],
    ['dash.verified', $verified],
    ['dash.feedback_approved', $fbApproved],
    ['dash.feedback_pending', $fbPending],
];

llpc_page_start('dash.title');
?>
<h1><?= th('dash.title') ?></h1>

<div class="grid">
  <?php foreach ($cards as $c): ?>
    <div class="card stat"><div class="muted"><?= th($c[0]) ?></div><div class="num"><?= (int) $c[1] ?></div></div>
  <?php endforeach; ?>
</div>

<section class="card">
  <h2><?= th('dash.progress') ?></h2>
  <p><strong><?= (int) $percent ?>%</strong> <label for="prog" class="muted"><?= th('dash.progress_label') ?></label></p>
  <progress id="prog" value="<?= (int) $totalReviewed ?>" max="<?= max(1, (int) $totalCommunity) ?>"><?= (int) $percent ?>%</progress>
  <p class="muted"><?= th('dash.progress_text', ['reviewed' => $totalReviewed, 'total' => $totalCommunity]) ?></p>
</section>

<section class="card">
  <h2><?= th('dash.by_language') ?></h2>
  <?php foreach ($langs as $lang => $count): ?>
    <p><label for="lg-<?= h(md5($lang)) ?>"><?= h($lang) ?> — <?= (int) $count ?></label><br>
    <progress id="lg-<?= h(md5($lang)) ?>" value="<?= (int) $count ?>" max="<?= max(1, (int) $totalAll) ?>"></progress></p>
  <?php endforeach; ?>
</section>

<p class="muted"><?= th('dash.footer_note') ?></p>
<?php llpc_page_end();
