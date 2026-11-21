<?php
require __DIR__ . '/../src/bootstrap.php';

$q            = llpc_get('q');
$langFilter   = llpc_get('language');
$topicFilter  = llpc_get('topic');
$page         = max(1, (int) llpc_get('page'));
$perPage      = 20;
$all          = llpc_visible_proposals();

$languages = [];
$topics    = [];
foreach ($all as $p) {
    $languages[$p['_lang_name']] = true;
    $topics[$p['_topic']]        = true;
}
$languages = array_keys($languages);
$topics    = array_keys($topics);
sort($languages);
sort($topics);

$filtered = array_values(array_filter($all, function ($p) use ($q, $langFilter, $topicFilter) {
    if ($langFilter !== '' && $p['_lang_name'] !== $langFilter) {
        return false;
    }
    if ($topicFilter !== '' && $p['_topic'] !== $topicFilter) {
        return false;
    }
    $hay = $p['original_text'] . ' ' . $p['indonesian_gloss'] . ' ' . $p['_topic'] . ' ' . llpc_topic_label($p['_topic']);
    return llpc_contains($hay, $q);
}));

$total = count($filtered);
$pages = max(1, (int) ceil($total / $perPage));
$page  = min($page, $pages);
$slice = array_slice($filtered, ($page - 1) * $perPage, $perPage);

llpc_page_start('index.title');
?>
<h1><?= th('index.title') ?></h1>
<p class="muted"><?= th('app.tagline') ?></p>
<p class="notice info"><?= llpc_is_moderator() ? th('index.moderator_view') : th('index.public_view') ?></p>

<form method="get" class="panel filters" role="search">
  <label for="q"><?= th('common.search') ?></label>
  <input id="q" type="search" name="q" value="<?= h($q) ?>" maxlength="100" placeholder="<?= th('index.search_placeholder') ?>">
  <label for="language"><?= th('common.language') ?></label>
  <select id="language" name="language">
    <option value=""><?= th('common.all_languages') ?></option>
    <?php llpc_render_select_options($languages, $langFilter); ?>
  </select>
  <label for="topic"><?= th('common.topic') ?></label>
  <select id="topic" name="topic">
    <option value=""><?= th('common.all_topics') ?></option>
    <?php llpc_render_select_options($topics, $topicFilter, 'llpc_topic_label'); ?>
  </select>
  <button type="submit"><?= th('common.filter') ?></button>
</form>

<p><strong><?= (int) $total ?></strong> <?= th('common.results') ?> · <?= th('index.legend') ?></p>

<?php foreach ($slice as $p) { llpc_render_card($p); } ?>
<?php if (!$slice): ?><p><?= th('common.no_results') ?></p><?php endif; ?>

<?php llpc_render_pagination($page, $pages); ?>

<p class="muted"><?= th('index.footer_note') ?></p>
<?php llpc_page_end();
