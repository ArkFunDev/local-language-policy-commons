<?php
/**
 * Proposal data model: languages, topics, loading, normalising, validating, persisting.
 */

/** ISO 639 codes => English names. Extend as BASAibu communities require. */
function llpc_languages()
{
    return [
        'id'    => 'Indonesian',
        'jv'    => 'Javanese',
        'su'    => 'Sundanese',
        'ban'   => 'Balinese',
        'mad'   => 'Madurese',
        'min'   => 'Minangkabau',
        'bug'   => 'Buginese',
        'mak'   => 'Makassarese',
        'bjn'   => 'Banjarese',
        'ace'   => 'Acehnese',
        'bbc'   => 'Batak Toba',
        'btx'   => 'Batak Karo',
        'sas'   => 'Sasak',
        'bew'   => 'Betawi',
        'ljp'   => 'Lampung',
        'mui'   => 'Musi',
        'gor'   => 'Gorontalo',
        'abs'   => 'Ambonese Malay',
        'nia'   => 'Nias',
        'nij'   => 'Ngaju Dayak',
        'sda'   => 'Toraja',
        'other' => 'Other',
    ];
}

function llpc_topics()
{
    return [
        'healthcare_access', 'digital_education', 'education', 'environment',
        'agriculture_food', 'infrastructure', 'economy_livelihoods',
        'culture_language', 'public_services', 'youth_participation', 'other',
    ];
}

function llpc_topic_label($slug)
{
    $key   = 'topic.' . $slug;
    $label = t($key);
    return $label === $key ? (string) $slug : $label;
}

function llpc_source_types()
{
    return ['community', 'mediawiki', 'other'];
}

/* ---------- Loading ---------- */

function llpc_load_samples()
{
    $data = llpc_read_json(LLPC_ROOT . '/sample-data/proposals.json', ['proposals' => []]);
    return (isset($data['proposals']) && is_array($data['proposals'])) ? $data['proposals'] : [];
}

function llpc_load_submissions()
{
    return llpc_read_json(llpc_storage_path('submissions.json'), []);
}

function llpc_load_feedback()
{
    return llpc_read_json(llpc_storage_path('feedback.json'), []);
}

function llpc_load_pair_reviews()
{
    return llpc_read_json(llpc_storage_path('similarity_reviews.json'), []);
}

/* ---------- Normalising ---------- */

function llpc_language_name_of($p)
{
    $langs = llpc_languages();
    $name  = isset($p['language_name']) ? (string) $p['language_name'] : '';
    if ($name !== '') {
        return $name;
    }
    $raw = isset($p['language']) ? (string) $p['language'] : '';
    if (isset($langs[$raw])) {
        return $langs[$raw];
    }
    return $raw !== '' ? $raw : 'Unknown';
}

/** Returns a valid ISO code for the lang="" attribute, or '' when unknown. */
function llpc_lang_code($p)
{
    $langs = llpc_languages();
    $raw   = isset($p['language']) ? (string) $p['language'] : '';
    if ($raw !== '' && $raw !== 'other' && isset($langs[$raw])) {
        return $raw;
    }
    return '';
}

function llpc_normalize($p, $origin)
{
    $p            = is_array($p) ? $p : [];
    $p['_origin'] = $origin;
    $p['_id']     = isset($p['id']) ? (string) $p['id'] : 'unknown';
    $p['id']      = $p['_id'];
    if ($origin === 'sample') {
        $p['_status'] = 'sample';
    } else {
        $p['_status'] = isset($p['status']) ? (string) $p['status'] : 'unreviewed';
    }
    $p['_lang_name'] = llpc_language_name_of($p);
    $p['_topic']     = isset($p['suggested_topic']) ? (string) $p['suggested_topic'] : 'other';
    foreach (['original_text', 'indonesian_gloss'] as $k) {
        $p[$k] = isset($p[$k]) ? (string) $p[$k] : '';
    }
    return $p;
}

/**
 * All proposals, normalised. Community submissions are included only when accepted,
 * unless $includeUnreviewed is true (moderators).
 */
