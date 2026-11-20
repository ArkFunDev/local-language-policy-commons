
<?php
// Temporary public MediaWiki API for testing.
// Replace this endpoint when an approved project wiki is available.
define('MEDIAWIKI_API_URL', 'https://id.wikipedia.org/w/api.php');

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function fetchMediaWiki($params) {
    $url = MEDIAWIKI_API_URL . '?' . http_build_query($params);

    if (!function_exists('curl_init')) {
        return ['error' => 'PHP cURL is not enabled. Enable the cURL extension in XAMPP.'];
    }

    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_USERAGENT => 'LocalLanguagePolicyCommons/0.1 (prototype)',
        CURLOPT_HTTPHEADER => ['Accept: application/json']
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if ($response === false) {
        return ['error' => 'Connection failed: ' . $error];
    }

    if ($httpCode !== 200) {
        return ['error' => 'MediaWiki returned HTTP ' . $httpCode];
    }

    $result = json_decode($response, true);

    if (!is_array($result)) {
        return ['error' => 'Invalid JSON response from MediaWiki.'];
    }

    if (isset($result['error'])) {
        return ['error' => $result['error']['info'] ?? 'MediaWiki API error.'];
    }

    return $result;
}

$query = trim($_GET['q'] ?? '');
$results = [];
$error = '';

if ($query !== '') {
    if (function_exists('mb_strlen') && mb_strlen($query, 'UTF-8') > 100) {
        $error = 'Search query is too long. Maximum 100 characters.';
    } elseif (strlen($query) > 300) {
        $error = 'Search query is too long.';
    } else {
        $response = fetchMediaWiki([
            'action' => 'query',
            'generator' => 'search',
            'gsrsearch' => $query,
            'gsrnamespace' => 0,
            'gsrlimit' => 10,
            'prop' => 'extracts',
            'exintro' => 1,
            'explaintext' => 1,
            'format' => 'json',
            'formatversion' => 2
        ]);

        if (isset($response['error'])) {
            $error = $response['error'];
        } else {
            $results = $response['query']['pages'] ?? [];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediaWiki Explorer - Local Language Policy Commons</title>

    <style>
        * { box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #263238;
            margin: 0;
            padding: 24px;
        }

        .container {
            max-width: 900px;
            margin: auto;
        }

        .panel {
            background: white;
            padding: 22px;
            border: 1px solid #e4e7ec;
            border-radius: 10px;
            margin-top: 18px;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin: 10px 0;
            font-size: 16px;
        }

        button {
            padding: 11px 18px;
            border: 0;
            background: #175cd3;
            color: white;
            border-radius: 6px;
            cursor: pointer;
        }

        .result {
            padding: 16px 0;
            border-bottom: 1px solid #eee;
        }

        .muted {
            color: #667085;
            font-size: 13px;
            line-height: 1.5;
        }

        .notice {
            background: #fff7e6;
            padding: 12px;
            border-radius: 6px;
            margin-top: 15px;
            font-size: 14px;
        }

        a { color: #175cd3; }
    </style>
</head>

<body>
<div class="container">

    <h1>MediaWiki Explorer</h1>

    <p class="muted">
        Search public MediaWiki articles through the read-only API.
    </p>

    <p>
        <a href="index.php">Proposal Explorer</a> |
        <a href="dashboard.php">Dashboard</a> |
        <a href="review.php">Community Review</a>
    </p>

    <div class="notice">
        Demonstration source: Indonesian Wikipedia.
        Results are external reference articles, not verified community proposals.
    </div>

    <div class="panel">
        <h2>Search Articles</h2>

        <form method="GET">
            <label for="q">Search topic or keyword</label>

            <input
                type="text"
                id="q"
                name="q"
                maxlength="100"
                value="<?= e($query) ?>"
                placeholder="Example: bahasa daerah, pendidikan, kesehatan"
                required
            >

            <button type="submit">Search MediaWiki</button>
        </form>
    </div>

    <?php if ($error): ?>
        <div class="panel">
            <strong>API Error</strong>
            <p><?= e($error) ?></p>
        </div>
    <?php endif; ?>

    <?php if ($query !== '' && !$error): ?>
        <div class="panel">
            <h2>Search Results</h2>

            <?php if (empty($results)): ?>
                <p>No articles found for this query.</p>
            <?php endif; ?>

            <?php foreach ($results as $page): ?>
                <div class="result">
                    <h3><?= e($page['title'] ?? 'Untitled') ?></h3>

                    <p>
                        <?= e($page['extract'] ?? 'No article introduction available.') ?>
                    </p>

                    <?php if (!empty($page['pageid'])): ?>
                        <a
                            href="https://id.wikipedia.org/?curid=<?= (int) $page['pageid'] ?>"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Open original article
                        </a>
                    <?php endif; ?>

                    <p class="muted">
                        Source: Indonesian Wikipedia · Page ID:
                        <?= (int) ($page['pageid'] ?? 0) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
