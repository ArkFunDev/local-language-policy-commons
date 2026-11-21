<?php
/**
 * Application bootstrap. Every public/*.php entry point requires this file first.
 */
define('LLPC_VERSION', '0.2.0');
define('LLPC_ROOT', dirname(__DIR__));

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/storage.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/proposals.php';
require_once __DIR__ . '/similarity.php';
require_once __DIR__ . '/mediawiki.php';
require_once __DIR__ . '/layout.php';

llpc_boot();
