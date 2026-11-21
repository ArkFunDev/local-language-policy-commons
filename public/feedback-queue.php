<?php
require __DIR__ . '/../src/bootstrap.php';
llpc_require_moderator();

$states = ['approved', 'rejected', 'pending'];

if (llpc_is_post()) {
    $error    = llpc_check_moderator_post();
    $id       = llpc_post('id');
    $decision = llpc_post('decision');
    if ($error === null) {
        if (!in_array($decision, $states, true)) {
            $error = 'err.invalid';
        } else {
            $result = llpc_update_feedback($id, ['status' => $decision, 'moderated_at' => date('c')]);
            if ($result === 'ok') {
                llpc_audit('feedback_moderation', $id, $decision);
                llpc_flash_set('ok', 'fq.saved');
                llpc_redirect(llpc_url_with([]));
            }
            $error = ($result === 'not_found') ? 'err.not_found' : 'err.save';
        }
    }
    llpc_flash_set('err', $error);
    llpc_redirect(llpc_url_with([]));
}

$filter = llpc_get('status');
if (!in_array($filter, $states, true)) {
    $filter = 'pending';
}
$items = [];
foreach (array_reverse(llpc_load_feedback()) as $f) {
    if ((isset($f['status']) ? $f['status'] : 'pending') === $filter) {
        $items[] = $f;
    }
}

llpc_page_start('fq.title');
?>
<h1><?= th('fq.title') ?></h1>
<p class="notice info"><?= th('fq.note') ?></p>

<p class="filters-inline">
  <?php foreach ($states as $i => $s): ?>
    <?= $i > 0 ? ' · ' : '' ?><a href="feedback-queue.php?status=<?= h($s) ?>"<?= $filter === $s ? ' aria-current="true"' : '' ?>><?= th('fq.' . $s) ?></a>
  <?php endforeach; ?>
</p>

<?php if (!$items): ?><div class="card"><?= th('fq.empty') ?></div><?php endif; ?>
<?php foreach ($items as $f):
    $fid = isset($f['id']) ? (string) $f['id'] : '';
    $pid = isset($f['proposal_id']) ? (string) $f['proposal_id'] : '';
    ?>
  <article class="card">
    <h2><?= h($fid) ?></h2>
    <p class="muted"><?= th('feedback.type.' . (isset($f['kind']) ? $f['kind'] : 'comment')) ?> ·
      <?= th('fq.proposal') ?>: <a href="proposal.php?id=<?= h(rawurlencode($pid)) ?>"><?= h($pid) ?></a></p>
    <p><?= nl2br(h(isset($f['content']) ? $f['content'] : '')) ?></p>
    <form method="post" class="inline-actions">
      <?= llpc_csrf_field() ?>
      <input type="hidden" name="id" value="<?= h($fid) ?>">
      <button type="submit" name="decision" value="approved"><?= th('fq.approve') ?></button>
      <button type="submit" name="decision" value="rejected" class="secondary"><?= th('fq.reject') ?></button>
      <?php if ($filter !== 'pending'): ?>
        <button type="submit" name="decision" value="pending" class="secondary"><?= th('fq.reopen') ?></button>
      <?php endif; ?>
    </form>
  </article>
<?php endforeach; ?>
<?php llpc_page_end();
