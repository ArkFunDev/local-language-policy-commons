<?php
require __DIR__ . '/../src/bootstrap.php';

$format = llpc_get('format');

if ($format === 'json' || $format === 'csv') {
    $includeSamples = llpc_get('samples') === '1';
    $records        = llpc_export_records($includeSamples);
    $stamp          = date('Ymd');

    if ($format === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename="llpc-proposals-' . $stamp . '.json"');
        echo json_encode([
            'dataset'      => 'Local Language Policy Commons export',
            'version'      => LLPC_VERSION,
            'generated_at' => date('c'),
            'license'      => (string) llpc_config('data_license'),
            'notice'       => 'Only moderator-accepted community submissions with contributor licence consent are included. Synthetic samples are included only when requested and are marked is_synthetic=true.',
            'count'        => count($records),
            'records'      => $records,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="llpc-proposals-' . $stamp . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so spreadsheet apps detect the encoding
    $columns = ['id', 'language_code', 'language_name', 'original_text', 'indonesian_gloss', 'topic',
        'source_type', 'source_url', 'attribution', 'verification_status', 'language_verified',
        'review_status', 'is_synthetic', 'submitted_at'];
    fputcsv($out, $columns);
    foreach ($records as $row) {
        $line = [];
        foreach ($columns as $c) {
            $v      = $row[$c];
            $line[] = is_bool($v) ? ($v ? 'true' : 'false') : llpc_csv_safe($v);
        }
        fputcsv($out, $line);
    }
    fclose($out);
    exit;
}

$count = count(llpc_export_records(false));
llpc_page_start('data.title');
?>
<h1><?= th('data.title') ?></h1>
<p><?= th('data.intro') ?></p>
<p class="notice info"><?= th('data.notice', ['license' => llpc_config('data_license')]) ?></p>
<p><?= th('data.count', ['count' => $count]) ?></p>
<ul>
  <li><a href="export.php?format=json"><?= th('data.json') ?></a></li>
  <li><a href="export.php?format=csv"><?= th('data.csv') ?></a></li>
  <li><a href="export.php?format=json&amp;samples=1"><?= th('data.json_samples') ?></a></li>
</ul>
<?php llpc_page_end();
