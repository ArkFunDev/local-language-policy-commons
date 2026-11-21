<?php
/**
 * Minimal internationalisation: English (en) and Indonesian (id).
 * To add a language, create lang/<code>.php and add the code to llpc_supported_langs().
 */

function llpc_supported_langs()
{
    return ['en', 'id'];
}

function llpc_lang()
{
    $supported = llpc_supported_langs();

    if (isset($_GET['lang']) && is_string($_GET['lang']) && in_array($_GET['lang'], $supported, true)) {
        $_SESSION['llpc_lang'] = $_GET['lang'];
        return $_GET['lang'];
    }
    if (isset($_SESSION['llpc_lang']) && in_array($_SESSION['llpc_lang'], $supported, true)) {
        return $_SESSION['llpc_lang'];
    }
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && is_string($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $first = strtolower(substr(trim($_SERVER['HTTP_ACCEPT_LANGUAGE']), 0, 2));
        if (in_array($first, $supported, true)) {
            return $first;
        }
    }
    $default = (string) llpc_config('default_lang', 'en');
    return in_array($default, $supported, true) ? $default : 'en';
}

function llpc_strings($lang)
{
    static $cache = [];
    if (!isset($cache[$lang])) {
        $file = LLPC_ROOT . '/lang/' . $lang . '.php';
        $cache[$lang] = is_file($file) ? require $file : [];
    }
    return $cache[$lang];
}

/** Translate a key. Placeholders use {name}. Returns RAW text: escape with h() or use th(). */
function t($key, $vars = [])
{
    $strings = llpc_strings(llpc_lang());
    if (isset($strings[$key])) {
        $text = $strings[$key];
    } else {
        $en   = llpc_strings('en');
        $text = isset($en[$key]) ? $en[$key] : $key;
    }
    foreach ($vars as $name => $value) {
        $text = str_replace('{' . $name . '}', (string) $value, $text);
    }
    return $text;
}

/** Translate and HTML-escape. */
function th($key, $vars = [])
{
    return h(t($key, $vars));
}
