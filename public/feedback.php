<?php
require __DIR__ . '/../src/bootstrap.php';

$id       = llpc_get('id');
if ($id === '') {
    $id = llpc_post('proposal_id');
}
$visible  = llpc_visible_proposals();
$proposal = llpc_find_proposal($id, $visible);
$error    = '';
$kinds    = ['comment', 'language_correction', 'context_note'];
$content  = '';
$kind     = 'comment';

if ($proposal && llpc_is_post()) {
    $kind    = llpc_post('kind');
    $content = llpc_post('content');
    $rl      = llpc_config('rate_limit.feedback');

    if (!llpc_csrf_valid()) {
        $error = 'err.csrf';
    } elseif (llpc_honeypot_tripped()) {
        llpc_flash_set('ok', 'feedback.saved');
        llpc_redirect('feedback.php?id=' . rawurlencode($id));
    } elseif (!llpc_rate_allow('feedback', (int) $rl['limit'], (int) $rl['window'], false)) {
        $error = 'err.rate';
    } elseif (!in_array($kind, $kinds, true)) {
        $error = 'err.invalid';
    } elseif ($content === '' || llpc_len($content) > 1500) {
        $error = 'feedback.err_content';
    } else {
        $status = llpc_add_feedback($proposal['_id'], $kind, $content);
        if ($status === 'ok') {
            llpc_rate_record('feedback');
            llpc_flash_set('ok', 'feedback.saved');
            llpc_redirect('feedback.php?id=' . rawurlencode($id));
        }
        $error = ($status === 'full') ? 'err.full' : 'err.save';
    }
}

$approved = $proposal ? llpc_approved_feedback_for($proposal['_id']) : [];
if (!$proposal) {
    http_response_code(404);
}

llpc_page_start('feedback.title');
?>
<h1><?= th('feedback.title') ?></h1>

<?php if (!$proposal): ?>
  <div class="card"><p><?= th('proposal.not_found') ?> <a href="index.php"><?= th('common.back_to_explorer') ?></a></p></div>
<?php else: ?>
  <p><a href="proposal.php?id=<?= h(rawurlencode($proposal['_id'])) ?>">← <?= th('feedback.back_to_proposal') ?></a></p>
  <?php llpc_render_card($proposal); ?>

  <p class="notice info"><?= th('feedback.note') ?></p>
  <?php if ($error !== '') { llpc_error_box($error); } ?>

  <form method="post" class="panel stacked">
    <?= llpc_csrf_field() ?>
    <?= llpc_honeypot_field() ?>
    <input type="hidden" name="proposal_id" value="<?= h($proposal['_id']) ?>">
    <label for="kind"><?= th('feedback.type') ?></label>
    <select id="kind" name="kind">
      <?php foreach ($kinds as $k): ?>
        <option value="<?= h($k) ?>"<?= $kind === $k ? ' selected' : '' ?>><?= th('feedback.type.' . $k) ?></option>
      <?php endforeach; ?>
    </select>
    <label for="content"><?= th('feedback.your') ?></label>
    <textarea id="content" name="content" maxlength="1500" rows="5" required><?= h($content) ?></textarea>
    <button type="submit"><?= th('feedback.submit') ?></button>
  </form>

  <h2><?= th('feedback.history') ?></h2>
  <?php if (!$approved): ?><p><?= th('feedback.none') ?></p><?php endif; ?>
  <?php foreach ($approved as $f): ?>
    <div class="card">
      <p><strong><?= th('feedback.type.' . (isset($f['kind']) ? $f['kind'] : 'comment')) ?></strong></p>
      <p><?= nl2br(h(isset($f['content']) ? $f['content'] : '')) ?></p>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
<?php llpc_page_end();
