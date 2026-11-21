<?php
require __DIR__ . '/../src/bootstrap.php';

$isMod = llpc_is_moderator();
$all   = llpc_visible_proposals();

if (llpc_is_post()) {
    $error = llpc_check_moderator_post();
    $a     = llpc_post('a');
    $b     = llpc_post('b');
    $dec   = llpc_post('decision');
    if ($error === null) {
        if (!in_array($dec, ['confirmed', 'rejected', 'reset'], true)
            || !llpc_find_proposal($a, $all) || !llpc_find_proposal($b, $all) || $a === $b) {
            $error = 'err.invalid';
        } elseif (!llpc_set_pair_review($a, $b, $dec)) {
            $error = 'err.save';
        } else {
            llpc_audit('similarity_review', llpc_pair_key($a, $b), $dec);
            llpc_flash_set('ok', 'discovery.pair_saved');
            llpc_redirect('discovery.php?id=' . rawurlencode($a));
        }
    }
    llpc_flash_set('err', $error);
    llpc_redirect('discovery.php' . ($a !== '' ? '?id=' . rawurlencode($a) : ''));
}

$selectedId = llpc_get('id');
if ($selectedId === '' && $all) {
    $selectedId = $all[0]['_id'];
}
$current = llpc_find_proposal($selectedId, $all);
$matches = $current ? llpc_similar_proposals($current, $all, 8) : [];

llpc_page_start('discovery.title');
?>
<h1><?= th('discovery.title') ?></h1>
<p class="notice info"><strong><?= th('discovery.experimental') ?></strong> <?= th('discovery.note') ?></p>

<form method="get" class="panel">
  <label for="id"><?= th('discovery.select') ?></label>
  <select id="id" name="id">
    <?php foreach ($all as $p): ?>
      <option value="<?= h($p['_id']) ?>"<?= $p['_id'] === $selectedId ? ' selected' : '' ?>><?= h($p['_id'] . ' — ' . $p['_lang_name'] . ' — ' . llpc_topic_label($p['_topic'])) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit"><?= th('discovery.go') ?></button>
</form>

<?php if ($current): ?>
  <h2><?= th('discovery.selected') ?></h2>
  <?php llpc_render_card($current, 3); ?>

  <h2><?= th('proposal.related') ?></h2>
  <?php
  $shown = 0;
  foreach ($matches as $m) {
      if ($m['_pair_status'] === 'rejected' && !$isMod) {
          continue;
      }
      $shown++;
      llpc_render_card($m, 3);
      ?>
      <p class="pair-meta">
        <span class="badge"><?= th('discovery.overlap') ?>: <?= (int) $m['_score'] ?>%</span>
        <span class="badge"><?= th('discovery.pair.' . $m['_pair_status']) ?></span>
        <?php if ($m['_same_topic']): ?><span class="badge"><?= th('discovery.same_topic') ?></span><?php endif; ?>
        <?php if ($m['_shared']): ?>
          <span class="muted"><?= th('discovery.shared_terms') ?>: <?= h(implode(', ', $m['_shared'])) ?></span>
        <?php endif; ?>
      </p>
      <?php if ($isMod): ?>
        <form method="post" class="inline-actions">
          <?= llpc_csrf_field() ?>
          <input type="hidden" name="a" value="<?= h($current['_id']) ?>">
          <input type="hidden" name="b" value="<?= h($m['_id']) ?>">
          <button type="submit" name="decision" value="confirmed"><?= th('discovery.confirm') ?></button>
          <button type="submit" name="decision" value="rejected" class="secondary"><?= th('discovery.reject') ?></button>
          <button type="submit" name="decision" value="reset" class="secondary"><?= th('discovery.reset') ?></button>
        </form>
      <?php endif; ?>
      <?php
  }
  if (!$shown) {
      echo '<p>' . th('discovery.no_matches') . '</p>';
  }
  ?>
<?php endif; ?>

<details class="panel">
  <summary><?= th('discovery.method') ?></summary>
  <p><?= th('discovery.method_text') ?></p>
</details>
<?php llpc_page_end();
