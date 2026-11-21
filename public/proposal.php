<?php
require __DIR__ . '/../src/bootstrap.php';

$id       = llpc_get('id');
$visible  = llpc_visible_proposals();
$proposal = llpc_find_proposal($id, $visible);

if (!$proposal) {
    http_response_code(404);
    llpc_page_start('proposal.not_found_title');
    echo '<h1>' . th('proposal.not_found_title') . '</h1>';
    echo '<p>' . th('proposal.not_found') . ' <a href="index.php">' . th('common.back_to_explorer') . '</a></p>';
    llpc_page_end();
    exit;
}

$related  = llpc_similar_proposals($proposal, $visible, 5);
$feedback = llpc_approved_feedback_for($proposal['_id']);
$isMod    = llpc_is_moderator();
$code     = llpc_lang_code($proposal);
$langAttr = ($code !== '') ? ' lang="' . h($code) . '"' : '';

llpc_page_start('proposal.title', ['id' => $proposal['_id']]);
?>
<p><a href="index.php">← <?= th('common.back_to_explorer') ?></a></p>
<h1><?= th('proposal.title', ['id' => $proposal['_id']]) ?></h1>
<p class="badges"><?= llpc_render_badges($proposal) ?></p>

<section class="card">
  <p class="muted"><?= h($proposal['_lang_name']) ?> · <?= h(llpc_topic_label($proposal['_topic'])) ?></p>
  <h2><?= th('common.original') ?></h2>
  <p class="original"<?= $langAttr ?>><?= nl2br(h($proposal['original_text'])) ?></p>
  <h2><?= th('common.indonesian_reference') ?></h2>
  <p><?= nl2br(h($proposal['indonesian_gloss'])) ?></p>

  <?php if ($proposal['_origin'] === 'sample'): ?>
    <p class="notice info"><?= th('proposal.sample_note') ?></p>
  <?php else: ?>
    <dl class="meta">
      <dt><?= th('proposal.source') ?></dt>
      <dd><?= th('submit.source_type.' . (isset($proposal['source_type']) ? $proposal['source_type'] : 'community')) ?>
        <?php if (!empty($proposal['source_url'])): ?>
          · <a href="<?= h($proposal['source_url']) ?>" target="_blank" rel="noopener noreferrer"><?= th('verify.open_source') ?></a>
        <?php endif; ?>
      </dd>
      <dt><?= th('proposal.attribution') ?></dt>
      <dd><?= !empty($proposal['attribution']) ? h($proposal['attribution']) : th('common.not_provided') ?></dd>
      <?php if (!empty($proposal['submitted_at'])): ?>
        <dt><?= th('proposal.submitted') ?></dt>
        <dd><?= h(substr((string) $proposal['submitted_at'], 0, 10)) ?></dd>
      <?php endif; ?>
    </dl>
  <?php endif; ?>
  <p><a class="button" href="feedback.php?id=<?= h(rawurlencode($proposal['_id'])) ?>"><?= th('common.give_feedback') ?></a></p>
</section>

<section>
  <h2><?= th('proposal.related') ?></h2>
  <p class="muted"><?= th('proposal.related_note') ?></p>
  <?php
  $shown = 0;
  foreach ($related as $m) {
      if ($m['_pair_status'] === 'rejected' && !$isMod) {
          continue;
      }
      $shown++;
      llpc_render_card($m, 3);
      echo '<p class="muted pair-meta">' . th('discovery.pair.' . $m['_pair_status']) . ' · ' . th('discovery.overlap') . ': ' . (int) $m['_score'] . '%</p>';
  }
  if (!$shown) {
      echo '<p>' . th('proposal.no_related') . '</p>';
  }
  ?>
</section>

<section>
  <h2><?= th('proposal.feedback_heading') ?></h2>
  <?php if (!$feedback): ?><p><?= th('proposal.no_feedback') ?></p><?php endif; ?>
  <?php foreach ($feedback as $f): ?>
    <div class="card">
      <p><strong><?= th('feedback.type.' . (isset($f['kind']) ? $f['kind'] : 'comment')) ?></strong></p>
      <p><?= nl2br(h(isset($f['content']) ? $f['content'] : '')) ?></p>
    </div>
  <?php endforeach; ?>
</section>
<?php llpc_page_end();
