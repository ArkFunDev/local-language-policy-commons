<?php
require __DIR__ . '/../src/bootstrap.php';

$isMod    = llpc_is_moderator();
$category = trim((string) llpc_config('mediawiki.category'));
$label    = (string) llpc_config('mediawiki.source_label');

/* ---- Moderator-only import of a wiki page as a pending submission ---- */
if (llpc_is_post()) {
    $error = llpc_check_moderator_post();
    if ($error === null) {
        $title = llpc_post('title');
        $text  = llpc_post('extract');
        $gloss = llpc_post('indonesian_gloss');
        $input = [
            'language'         => llpc_post('language'),
            'language_other'   => '',
            'original_text'    => function_exists('mb_substr') ? mb_substr($text, 0, 2000, 'UTF-8') : substr($text, 0, 2000),
            'indonesian_gloss' => $gloss,
            'topic'            => llpc_post('topic'),
            'source_type'      => 'mediawiki',
            'source_url'       => $title !== '' ? llpc_mw_page_url($title) : '',
            'attribution'      => 'MediaWiki: ' . $label,
            'consent'          => '1', // moderators confirm the source licence allows reuse
        ];
        $result = llpc_validate_submission($input);
        if ($result['error'] !== null) {
            $error = $result['error'];
        } else {
            $status = llpc_add_submission($result['data']);
            if ($status === 'ok') {
                llpc_audit('mediawiki_import', $title);
                llpc_flash_set('ok', 'mw.imported');
                llpc_redirect(llpc_url_with([]));
            }
            $error = ($status === 'full') ? 'err.full' : 'err.save';
        }
    }
    llpc_flash_set('err', $error);
    llpc_redirect(llpc_url_with([]));
}

$query   = llpc_get('q');
$error   = '';
$vars    = [];
$pages   = [];
$mode    = '';

if ($query !== '') {
    if (llpc_len($query) > 100) {
        $error = 'mw.too_long';
    } else {
        $mode     = 'search';
        $response = llpc_mw_search($query);
    }
} elseif ($category !== '') {
    $mode     = 'category';
    $response = llpc_mw_category($category);
}

if ($mode !== '') {
    if (isset($response['error'])) {
        $error = $response['error'];
        $vars  = isset($response['vars']) ? $response['vars'] : [];
    } else {
        $pages = llpc_mw_pages($response);
    }
}

llpc_page_start('mw.title');
?>
<h1><?= th('mw.title') ?></h1>
<p class="muted"><?= th('mw.intro') ?></p>
<p class="notice info"><?= th('mw.notice', ['source' => $label]) ?></p>

<form method="get" class="panel stacked" role="search">
  <label for="q"><?= th('mw.search_label') ?></label>
  <input id="q" type="search" name="q" maxlength="100" value="<?= h($query) ?>" placeholder="<?= th('mw.placeholder') ?>">
  <button type="submit"><?= th('mw.search') ?></button>
</form>

<?php if ($error !== '') { llpc_error_box_vars($error, $vars); } ?>

<?php if ($mode !== '' && $error === ''): ?>
  <section class="panel">
    <h2><?= $mode === 'category' ? th('mw.category_heading') : th('mw.results') ?></h2>
    <?php if (!$pages): ?><p><?= th('mw.none') ?></p><?php endif; ?>
    <?php foreach ($pages as $i => $page):
        $title   = isset($page['title']) ? (string) $page['title'] : '';
        $extract = isset($page['extract']) ? (string) $page['extract'] : '';
        ?>
      <div class="result">
        <h3><?= h($title !== '' ? $title : '—') ?></h3>
        <p><?= h($extract !== '' ? $extract : t('mw.no_extract')) ?></p>
        <?php if ($title !== ''): ?>
          <p><a href="<?= h(llpc_mw_page_url($title)) ?>" target="_blank" rel="noopener noreferrer"><?= th('mw.open') ?></a></p>
        <?php endif; ?>

        <?php if ($isMod && $title !== ''): ?>
          <details>
            <summary><?= th('mw.import_heading') ?></summary>
            <form method="post" class="stacked">
              <?= llpc_csrf_field() ?>
              <input type="hidden" name="title" value="<?= h($title) ?>">
              <input type="hidden" name="extract" value="<?= h($extract) ?>">
              <label for="imp-lang-<?= (int) $i ?>"><?= th('submit.language') ?></label>
              <select id="imp-lang-<?= (int) $i ?>" name="language" required>
                <?php foreach (llpc_languages() as $code => $name): ?>
                  <?php if ($code === 'other') { continue; } ?>
                  <option value="<?= h($code) ?>"<?= $code === 'id' ? ' selected' : '' ?>><?= h($name . ' (' . $code . ')') ?></option>
                <?php endforeach; ?>
              </select>
              <label for="imp-topic-<?= (int) $i ?>"><?= th('submit.topic') ?></label>
              <select id="imp-topic-<?= (int) $i ?>" name="topic" required>
                <?php llpc_render_select_options(llpc_topics(), 'other', 'llpc_topic_label'); ?>
              </select>
              <label for="imp-gloss-<?= (int) $i ?>"><?= th('submit.gloss') ?></label>
              <textarea id="imp-gloss-<?= (int) $i ?>" name="indonesian_gloss" rows="3" maxlength="2000" required><?= h($extract) ?></textarea>
              <button type="submit"><?= th('mw.import_button') ?></button>
            </form>
          </details>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </section>
<?php endif; ?>
<?php llpc_page_end();
