
<?php
session_start();

$submissionFile = __DIR__ . '/submissions.json';

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function textLength($value) {
    return function_exists('mb_strlen')
        ? mb_strlen($value, 'UTF-8')
        : strlen($value);
}

function loadSubmissions($file) {
    if (!file_exists($file)) {
        return [];
    }

    $content = file_get_contents($file);
    $data = json_decode($content, true);

    return is_array($data) ? $data : [];
}

function saveSubmission($file, $submission) {
    $handle = fopen($file, 'c+');

    if (!$handle) {
        return false;
    }

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        return false;
    }

    $content = stream_get_contents($handle);
    $submissions = json_decode($content, true);

    if (!is_array($submissions)) {
        $submissions = [];
    }

    $submissions[] = $submission;

    $json = json_encode(
        $submissions,
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    rewind($handle);
    ftruncate($handle, 0);

    $saved = $json !== false && fwrite($handle, $json) !== false;

    fflush($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    return $saved;
}

if (empty($_SESSION['submission_token'])) {
    $_SESSION['submission_token'] = bin2hex(random_bytes(32));
}

$error = '';
$success = isset($_GET['success']);
$language = '';
$originalText = '';
$indonesianGloss = '';
$topic = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';

    $language = trim($_POST['language'] ?? '');
    $originalText = trim($_POST['original_text'] ?? '');
    $indonesianGloss = trim($_POST['indonesian_gloss'] ?? '');
    $topic = trim($_POST['topic'] ?? '');

    if (!hash_equals($_SESSION['submission_token'], $token)) {
        $error = 'Invalid security token. Please reload the page.';
    } elseif (
        $language === '' ||
        $originalText === '' ||
        $indonesianGloss === '' ||
        $topic === ''
    ) {
        $error = 'Please complete all required fields.';
    } elseif (
        textLength($language) > 80 ||
        textLength($topic) > 100 ||
        textLength($originalText) > 2000 ||
        textLength($indonesianGloss) > 2000
    ) {
        $error = 'One or more fields exceed the allowed character limit.';
    } else {
        $submission = [
            'id' => 'SUB-' . strtoupper(bin2hex(random_bytes(4))),
            'language' => $language,
            'original_text' => $originalText,
            'indonesian_gloss' => $indonesianGloss,
            'suggested_topic' => $topic,
            'source' => 'community_submission',
            'status' => 'unreviewed',
            'is_synthetic' => false,
            'submitted_at' => date('c')
        ];

        if (saveSubmission($submissionFile, $submission)) {
            header('Location: submit.php?success=1');
            exit;
        } else {
            $error = 'Unable to save the proposal. Check folder permissions.';
        }
    }
}

$submissions = loadSubmissions($submissionFile);
$submissions = array_reverse($submissions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit a Proposal - Local Language Policy Commons</title>

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
            max-width: 850px;
            margin: auto;
        }

        .panel {
            background: white;
            padding: 22px;
            border: 1px solid #e4e7ec;
            border-radius: 10px;
            margin: 18px 0;
        }

        label {
            display: block;
            font-weight: bold;
            margin: 16px 0 7px;
        }

        input, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-family: inherit;
            font-size: 15px;
        }

        textarea {
            min-height: 110px;
            resize: vertical;
        }

        button {
            margin-top: 18px;
            padding: 12px 20px;
            border: 0;
            border-radius: 6px;
            background: #175cd3;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .notice, .success, .error {
            padding: 13px;
            border-radius: 6px;
            margin: 15px 0;
            line-height: 1.5;
        }

        .notice {
            background: #fff7e6;
        }

        .success {
            background: #d1fae5;
            color: #065f46;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .muted {
            color: #667085;
            font-size: 13px;
            line-height: 1.5;
        }

        .submission {
            padding: 16px 0;
            border-bottom: 1px solid #eee;
        }

        .status {
            display: inline-block;
            padding: 5px 9px;
            background: #e8eef5;
            border-radius: 5px;
            font-size: 12px;
        }

        a {
            color: #175cd3;
        }
    </style>
</head>

<body>
<div class="container">

    <h1>Submit a Policy Proposal</h1>

    <p class="muted">
        Share a policy idea in your local language.
        Preserve the original expression and provide an Indonesian
        explanation to help others understand its meaning.
    </p>

    <p>
        <a href="index.php">Proposal Explorer</a> |
        <a href="dashboard.php">Dashboard</a> |
        <a href="review.php">Community Review</a>
    </p>

    <div class="notice">
        This is an experimental local prototype.
        Do not submit personal, confidential, or sensitive information.
        Submissions are stored locally and are not yet publicly shared.
    </div>

    <?php if ($success): ?>
        <div class="success">
            Proposal submitted successfully. Its current status is unreviewed.
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= e($error) ?></div>
    <?php endif; ?>

    <div class="panel">
        <h2>Proposal Details</h2>

        <form method="POST">
            <input
                type="hidden"
                name="token"
                value="<?= e($_SESSION['submission_token']) ?>"
            >

            <label for="language">Local language</label>
            <input
                type="text"
                id="language"
                name="language"
                maxlength="80"
                value="<?= e($language) ?>"
                placeholder="Example: Javanese, Sundanese, Madurese"
                required
            >

            <label for="original_text">Original proposal</label>
            <textarea
                id="original_text"
                name="original_text"
                maxlength="2000"
                placeholder="Write your proposal in its original language..."
                required
            ><?= e($originalText) ?></textarea>

            <label for="indonesian_gloss">
                Indonesian explanation or translation
            </label>
            <textarea
                id="indonesian_gloss"
                name="indonesian_gloss"
                maxlength="2000"
                placeholder="Explain the meaning in Indonesian..."
                required
            ><?= e($indonesianGloss) ?></textarea>

            <label for="topic">Suggested policy topic</label>
            <input
                type="text"
                id="topic"
                name="topic"
                maxlength="100"
                value="<?= e($topic) ?>"
                placeholder="Example: Healthcare, Education, Environment"
                required
            >

            <button type="submit">Submit Proposal</button>
        </form>
    </div>

    <div class="panel">
        <h2>Recent Local Submissions</h2>

        <?php if (empty($submissions)): ?>
            <p class="muted">No proposals submitted yet.</p>
        <?php else: ?>
            <?php foreach (array_slice($submissions, 0, 10) as $item): ?>
                <div class="submission">
                    <h3><?= e($item['id'] ?? '') ?></h3>

                    <p class="muted">
                        Language: <?= e($item['language'] ?? '') ?>
                        · Topic: <?= e($item['suggested_topic'] ?? '') ?>
                    </p>

                    <p><?= e($item['original_text'] ?? '') ?></p>

                    <p class="muted">
                        Indonesian explanation:
                        <?= e($item['indonesian_gloss'] ?? '') ?>
                    </p>

                    <span class="status">
                        <?= e($item['status'] ?? 'unreviewed') ?>
                    </span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
