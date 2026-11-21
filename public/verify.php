<?php
require __DIR__ . '/../src/bootstrap.php';
llpc_require_moderator();

$states = ['pending', 'verified', 'needs_clarification'];

if (llpc_is_post()) {
    $error    = llpc_check_moderator_post();
    $id       = llpc_post('id');
    $decision = llpc_post('verification_status');
    if ($error === null) {
        if (!in_array($decision, $states, true)) {
            $error = 'err.invalid';
        } else {
            $result = llpc_update_submission($id, [
                'verification_status' => $decision,
                'language_verified'   => llpc_post('language_verified') === '1',
                'verified_at'         => date('c'),
            ]);
            if ($result === 'ok') {
                llpc_audit('verify', $id, $decision);
                llpc_flash_set('ok', 'verify.saved');
                llpc_redirect('verify.php');
            }
            $error = ($result === 'not_found') ? 'err.not_found' : 'err.save';
        }
    }
    llpc_flash_set('err', $error);
    llpc_redirect('verify.php');
}

$items = array_reverse(llpc_load_submissions());

llpc_page_start('verify.title');
?>
<h1><?= th('verify.title') ?></h1>
<p class="notice info"><?= th('verify.note') ?></p>

<?php if (!$items): ?><div class="card"><?= th('verify.empty') ?></div><?php endif; ?>
<?php foreach ($items as $item):
    $n    = llpc_normalize($item, 'community');
    $code = llpc_lang_code($n);
    $ver  = isset($n['verification_status']) ? (string) $n['verification_status'] : 'pending';
    ?>
  <article class="card">
    <h2><?= h($n['_id']) ?></h2>
    <p class="muted"><?= h($n['_lang_name']) ?> · <?= h(llpc_topic_label($n['_topic'])) ?></p>
    <p class="original"<?= $code !== '' ? ' lang="' . h($code) . '"' : '' ?>><?= nl2br(h($n['original_text'])) ?></p>
    <p><strong><?= th('common.indonesian_reference') ?>:</strong> <?= h($n['indonesian_gloss']) ?></p>
    <hr>
    <p><?= th('verify.source_type') ?>: <?= th('submit.source_type.' . (isset($n['source_type']) ? $n['source_type'] : 'community')) ?></p>
    <p><?= th('verify.attribution') ?>: <?= !empty($n['attribution']) ? h($n['attribution']) : th('common.not_provided') ?></p>
    <p><?= th('verify.source_url') ?>:
      <?php if (!empty($n['source_url'])): ?>
        <a href="<?= h($n['source_url']) ?>" target="_blank" rel="noopener noreferrer"><?= th('verify.open_source') ?></a>
      <?php else: ?><?= th('common.not_provided') ?><?php endif; ?></p>
    <p><?= th('verify.current') ?>: <strong><?= th('verification.' . $ver) ?></strong></p>
    <form method="post" class="stacked">
      <?= llpc_csrf_field() ?>
      <input type="hidden" name="id" value="<?= h($n['_id']) ?>">
      <label for="vs-<?= h($n['_id']) ?>"><?= th('verify.status_label') ?></label>
      <select id="vs-<?= h($n['_id']) ?>" name="verification_status">
        <?php foreach ($states as $s): ?>
          <option value="<?= h($s) ?>"<?= $ver === $s ? ' selected' : '' ?>><?= th('verification.' . $s) ?></option>
        <?php endforeach; ?>
      </select>
      <label class="check"><input type="checkbox" name="language_verified" value="1"<?= !empty($n['language_verified']) ? ' checked' : '' ?>>
        <span><?= th('verify.language_checked') ?></span></label>
      <button type="submit"><?= th('common.save') ?></button>
    </form>
  </article>
<?php endforeach; ?>
<?php llpc_page_end();
