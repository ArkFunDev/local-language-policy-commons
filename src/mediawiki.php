<?php
/**
 * Read-only MediaWiki Action API client (HTTPS only, cached, descriptive User-Agent).
 *
 * Configure the target wiki in config/config.local.php under the 'mediawiki' key.
 * The default is Indonesian Wikipedia, used purely as a public demo source until an
 * approved BASAibu wiki endpoint is configured.
 */

function llpc_mw_user_agent()
{
    return 'LocalLanguagePolicyCommons/' . LLPC_VERSION
        . ' (' . llpc_config('mediawiki.contact') . '; open-source prototype) PHP-cURL';
}

/**
 * @return array response array, or ['error' => translation key, 'vars' => placeholders]
 */
function llpc_mw_fetch($params)
{
    if (!function_exists('curl_init')) {
        return ['error' => 'mw.err.curl'];
    }
    $base = (string) llpc_config('mediawiki.api_url');
    if (stripos($base, 'https://') !== 0) {
        return ['error' => 'mw.err.https'];
    }

    $url      = $base . '?' . http_build_query($params);
    $ttl      = (int) llpc_config('mediawiki.cache_ttl', 300);
    $cacheDir = llpc_storage_dir() . '/cache';
    $cache    = $cacheDir . '/mw-' . sha1($url) . '.json';

    if ($ttl > 0 && is_file($cache) && (time() - filemtime($cache)) < $ttl) {
        $cached = json_decode((string) @file_get_contents($cache), true);
        if (is_array($cached)) {
            return $cached;
        }
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 3,
        CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS,
        CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTPS,
        CURLOPT_USERAGENT      => llpc_mw_user_agent(),
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);
    $response = curl_exec($ch);
    $code     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return ['error' => 'mw.err.connect'];
    }
    if ($code !== 200) {
        return ['error' => 'mw.err.http', 'vars' => ['code' => $code]];
    }
    $result = json_decode($response, true);
    if (!is_array($result)) {
        return ['error' => 'mw.err.json'];
    }
    if (isset($result['error'])) {
        return ['error' => 'mw.err.api'];
    }

    if ($ttl > 0) {
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0750, true);
        }
        @file_put_contents($cache, $response, LOCK_EX);
        if (mt_rand(1, 50) === 1) {
            $olds = glob($cacheDir . '/mw-*.json');
            foreach (($olds ? $olds : []) as $old) {
                if ((time() - filemtime($old)) > 86400) {
                    @unlink($old);
                }
            }
        }
    }
    return $result;
}

function llpc_mw_extract_params()
{
    return [
        'action'        => 'query',
        'prop'          => 'extracts',
        'exintro'       => 1,
        'explaintext'   => 1,
        'exlimit'       => 'max',
        'format'        => 'json',
        'formatversion' => 2,
    ];
}

function llpc_mw_search($query)
{
    return llpc_mw_fetch(array_merge(llpc_mw_extract_params(), [
        'generator'    => 'search',
        'gsrsearch'    => $query,
        'gsrnamespace' => 0,
        'gsrlimit'     => 10,
    ]));
}

/** $category is the full title as used on the wiki, e.g. "Category:Policy proposals". */
function llpc_mw_category($category)
{
    return llpc_mw_fetch(array_merge(llpc_mw_extract_params(), [
        'generator'    => 'categorymembers',
        'gcmtitle'     => $category,
        'gcmnamespace' => 0,
        'gcmlimit'     => 20,
    ]));
}

function llpc_mw_pages($response)
{
    $pages = (isset($response['query']['pages']) && is_array($response['query']['pages']))
        ? $response['query']['pages'] : [];
    usort($pages, function ($x, $y) {
        $ix = isset($x['index']) ? (int) $x['index'] : 0;
        $iy = isset($y['index']) ? (int) $y['index'] : 0;
        return $ix <=> $iy;
    });
    return $pages;
}

function llpc_mw_page_url($title)
{
    return rtrim((string) llpc_config('mediawiki.page_base'), '/') . '/' . rawurlencode(str_replace(' ', '_', (string) $title));
}