function llpc_all_proposals($includeUnreviewed = false)
{
    $out = [];
    foreach (llpc_load_samples() as $p) {
        $out[] = llpc_normalize($p, 'sample');
    }
    foreach (llpc_load_submissions() as $p) {
        $n = llpc_normalize($p, 'community');
        if ($includeUnreviewed || $n['_status'] === 'accepted') {
            $out[] = $n;
        }
    }
    return $out;
}

function llpc_visible_proposals()
{
    return llpc_all_proposals(llpc_is_moderator());
}

function llpc_find_proposal($id, $list)
{
    foreach ($list as $p) {
        if ($p['_id'] === $id) {
            return $p;
        }
    }
    return null;
}

/* ---------- Validation ---------- */

/**
 * Validate a public submission. $in must contain strings only.
 * Returns ['error' => translation key|null, 'data' => cleaned record fields].
 */
function llpc_validate_submission($in)
{
    $get = function ($k) use ($in) {
        return isset($in[$k]) && is_string($in[$k]) ? trim($in[$k]) : '';
    };

    $langs = llpc_languages();
    $code  = $get('language');
    $other = $get('language_other');

    $data = [
        'language'         => $code,
        'language_name'    => isset($langs[$code]) ? $langs[$code] : '',
        'original_text'    => $get('original_text'),
        'indonesian_gloss' => $get('indonesian_gloss'),
        'suggested_topic'  => $get('topic'),
        'source_type'      => $get('source_type') !== '' ? $get('source_type') : 'community',
        'source_url'       => $get('source_url'),
        'attribution'      => $get('attribution'),
    ];

    if ($code === 'other' && $other !== '') {
        $data['language_name'] = $other;
    }

    if (!isset($langs[$code])) {
        return ['error' => 'err.language', 'data' => $data];
    }
    if ($code === 'other' && $other === '') {
        return ['error' => 'err.required', 'data' => $data];
    }
    if ($data['original_text'] === '' || $data['indonesian_gloss'] === '' || $data['suggested_topic'] === '') {
        return ['error' => 'err.required', 'data' => $data];
    }
    if (llpc_len($data['original_text']) > 2000 || llpc_len($data['indonesian_gloss']) > 2000
        || llpc_len($other) > 80 || llpc_len($data['source_url']) > 1000
        || llpc_len($data['attribution']) > 200) {
        return ['error' => 'err.too_long', 'data' => $data];
    }
    if (!in_array($data['suggested_topic'], llpc_topics(), true)) {
        return ['error' => 'err.topic', 'data' => $data];
    }
    if (!in_array($data['source_type'], llpc_source_types(), true)) {
        return ['error' => 'err.source_type', 'data' => $data];
    }
    if ($data['source_url'] !== '') {
        $scheme = strtolower((string) parse_url($data['source_url'], PHP_URL_SCHEME));
        if (!filter_var($data['source_url'], FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true)) {
            return ['error' => 'err.url', 'data' => $data];
        }
    }
    if ($get('consent') !== '1') {
        return ['error' => 'err.consent', 'data' => $data];
    }

    return ['error' => null, 'data' => $data];
}

/* ---------- Persistence ---------- */

/** @return string 'ok' | 'full' | 'error' */
function llpc_add_submission($fields)
{
    $record = array_merge($fields, [
        'id'                  => llpc_new_id('SUB'),
        'source'              => 'community_submission',
        'verification_status' => 'pending',
        'language_verified'   => false,
        'status'              => 'unreviewed',
        'license_consent'     => true,
        'is_synthetic'        => false,
        'submitted_at'        => date('c'),
    ]);
    $max  = (int) llpc_config('max_records', 5000);
    $full = false;

    $ok = llpc_update_json(llpc_storage_path('submissions.json'), function ($items) use ($record, $max, &$full) {
        if (count($items) >= $max) {
            $full = true;
            return false;
        }
        $items[] = $record;
        return array_values($items);
    }, []);

    if ($ok) {
        return 'ok';
    }
    return $full ? 'full' : 'error';
}

