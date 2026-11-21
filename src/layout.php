<?php
/**
 * Shared page layout and small view helpers.
 */

/** Build a URL to the current page with query parameters changed (null removes a parameter). */
function llpc_url_with($changes)
{
    // Relative to the current script so redirects can never leave this site.
    $path = basename(isset($_SERVER['SCRIPT_NAME']) ? (string) $_SERVER['SCRIPT_NAME'] : 'index.php');
    $q    = $_GET;
    foreach ($changes as $k => $v) {
        if ($v === null) {
            unset($q[$k]);
        } else {
            $q[$k] = $v;
        }
    }
    $qs = http_build_query($q);
    return $path . ($qs !== '' ? '?' . $qs : '');
}

function llpc_nav_link($file, $labelKey, $current)
{
    $aria = ($file === $current) ? ' aria-current="page"' : '';
    return '<a href="' . h($file) . '"' . $aria . '>' . th($labelKey) . '</a>';
}

function llpc_page_start($titleKey, $titleVars = [])
{
    $current   = basename(isset($_SERVER['SCRIPT_NAME']) ? (string) $_SERVER['SCRIPT_NAME'] : '');
    $lang      = llpc_lang();
    $title     = t($titleKey, $titleVars) . ' – ' . t('app.name');
    $moderator = llpc_is_moderator();

    header('Content-Type: text/html; charset=utf-8');
    if ($moderator) {
        header('Cache-Control: no-store');
    }

    echo "<!doctype html>\n";
    echo '<html lang="' . h($lang) . '"><head>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<meta name="color-scheme" content="light dark">';
    echo '<meta name="description" content="' . th('app.tagline') . '">';
    echo '<title>' . h($title) . '</title>';
    echo '<link rel="icon" href="data:,">';
    echo '<link rel="stylesheet" href="assets/style.css">';
    echo '</head><body>';
    echo '<a class="skip" href="#main">' . th('nav.skip') . '</a>';

    echo '<header class="site-header"><div class="wrap">';
    echo '<p class="brand"><a href="index.php">' . th('app.name') . '</a></p>';
    echo '<nav aria-label="' . th('nav.main') . '">';
    echo llpc_nav_link('index.php', 'nav.explorer', $current);
    echo llpc_nav_link('discovery.php', 'nav.discovery', $current);
    if (llpc_config('public_submissions')) {
        echo llpc_nav_link('submit.php', 'nav.submit', $current);
    }
    echo llpc_nav_link('dashboard.php', 'nav.dashboard', $current);
    echo llpc_nav_link('mediawiki.php', 'nav.mediawiki', $current);
    echo llpc_nav_link('export.php', 'nav.data', $current);
    echo llpc_nav_link('about.php', 'nav.about', $current);
    echo '</nav>';

    echo '<nav class="nav-secondary" aria-label="' . th('nav.account') . '">';
    if ($moderator) {
        echo llpc_nav_link('verify.php', 'nav.verify', $current);
        echo llpc_nav_link('review.php', 'nav.review', $current);
        echo llpc_nav_link('feedback-queue.php', 'nav.feedback_queue', $current);
        echo llpc_nav_link('status.php', 'nav.status', $current);
        echo '<form method="post" action="logout.php" class="inline">' . llpc_csrf_field()
            . '<button type="submit" class="linkbutton">' . th('nav.logout') . '</button></form>';
    } else {
        echo llpc_nav_link('login.php', 'nav.login', $current);
    }
    echo '<span class="lang-switch" role="group" aria-label="' . th('nav.language') . '">';
    foreach (llpc_supported_langs() as $code) {
        $aria = ($code === $lang) ? ' aria-current="true"' : '';
        echo '<a href="' . h(llpc_url_with(['lang' => $code])) . '" hreflang="' . h($code) . '" lang="' . h($code) . '"' . $aria . '>' . h(strtoupper($code)) . '</a>';
    }
    echo '</span>';
    echo '</nav>';
    echo '</div></header>';

    echo '<main id="main" class="wrap">';

    $flash = llpc_flash_take();
    if ($flash) {
        $cls = ($flash['type'] === 'ok') ? 'ok' : 'err';
        echo '<p class="notice ' . $cls . '" role="status">' . th($flash['key']) . '</p>';
    }
}

