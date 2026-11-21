<?php
require __DIR__ . '/../src/bootstrap.php';

$enabled = (bool) llpc_config('public_submissions');
$error   = '';
$values  = [
    'language' => '', 'language_other' => '', 'original_text' => '', 'indonesian_gloss' => '',
    'topic' => '', 'source_type' => 'community', 'source_url' => '', 'attribution' => '', 'consent' => '',
];

if ($enabled && llpc_is_post()) {
    foreach ($values as $k => $v) {
        $values[$k] = llpc_post($k);
    }
    $rl = llpc_config('rate_limit.submit');

    if (!llpc_csrf_valid()) {
        $error = 'err.csrf';
    } elseif (llpc_honeypot_tripped()) {
        // Pretend success to bots without storing anything.
        llpc_flash_set('ok', 'submit.saved');
        llpc_redirect('submit.php');
    } elseif (!llpc_rate_allow('submit', (int) $rl['limit'], (int) $rl['window'], false)) {
        $error = 'err.rate';
    } else {
        $result = llpc_validate_submission($values);
        if ($result['error'] !== null) {
            $error = $result['error'];
        } else {
            $status = llpc_add_submission($result['data']);
            if ($status === 'ok') {
                llpc_rate_record('submit');
                llpc_flash_set('ok', 'submit.saved');
                llpc_redirect('submit.php');
            }
            $error = ($status === 'full') ? 'err.full' : 'err.save';
        }
    }
}

llpc_page_start('submit.title');
?>
<h1><?= th('submit.title') ?></h1>

<?php if (!$enabled): ?>
  <p class="notice info"><?= th('submit.disabled') ?></p>
<?php else: ?>
  <p class="notice info"><?= th('submit.privacy_note') ?></p>
  <?php if ($error !== '') { llpc_error_box($error); } ?>

  <form method="post" class="panel stacked">
    <?= llpc_csrf_field() ?>
    <?= llpc_honeypot_field() ?>

    <label for="language"><?= th('submit.language') ?></label>
    <select id="language" name="language" required>
      <option value=""><?= th('submit.choose') ?></option>
      <?php foreach (llpc_languages() as $code => $name): ?>
        <option value="<?= h($code) ?>"<?= $values['language'] === $code ? ' selected' : '' ?>><?= h($name . ($code !== 'other' ? ' (' . $code . ')' : '')) ?></option>
      <?php endforeach; ?>
    </select>

    <label for="language_other"><?= th('submit.language_other') ?></label>
    <input id="language_other" name="language_other" maxlength="80" value="<?= h($values['language_other']) ?>">

    <label for="original_text"><?= th('submit.original') ?></label>
    <textarea id="original_text" name="original_text" maxlength="2000" rows="5" required><?= h($values['original_text']) ?></textarea>

    <label for="indonesian_gloss"><?= th('submit.gloss') ?></label>
    <textarea id="indonesian_gloss" name="indonesian_gloss" maxlength="2000" rows="4" required><?= h($values['indonesian_gloss']) ?></textarea>

    <label for="topic"><?= th('submit.topic') ?></label>
    <select id="topic" name="topic" required>
      <option value=""><?= th('submit.choose') ?></option>
      <?php llpc_render_select_options(llpc_topics(), $values['topic'], 'llpc_topic_label'); ?>
    </select>

    <label for="source_type"><?= th('submit.source_type') ?></label>
    <select id="source_type" name="source_type">
      <?php foreach (llpc_source_types() as $type): ?>
        <option value="<?= h($type) ?>"<?= $values['source_type'] === $type ? ' selected' : '' ?>><?= th('submit.source_type.' . $type) ?></option>
      <?php endforeach; ?>
    </select>

    <label for="source_url"><?= th('submit.source_url') ?></label>
    <input id="source_url" type="url" name="source_url" maxlength="1000" value="<?= h($values['source_url']) ?>" placeholder="https://example.org">

    <label for="attribution"><?= th('submit.attribution') ?></label>
    <input id="attribution" name="attribution" maxlength="200" value="<?= h($values['attribution']) ?>">

    <label class="check"><input type="checkbox" name="consent" value="1" required<?= $values['consent'] === '1' ? ' checked' : '' ?>>
      <span><?= th('submit.consent', ['license' => llpc_config('data_license')]) ?></span></label>

    <button type="submit"><?= th('submit.submit') ?></button>
  </form>
<?php endif; ?>
<?php llpc_page_end();
