<?php
/**
 * Local Language Policy Commons - example configuration.
 *
 * Easiest setup:   php bin/set-moderator-password.php
 * Manual setup:    copy this file to config/config.local.php and edit it.
 *
 * config/config.local.php is ignored by Git and must NEVER be committed.
 * Every value below is optional; the defaults live in src/config.php.
 */
return [
    // Password hash for the moderator account. Generate with bin/set-moderator-password.php
    'moderator_password_hash' => '',

    // 'en' or 'id'
    'default_lang' => 'en',

    // Where JSON data is stored. Keep this OUTSIDE the web root in production.
    'storage_dir' => dirname(__DIR__) . '/storage',

    // Set to false to disable the public submission form.
    'public_submissions' => true,

    // Licence shown to contributors in the consent statement and in exports.
    // Confirm the final choice with BASAibu before launching publicly.
    'data_license' => 'CC BY 4.0',

    // Safety cap on the number of stored submissions / feedback items.
    'max_records' => 5000,

    // Only set to true when running behind a trusted reverse proxy.
    'trust_proxy_headers' => false,

    // Shown in the footer and About page.
    'repository_url' => 'https://github.com/ArkFunDev/local-language-policy-commons',

    // MediaWiki source. Defaults to Indonesian Wikipedia as a public demo.
    // Replace with the BASAibu wiki API once access is agreed.
    'mediawiki' => [
        'api_url'      => 'https://id.wikipedia.org/w/api.php',   // must be HTTPS
        'page_base'    => 'https://id.wikipedia.org/wiki/',
        'source_label' => 'Indonesian Wikipedia (demo source)',
        'category'     => '',      // e.g. 'Category:Policy proposals' to list a category by default
        'cache_ttl'    => 300,     // seconds; 0 disables caching
        'contact'      => 'https://github.com/ArkFunDev/local-language-policy-commons', // sent in the User-Agent
    ],

    // Attempts allowed per time window (seconds) for each client.
    'rate_limit' => [
        'submit'   => ['limit' => 5,  'window' => 3600],
        'feedback' => ['limit' => 10, 'window' => 3600],
        'login'    => ['limit' => 5,  'window' => 900],
    ],
];
