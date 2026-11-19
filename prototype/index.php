
<?php
// Local Language Policy Commons
// Initial PHP Prototype v0.1.0

$dataFile = __DIR__ . '/../sample-data/proposals.json';

if (!file_exists($dataFile)) {
    die('Dataset not found. Please check sample-data/proposals.json');
}

$data = json_decode(file_get_contents($dataFile), true);

if (!$data || !isset($data['proposals'])) {
    die('Invalid proposal dataset.');
}

$proposals = $data['proposals'];
require_once __DIR__ . '/similarity.php';

$search = trim($_GET['search'] ?? '');
$language = $_GET['language'] ?? 'all';
$topic = $_GET['topic'] ?? 'all';

$filtered = array_filter($proposals, function ($item) use ($search, $language, $topic) {
    $matchesSearch = $search === '' ||
        stripos($item['original_text'], $search) !== false ||
        stripos($item['indonesian_gloss'], $search) !== false;

    $matchesLanguage = $language === 'all' ||
        $item['language'] === $language;

    $matchesTopic = $topic === 'all' ||
        $item['suggested_topic'] === $topic;

    return $matchesSearch && $matchesLanguage && $matchesTopic;
});

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$languages = [
    'all' => 'All languages',
    'id' => 'Indonesian',
    'jv' => 'Javanese',
    'su' => 'Sundanese'
];

$topics = [
    'all' => 'All topics',
    'healthcare_access' => 'Healthcare Access',
    'digital_education' => 'Digital Education'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Local Language Policy Commons</title>

<style>
* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f8;
    color: #243044;
}

header {
    background: #183b56;
    color: white;
    padding: 28px 20px;
}

.container {
    max-width: 1050px;
    margin: auto;
    padding: 24px 18px;
}

h1 { margin: 0 0 10px; }

header p {
    margin: 0;
    color: #dce8f0;
    line-height: 1.6;
}

.filters {
    background: white;
    padding: 18px;
    border-radius: 10px;
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 10px;
    margin-bottom: 22px;
}

input, select, button {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccd4dc;
    border-radius: 6px;
    font-size: 14px;
}

button {
    background: #1875a5;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: bold;
}

button:hover { background: #125b80; }

.summary {
    margin-bottom: 16px;
    color: #596579;
    font-size: 14px;
}

.proposal {
    background: white;
    border: 1px solid #e1e6eb;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 14px;
}

.proposal h3 {
    margin: 0 0 12px;
}

.original {
    font-size: 17px;
    line-height: 1.6;
    margin-bottom: 12px;
}

.gloss {
    background: #f1f6fa;
    padding: 12px;
    border-radius: 6px;
    line-height: 1.5;
    color: #43546a;
}

.tags {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 14px;
}

.tag {
    background: #e8f1f7;
    color: #245a7a;
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 12px;
}

.empty {
    background: white;
    padding: 25px;
    border-radius: 10px;
    text-align: center;
}

footer {
    text-align: center;
    padding: 25px;
    color: #748094;
    font-size: 12px;
}

@media(max-width: 700px) {
    .filters { grid-template-columns: 1fr; }
}
</style>
</head>

<p>
    <a href="dashboard.php">Project Dashboard</a> |
    <a href="review.php">Community Review</a>
</p>
    
<body>

<header>
    <div class="container">
        <h1>Local Language Policy Commons</h1>
        <p>
            Discover shared policy ideas across local languages
            while preserving original expressions and community voices.
        </p>
    </div>
</header>

<main class="container">

    <form method="GET" class="filters">

        <input
            type="search"
            name="search"
            placeholder="Search proposals..."
            value="<?= e($search) ?>"
        >

        <select name="language">
            <?php foreach ($languages as $key => $label): ?>
                <option value="<?= e($key) ?>"
                    <?= $language === $key ? 'selected' : '' ?>>
                    <?= e($label) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select name="topic">
            <?php foreach ($topics as $key => $label): ?>
                <option value="<?= e($key) ?>"
                    <?= $topic === $key ? 'selected' : '' ?>>
                    <?= e($label) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Explore</button>

    </form>

    <div class="summary">
        Showing <?= count($filtered) ?> of <?= count($proposals) ?> proposals
    </div>

    <?php if (count($filtered) > 0): ?>

        <?php foreach ($filtered as $item): ?>

            <article class="proposal">

                <h3>Proposal <?= e($item['id']) ?></h3>

                <div class="original">
                    <?= e($item['original_text']) ?>
                </div>

                <div class="gloss">
                    <strong>Indonesian reference:</strong><br>
                    <?= e($item['indonesian_gloss']) ?>
                </div>
                
<?php
$similarItems = getSimilarProposals($item, $proposals, 2);
?>

<?php if (!empty($similarItems)): ?>
    <div class="gloss" style="margin-top:12px;">
        <strong>Potentially Related Proposals</strong>

        <?php foreach ($similarItems as $similar): ?>
            <div style="margin-top:10px;">
                <strong><?= e($similar['id']) ?></strong>
                — <?= e($similar['original_text']) ?>

                <span class="tag">
                    <?= e($similar['similarity_score']) ?>% lexical overlap
                </span>
            </div>
        <?php endforeach; ?>

        <small>
            Experimental suggestions based on shared Indonesian
            reference words. Human review is required.
        </small>
    </div>
<?php endif; ?>
                
                <div class="tags">
                    <span class="tag">
                        <?= e($item['language_name']) ?>
                    </span>

                    <span class="tag">
                        <?= e(str_replace('_', ' ', $item['suggested_topic'])) ?>
                    </span>

                    <span class="tag">
                        <?= e($item['review_status']) ?>
                    </span>
                </div>

            </article>

        <?php endforeach; ?>

    <?php else: ?>

        <div class="empty">
            No proposals found. Try another search or filter.
        </div>

    <?php endif; ?>

</main>

<footer>
    Local Language Policy Commons · Prototype v0.1.0
    <br>
    Synthetic demonstration data - not actual community submissions.
</footer>

</body>
</html>
