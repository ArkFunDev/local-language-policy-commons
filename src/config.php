<?php
/**
 * Configuration loader.
 *
 * Order of precedence (lowest to highest):
 *   1. Built-in defaults (below)
 *   2. config/config.local.php (git-ignored, created by bin/set-moderator-password.php)
 *   3. Environment variables (LLPC_*)
 */

function llpc_config_defaults()
{
    return [
        'moderator_password_hash' => '',
        'default_lang'            => 'en',
        'storage_dir'             => LLPC_ROOT . '/storage',
        'public_submissions'      => true,
        'data_license'            => 'CC BY 4.0',
        'max_records'             => 5000,
        'trust_proxy_headers'     => false,
        'debug'                   => false,
        'repository_url'          => 'https://github.com/ArkFunDev/local-language-policy-commons',
        'mediawiki'               => [
            'api_url'      => 'https://id.wikipedia.org/w/api.php',
            'page_base'    => 'https://id.wikipedia.org/wiki/',
            'source_label' => 'Indonesian Wikipedia (demo source)',
            'category'     => '',
            'cache_ttl'    => 300,
            'contact'      => 'https://github.com/ArkFunDev/local-language-policy-commons',
        ],
        'rate_limit'              => [
            'submit'   => ['limit' => 5,  'window' => 3600],
            'feedback' => ['limit' => 10, 'window' => 3600],
            'login'    => ['limit' => 5,  'window' => 900],
        ],
    ];
}

function llpc_config_all()
{
    static $cfg = null;
    if ($cfg !== null) {
        return $cfg;
    }

    $cfg = llpc_config_defaults();

    $file = LLPC_ROOT . '/config/config.local.php';
    if (is_file($file)) {
        $local = require $file;
        if (is_array($local)) {
            $cfg = array_replace_recursive($cfg, $local);
        }
    }

    $env = [
        'LLPC_MODERATOR_PASSWORD_HASH' => 'moderator_password_hash',
        'LLPC_STORAGE_DIR'             => 'storage_dir',
        'LLPC_DEFAULT_LANG'            => 'default_lang',
        'LLPC_DATA_LICENSE'            => 'data_license',
    ];
    foreach ($env as $name => $key) {
        $value = getenv($name);
        if ($value !== false && $value !== '') {
            $cfg[$key] = $value;
        }
    }

    $envMw = [
        'LLPC_MEDIAWIKI_API_URL'   => 'api_url',
        'LLPC_MEDIAWIKI_PAGE_BASE' => 'page_base',
        'LLPC_MEDIAWIKI_CATEGORY'  => 'category',
    ];
    foreach ($envMw as $name => $key) {
        $value = getenv($name);
        if ($value !== false && $value !== '') {
            $cfg['mediawiki'][$key] = $value;
        }
    }

    $debug = getenv('LLPC_DEBUG');
    if ($debug === '1' || $debug === 'true') {
        $cfg['debug'] = true;
    }

    return $cfg;
}

/**
 * Read a configuration value using dot notation, e.g. llpc_config('mediawiki.api_url').
 */
function llpc_config($path, $default = null)
{
    $node = llpc_config_all();
    foreach (explode('.', $path) as $part) {
        if (!is_array($node) || !array_key_exists($part, $node)) {
            return $default;
        }
        $node = $node[$part];
    }
    return $node;
}

/**
 * Passwords that must never be accepted for the moderator account.
 */
function llpc_weak_passwords()
{
    return ['123456', '12345678', 'password', 'admin', 'moderator', 'changeme', 'qwerty', 'letmein'];
}