function llpc_page_end()
{
    $repo = (string) llpc_config('repository_url');
    echo '</main>';
    echo '<footer class="site-footer"><div class="wrap">';
    echo '<p>' . th('footer.disclaimer') . '</p>';
    echo '<p>' . th('footer.version', ['version' => LLPC_VERSION]) . ' · '
        . '<a href="' . h($repo) . '" rel="noopener noreferrer">' . th('footer.source') . '</a> · '
        . '<a href="about.php">' . th('nav.about') . '</a></p>';
    echo '</div></footer>';
    echo '</body></html>';
}

function llpc_error_box($key)
{
    echo '<p class="notice err" role="alert">' . th($key) . '</p>';
}

/** Badges describing origin, review state and verification of a proposal. */
function llpc_render_badges($p)
{
    $out = '';
    if ($p['_origin'] === 'community') {
        $out .= '<span class="badge badge-community">' . th('badge.community') . '</span>';
        $out .= '<span class="badge">' . th('status.' . $p['_status']) . '</span>';
        $v    = isset($p['verification_status']) ? (string) $p['verification_status'] : 'pending';
        $out .= '<span class="badge">' . th('verification.label') . ': ' . th('verification.' . $v) . '</span>';
        if (!empty($p['language_verified'])) {
            $out .= '<span class="badge badge-ok">' . th('badge.language_verified') . '</span>';
        }
    } else {
        $out .= '<span class="badge badge-sample">' . th('badge.sample') . '</span>';
    }
    return $out;
}

/** Render one proposal as a card. */
function llpc_render_card($p, $headingLevel = 2)
{
    $code = llpc_lang_code($p);
    $lang = ($code !== '') ? ' lang="' . h($code) . '"' : '';
    $cls  = ($p['_origin'] === 'community') ? 'community' : 'sample';
    $tag  = 'h' . (int) $headingLevel;

    echo '<article class="card proposal ' . $cls . '">';
    echo '<' . $tag . '><a href="proposal.php?id=' . h(rawurlencode($p['_id'])) . '">' . h($p['_id']) . '</a></' . $tag . '>';
    echo '<p class="muted">' . h($p['_lang_name']) . ' · ' . h(llpc_topic_label($p['_topic'])) . '</p>';
    echo '<p class="original"' . $lang . '>' . nl2br(h($p['original_text'])) . '</p>';
    echo '<p><strong>' . th('common.indonesian_reference') . ':</strong> ' . h($p['indonesian_gloss']) . '</p>';
    echo '<p class="badges">' . llpc_render_badges($p) . '</p>';
    echo '</article>';
}

function llpc_render_pagination($page, $pages)
{
    if ($pages <= 1) {
        return;
    }
    echo '<nav class="pagination" aria-label="' . th('common.pagination') . '">';
    if ($page > 1) {
        echo '<a href="' . h(llpc_url_with(['page' => $page - 1])) . '">' . th('common.previous') . '</a>';
    }
    echo '<span>' . th('common.page_of', ['page' => $page, 'pages' => $pages]) . '</span>';
    if ($page < $pages) {
        echo '<a href="' . h(llpc_url_with(['page' => $page + 1])) . '">' . th('common.next') . '</a>';
    }
    echo '</nav>';
}

function llpc_render_select_options($options, $selected, $labelCallback = null)
{
    foreach ($options as $value) {
        $label = $labelCallback ? call_user_func($labelCallback, $value) : $value;
        $sel   = ((string) $value === (string) $selected) ? ' selected' : '';
        echo '<option value="' . h($value) . '"' . $sel . '>' . h($label) . '</option>';
    }
}

/** Like llpc_error_box() but with placeholders for the translated message. */
function llpc_error_box_vars($key, $vars)
{
    echo '<p class="notice err" role="alert">' . th($key, $vars) . '</p>';
}