/** @return string 'ok' | 'not_found' | 'error' */
function llpc_update_submission($id, $changes)
{
    $found = false;
    $ok    = llpc_update_json(llpc_storage_path('submissions.json'), function ($items) use ($id, $changes, &$found) {
        foreach ($items as $i => $item) {
            if (isset($item['id']) && $item['id'] === $id) {
                $items[$i] = array_merge($item, $changes);
                $found     = true;
                break;
            }
        }
        return $found ? array_values($items) : false;
    }, []);

    if ($ok) {
        return 'ok';
    }
    return $found ? 'error' : 'not_found';
}

/** @return string 'ok' | 'full' | 'error' */
function llpc_add_feedback($proposalId, $kind, $content)
{
    $record = [
        'id'          => llpc_new_id('FB'),
        'proposal_id' => $proposalId,
        'kind'        => $kind,
        'content'     => $content,
        'created_at'  => date('c'),
        'status'      => 'pending',
    ];
    $max  = (int) llpc_config('max_records', 5000);
    $full = false;

    $ok = llpc_update_json(llpc_storage_path('feedback.json'), function ($items) use ($record, $max, &$full) {
        if (count($items) >= $max) {
            $full = true;
            return false;
        }
        $items[] = $record;
        return array_values($items);
    }, []);

    if ($ok) {
        return 'ok';
    }
    return $full ? 'full' : 'error';
}

/** @return string 'ok' | 'not_found' | 'error' */
function llpc_update_feedback($id, $changes)
{
    $found = false;
    $ok    = llpc_update_json(llpc_storage_path('feedback.json'), function ($items) use ($id, $changes, &$found) {
        foreach ($items as $i => $item) {
            if (isset($item['id']) && $item['id'] === $id) {
                $items[$i] = array_merge($item, $changes);
                $found     = true;
                break;
            }
        }
        return $found ? array_values($items) : false;
    }, []);

    if ($ok) {
        return 'ok';
    }
    return $found ? 'error' : 'not_found';
}

function llpc_approved_feedback_for($proposalId)
{
    $out = [];
    foreach (llpc_load_feedback() as $f) {
        if (isset($f['proposal_id'], $f['status']) && $f['proposal_id'] === $proposalId && $f['status'] === 'approved') {
            $out[] = $f;
        }
    }
    return $out;
}

/* ---------- Open data export ---------- */

/** Neutralise spreadsheet formula injection in CSV cells. */
function llpc_csv_safe($value)
{
    $value = (string) $value;
    if ($value !== '' && strpos("=+-@\t\r", $value[0]) !== false) {
        return "'" . $value;
    }
    return $value;
}

/**
 * Records safe for public export: accepted community submissions whose contributor
 * gave licence consent. Samples are included only on request and are flagged synthetic.
 */
function llpc_export_records($includeSamples = false)
{
    $out = [];
    if ($includeSamples) {
        foreach (llpc_load_samples() as $p) {
            $n     = llpc_normalize($p, 'sample');
            $out[] = llpc_export_row($n);
        }
    }
    foreach (llpc_load_submissions() as $p) {
        $n = llpc_normalize($p, 'community');
        if ($n['_status'] === 'accepted' && !empty($p['license_consent'])) {
            $out[] = llpc_export_row($n);
        }
    }
    return $out;
}

function llpc_export_row($n)
{
    return [
        'id'                  => $n['_id'],
        'language_code'       => llpc_lang_code($n),
        'language_name'       => $n['_lang_name'],
        'original_text'       => $n['original_text'],
        'indonesian_gloss'    => $n['indonesian_gloss'],
        'topic'               => $n['_topic'],
        'source_type'         => isset($n['source_type']) ? (string) $n['source_type'] : '',
        'source_url'          => isset($n['source_url']) ? (string) $n['source_url'] : '',
        'attribution'         => isset($n['attribution']) ? (string) $n['attribution'] : '',
        'verification_status' => isset($n['verification_status']) ? (string) $n['verification_status'] : '',
        'language_verified'   => !empty($n['language_verified']),
        'review_status'       => $n['_status'],
        'is_synthetic'        => $n['_origin'] === 'sample',
        'submitted_at'        => isset($n['submitted_at']) ? (string) $n['submitted_at'] : '',
    ];
}
