<?php
/**
 * Dependency-free test runner.   Usage:  php tests/run.php
 * Uses a temporary storage directory; never touches real data.
 */
$tmp = sys_get_temp_dir() . '/llpc-test-' . bin2hex(random_bytes(4));
mkdir($tmp, 0777, true);
putenv('LLPC_STORAGE_DIR=' . $tmp);

require __DIR__ . '/../src/bootstrap.php';

$passed = 0;
$failed = 0;

function check($name, $condition)
{
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  ok   $name\n";
    } else {
        $failed++;
        echo "  FAIL $name\n";
    }
}

echo "i18n\n";
$en = llpc_strings('en');
$id = llpc_strings('id');
check('en and id define the same keys', array_keys($en) === array_keys($id) || (!array_diff_key($en, $id) && !array_diff_key($id, $en)));
$placeholdersOk = true;
foreach ($en as $k => $v) {
    preg_match_all('/\{(\w+)\}/', $v, $a);
    preg_match_all('/\{(\w+)\}/', isset($id[$k]) ? $id[$k] : '', $b);
    sort($a[1]);
    sort($b[1]);
    if ($a[1] !== $b[1]) {
        $placeholdersOk = false;
    }
}
check('placeholders match across languages', $placeholdersOk);
$topicsOk = true;
foreach (llpc_topics() as $slug) {
    if (!isset($en['topic.' . $slug]) || !isset($id['topic.' . $slug])) {
        $topicsOk = false;
    }
}
check('every topic has a label in both languages', $topicsOk);

// Every literal translation key used in the code must exist.
$used = [];
foreach (array_merge(glob(LLPC_ROOT . '/public/*.php'), glob(LLPC_ROOT . '/src/*.php')) as $file) {
    if (preg_match_all("/\\b(?:th|t)\\(\\s*'([a-z_.]+)'/", file_get_contents($file), $m)) {
        $used = array_merge($used, $m[1]);
    }
}
$missing = [];
foreach (array_unique($used) as $k) {
    if (!isset($en[$k])) {
        $missing[] = $k;
    }
}
check('all translation keys used in code exist' . ($missing ? ' (missing: ' . implode(', ', $missing) . ')' : ''), !$missing);

echo "escaping and input\n";
check('h() escapes HTML', h('<script>"x"</script>') === '&lt;script&gt;&quot;x&quot;&lt;/script&gt;');
check('clean_text strips control characters', llpc_clean_text("a\x00b\x07c") === 'abc');
check('clean_text rejects invalid UTF-8', llpc_clean_text("\xff\xfe") === '');
check('csv_safe neutralises formulas', llpc_csv_safe('=1+1') === "'=1+1" && llpc_csv_safe('@x') === "'@x" && llpc_csv_safe('plain') === 'plain');

echo "similarity\n";
check('stopwords removed from tokens', !in_array('yang', llpc_tokens('layanan yang baik'), true));
check('tokens are unique and lowercase', llpc_tokens('Desa DESA desa') === ['desa']);
check('pair key is order independent', llpc_pair_key('P1', 'P2') === llpc_pair_key('P2', 'P1'));
$all = llpc_all_proposals(false);
$p1  = llpc_find_proposal('P001', $all);
check('sample P001 exists', $p1 !== null);
$sim = llpc_similar_proposals($p1, $all, 2);
$topicOk = count($sim) === 2;
foreach ($sim as $m) {
    if ($m['suggested_topic'] !== 'healthcare_access') {
        $topicOk = false;
    }
}
check('top related proposals for P001 share its topic', $topicOk);
check('suggestions expose shared terms', !empty($sim[0]['_shared']));

echo "validation\n";
$good = [
    'language' => 'jv', 'language_other' => '', 'original_text' => 'Tes usulan', 'indonesian_gloss' => 'Tes usulan',
    'topic' => 'education', 'source_type' => 'community', 'source_url' => '', 'attribution' => '', 'consent' => '1',
];
check('valid submission passes', llpc_validate_submission($good)['error'] === null);
check('consent is required', llpc_validate_submission(array_merge($good, ['consent' => '']))['error'] === 'err.consent');
check('unknown language rejected', llpc_validate_submission(array_merge($good, ['language' => 'xx']))['error'] === 'err.language');
check('unknown topic rejected', llpc_validate_submission(array_merge($good, ['topic' => 'nope']))['error'] === 'err.topic');
check('javascript: URL rejected', llpc_validate_submission(array_merge($good, ['source_url' => 'javascript:alert(1)']))['error'] === 'err.url');
check('https URL accepted', llpc_validate_submission(array_merge($good, ['source_url' => 'https://example.org/a']))['error'] === null);
check('over-long text rejected', llpc_validate_submission(array_merge($good, ['original_text' => str_repeat('a', 2001)]))['error'] === 'err.too_long');
check('"other" language needs a name', llpc_validate_submission(array_merge($good, ['language' => 'other']))['error'] === 'err.required');

echo "storage\n";
check('add_submission stores a record', llpc_add_submission(llpc_validate_submission($good)['data']) === 'ok');
$subs = llpc_load_submissions();
check('record has pending verification and unreviewed status', count($subs) === 1 && $subs[0]['status'] === 'unreviewed' && $subs[0]['verification_status'] === 'pending');
check('unreviewed submissions are hidden from public view', count(llpc_all_proposals(false)) === count(llpc_load_samples()));
check('unreviewed submissions are visible to moderators', count(llpc_all_proposals(true)) === count(llpc_load_samples()) + 1);
$sid = $subs[0]['id'];
check('update_submission works', llpc_update_submission($sid, ['status' => 'accepted']) === 'ok');
check('update_submission reports not_found', llpc_update_submission('SUB-NOPE', ['status' => 'accepted']) === 'not_found');
check('accepted submissions become public', count(llpc_all_proposals(false)) === count(llpc_load_samples()) + 1);
check('export includes accepted consented records only', count(llpc_export_records(false)) === 1);

$corrupt = $tmp . '/corrupt.json';
file_put_contents($corrupt, '{not json');
check('corrupt JSON is never overwritten', llpc_update_json($corrupt, function ($d) { return ['x' => 1]; }, []) === false
    && file_get_contents($corrupt) === '{not json');

check('add_feedback stores pending feedback', llpc_add_feedback('P001', 'comment', 'hello') === 'ok');
check('pending feedback is not public', llpc_approved_feedback_for('P001') === []);
$fb = llpc_load_feedback();
llpc_update_feedback($fb[0]['id'], ['status' => 'approved']);
check('approved feedback is public', count(llpc_approved_feedback_for('P001')) === 1);

echo "rate limiting\n";
$allowed = 0;
for ($i = 0; $i < 5; $i++) {
    if (llpc_rate_allow('test', 3, 60)) {
        $allowed++;
    }
}
check('rate limit allows exactly the limit', $allowed === 3);
check('rate-limit file stores no raw IP', strpos((string) file_get_contents($tmp . '/ratelimit.json'), 'cli') === false);

echo "similarity review\n";
check('pair review can be saved', llpc_set_pair_review('P001', 'P002', 'confirmed') === true);
$sim = llpc_similar_proposals($p1, $all, 8);
$status = '';
foreach ($sim as $m) {
    if ($m['_id'] === 'P002') {
        $status = $m['_pair_status'];
    }
}
check('confirmed pair is reflected in suggestions', $status === 'confirmed');

// Cleanup
foreach (glob($tmp . '/*') as $f) {
    @unlink($f);
}
@rmdir($tmp);

echo "\n$passed passed, $failed failed\n";
exit($failed ? 1 : 0);
