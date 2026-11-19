
<?php
$proposalFile = __DIR__ . '/../sample-data/proposals.json';
$reviewFile = __DIR__ . '/reviews.json';

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Load proposal data
if (!file_exists($proposalFile)) {
    die('Proposal data file not found.');
}

$data = json_decode(file_get_contents($proposalFile), true);
$proposals = $data['proposals'] ?? [];

if (!is_array($proposals)) {
    die('Invalid proposal data.');
}

// Load review data
$reviews = [];

if (file_exists($reviewFile)) {
    $loadedReviews = json_decode(file_get_contents($reviewFile), true);
    if (is_array($loadedReviews)) {
        $reviews = $loadedReviews;
    }
}

// Calculate statistics
$totalProposals = count($proposals);
$languages = [];
$topics = [];

$accepted = 0;
$needsRevision = 0;
$unreviewed = 0;

foreach ($proposals as $proposal) {
    $language = $proposal['language_name'] ?? 'Unknown';
    $topic = $proposal['suggested_topic'] ?? 'Uncategorized';
    $id = $proposal['id'] ?? '';

    $languages[$language] = ($languages[$language] ?? 0) + 1;
    $topics[$topic] = ($topics[$topic] ?? 0) + 1;

    $status = $reviews[$id]['status'] ?? 'unreviewed';

    if ($status === 'accepted') {
        $accepted++;
    } elseif ($status === 'needs_revision') {
        $needsRevision++;
    } else {
        $unreviewed++;
    }
}

$totalLanguages = count($languages);
$totalTopics = count($topics);

$reviewed = $accepted + $needsRevision;
$reviewPercentage = $totalProposals > 0
    ? round(($reviewed / $totalProposals) * 100)
    : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Dashboard - Local Language Policy Commons</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            color: #263238;
            margin: 0;
            padding: 24px;
        }

        .container {
            max-width: 1050px;
            margin: auto;
        }

        .header {
            margin-bottom: 24px;
        }

        .muted {
            color: #667085;
            line-height: 1.5;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 16px;
        }

        .card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            border: 1px solid #e4e7ec;
        }

        .card h3 {
            font-size: 14px;
            color: #667085;
            margin: 0 0 12px;
        }

        .number {
            font-size: 32px;
            font-weight: bold;
        }

        .section {
            background: white;
            padding: 22px;
            border-radius: 10px;
            margin-top: 20px;
            border: 1px solid #e4e7ec;
        }

        .bar-row {
            margin: 18px 0;
        }

        .bar-label {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 7px;
            font-size: 14px;
        }

        .bar-bg {
            height: 12px;
            background: #e9edf2;
            border-radius: 20px;
            overflow: hidden;
        }

        .bar {
            height: 100%;
            background: #3478f6;
            border-radius: 20px;
        }

        .progress-bg {
            height: 14px;
            background: #e9edf2;
            border-radius: 20px;
            overflow: hidden;
            margin-top: 12px;
        }

        .progress {
            height: 100%;
            background: #16a36a;
        }

        .links {
            margin-top: 24px;
        }

        a {
            display: inline-block;
            margin-right: 16px;
            margin-bottom: 10px;
            color: #175cd3;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .note {
            font-size: 13px;
            color: #667085;
            margin-top: 20px;
        }
    </style>
</head>

<body>
<div class="container">

    <div class="header">
        <h1>Project Dashboard</h1>
        <p class="muted">
            Local Language Policy Commons
            <br>
            Overview of multilingual proposals and community review activity.
        </p>
    </div>

    <div class="grid">

        <div class="card">
            <h3>Total Proposals</h3>
            <div class="number"><?= $totalProposals ?></div>
        </div>

        <div class="card">
            <h3>Languages</h3>
            <div class="number"><?= $totalLanguages ?></div>
        </div>

        <div class="card">
            <h3>Accepted</h3>
            <div class="number"><?= $accepted ?></div>
        </div>

        <div class="card">
            <h3>Needs Revision</h3>
            <div class="number"><?= $needsRevision ?></div>
        </div>

        <div class="card">
            <h3>Unreviewed</h3>
            <div class="number"><?= $unreviewed ?></div>
        </div>

    </div>

    <div class="section">
        <h2>Community Review Progress</h2>

        <strong><?= $reviewPercentage ?>% reviewed</strong>

        <div class="progress-bg">
            <div class="progress"
                 style="width: <?= $reviewPercentage ?>%">
            </div>
        </div>

        <p class="muted">
            <?= $reviewed ?> of <?= $totalProposals ?> proposals
            have received a review decision.
        </p>
    </div>

    <div class="section">
        <h2>Proposals by Language</h2>

        <?php foreach ($languages as $language => $count): ?>
            <?php
                $percentage = $totalProposals > 0
                    ? ($count / $totalProposals) * 100
                    : 0;
            ?>

            <div class="bar-row">
                <div class="bar-label">
                    <strong><?= e($language) ?></strong>
                    <span><?= $count ?> proposals</span>
                </div>

                <div class="bar-bg">
                    <div class="bar"
                         style="width: <?= $percentage ?>%">
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="section">
        <h2>Proposals by Topic</h2>

        <?php foreach ($topics as $topic => $count): ?>
            <div class="bar-row">
                <div class="bar-label">
                    <strong><?= e($topic) ?></strong>
                    <span><?= $count ?> proposals</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="links">
        <a href="index.php">Proposal Explorer</a>
        <a href="review.php">Community Review</a>
    </div>

    <p class="note">
        Demonstration dashboard using synthetic sample data.
        Statistics describe this local prototype only, not real
        community participation.
    </p>

</div>
</body>
</html>
