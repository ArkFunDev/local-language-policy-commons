
<?php
session_start();

$proposalFile = __DIR__ . '/../sample-data/proposals.json';
$reviewFile = __DIR__ . '/reviews.json';

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// CSRF protection
if (empty($_SESSION['review_token'])) {
    $_SESSION['review_token'] = bin2hex(random_bytes(32));
}

// Load proposals
if (!file_exists($proposalFile)) {
    die('Proposal data file not found.');
}

$data = json_decode(file_get_contents($proposalFile), true);
$proposals = $data['proposals'] ?? [];

if (!is_array($proposals)) {
    die('Invalid proposal data.');
}

// Create local review file if it does not exist
if (!file_exists($reviewFile)) {
    file_put_contents($reviewFile, '{}', LOCK_EX);
}

$reviews = json_decode(file_get_contents($reviewFile), true);
if (!is_array($reviews)) {
    $reviews = [];
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $proposalId = $_POST['proposal_id'] ?? '';
    $decision = $_POST['decision'] ?? '';

    if (!hash_equals($_SESSION['review_token'], $token)) {
        $message = 'Invalid security token. Please reload the page.';
    } else {
        $knownIds = array_column($proposals, 'id');

        if (
            !in_array($proposalId, $knownIds, true) ||
            !in_array($decision, ['accepted', 'needs_revision'], true)
        ) {
            $message = 'Invalid review submission.';
        } else {
            $reviews[$proposalId] = [
                'status' => $decision,
                'reviewed_at' => date('c')
            ];

            $saved = file_put_contents(
                $reviewFile,
                json_encode(
                    $reviews,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                ),
                LOCK_EX
            );

            if ($saved !== false) {
                header('Location: review.php?saved=1');
                exit;
            } else {
                $message = 'Could not save review. Check folder permissions.';
            }
        }
    }
}

if (isset($_GET['saved'])) {
    $message = 'Review decision saved successfully.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community Review - Local Language Policy Commons</title>
    <style>
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
        .proposal {
            background: white;
            padding: 20px;
            margin: 16px 0;
            border-radius: 10px;
            border: 1px solid #ddd;
        }
        .muted {
            color: #667085;
            font-size: 14px;
        }
        .original {
            font-size: 18px;
            margin: 14px 0;
        }
        .gloss {
            background: #f1f5f9;
            padding: 12px;
            border-radius: 6px;
        }
        .status {
            display: inline-block;
            padding: 5px 9px;
            border-radius: 5px;
            background: #e8eef5;
            font-size: 13px;
        }
        button {
            padding: 10px 14px;
            margin: 12px 8px 0 0;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .accept {
            background: #d1fae5;
            color: #065f46;
        }
        .revise {
            background: #ffedd5;
            color: #9a3412;
        }
        .message {
            background: #e0f2fe;
            padding: 12px;
            border-radius: 6px;
        }
        a {
            color: #175cd3;
        }
    </style>
</head>
<body>
<div class="container">

    <h1>Community Review</h1>
    <p class="muted">
        Review the original proposal and its Indonesian reference gloss.
        Decisions are experimental and stored locally.
    </p>

    <p><a href="index.php">← Back to Proposal Explorer</a></p>

    <?php if ($message): ?>
        <p class="message"><?= e($message) ?></p>
    <?php endif; ?>

    <?php foreach ($proposals as $proposal): ?>
        <?php
            $id = $proposal['id'] ?? '';
            $reviewStatus = $reviews[$id]['status'] ?? 'unreviewed';
        ?>

        <section class="proposal">
            <h2><?= e($id) ?></h2>

            <p class="muted">
                Language: <?= e($proposal['language_name'] ?? 'Unknown') ?>
            </p>

            <div class="original">
                <?= e($proposal['original_text'] ?? '') ?>
            </div>

            <div class="gloss">
                <strong>Indonesian reference gloss</strong>
                <p><?= e($proposal['indonesian_gloss'] ?? '') ?></p>
            </div>

            <p>
                Current review status:
                <span class="status"><?= e($reviewStatus) ?></span>
            </p>

            <form method="POST">
                <input
                    type="hidden"
                    name="token"
                    value="<?= e($_SESSION['review_token']) ?>"
                >
                <input
                    type="hidden"
                    name="proposal_id"
                    value="<?= e($id) ?>"
                >

                <button class="accept" name="decision" value="accepted">
                    Accept
                </button>

                <button class="revise" name="decision" value="needs_revision">
                    Needs Revision
                </button>
            </form>
        </section>
    <?php endforeach; ?>

</div>
</body>
</html>
