<?php
require __DIR__ . '/../src/bootstrap.php';
llpc_require_moderator();

$statuses = ['unreviewed', 'accepted', 'needs_revision'];

if (llpc_is_post()) {
    $error    = llpc_check_moderator_post();
    $id       = llpc_post('id');
    $decision = llpc_post('status');
    if ($error === null) {
        if (!in_array($decision, $statuses, true)) {
            $error = 'err.invalid';
        } else {
            $result = llpc_update_submission($id, ['status' => $decision, 'reviewed_at' => date('c')]);
            if ($result === 'ok') {
                llpc_audit('review', $id, $decision);
                llpc_flash_set('ok', 'review.saved');
                llpc_redirect(llpc_url_with([]));
            }
            $error = ($result === 'not_found') ? 'err.not_found' : 'err.save';
        }
    }
    llpc_flash_set('err', $error);
    llpc_redirect(llpc_url_with([]));
}

$filter = llpc_get('status');
$items  = array_reverse(llpc_load_submissions());
if (in_array($filter, $statuses, true)) {
    $items = array_values(array_filter($items, function ($i) use ($filter) {
        return (isset($i['status']) ? $i['status'] : 'unreviewed') === $filter;
    }));
}

llpc_page_start('review.title');
?>
<h1><?= th('review.title') ?></h1>
<p class="notice info"><?= th('review.note') ?></p>

<p class="filters-inline">
  <a href="review.php"<?= $filter === '' ? ' aria-current="true"' : '' ?>><?= th('review.filter_all') ?></a>
  <?php foreach ($statuses as $s): ?>
    · <a href="review.php?status=<?= h($s) ?>"<?= $filter === $s ? ' aria-current="true"' : '' ?>><?= th('status.' . $s) ?></a>
  <?php endforeach; ?>
</p>

<?php if (!$items): ?><div class="card"><?= th('review.empty') ?></div><?php endif; ?>
<?php foreach ($items as $item):
    $n = llpc_normalize($item, 'community');
    $code = llpc_lang_code($n);
    ?>
  <article class="card">
    <h2><?= h($n['_id']) ?></h2>
    <p class="muted"><?= h($n['_lang_name']) ?> · <?= h(llpc_topic_label($n['_topic'])) ?></p>
    <p class="original"<?= $code !== '' ? ' lang="' . h($code) . '"' : '' ?>><?= nl2br(h($n['original_text'])) ?></p>
    <p><strong><?= th('common.indonesian_reference') ?>:</strong> <?= h($n['indonesian_gloss']) ?></p>
    <p class="badges"><?= llpc_render_badges($n) ?></p>
    <form method="post" class="inline-actions">
      <?= llpc_csrf_field() ?>
      <input type="hidden" name="id" value="<?= h($n['_id']) ?>">
      <label for="st-<?= h($n['_id']) ?>"><?= th('review.status_label') ?></label>
      <select id="st-<?= h($n['_id']) ?>" name="status">
        <?php foreach ($statuses as $s): ?>
          <option value="<?= h($s) ?>"<?= $n['_status'] === $s ? ' selected' : '' ?>><?= th('status.' . $s) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit"><?= th('common.save') ?></button>
    </form>
  </article>
<?php endforeach; ?>
<?php llpc_page_end();
