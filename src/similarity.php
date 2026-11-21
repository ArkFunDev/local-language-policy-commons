<?php
/**
 * Transparent topic-similarity baseline.
 *
 * This is NOT a machine-learning model. It compares the Indonesian reference text
 * (gloss) and topic label of two proposals using Jaccard word overlap, plus a small
 * bonus when both share the same topic label. Every suggestion therefore comes with
 * the exact shared terms that produced it, and every suggestion requires human review.
 *
 * Limitation (documented in docs/RESPONSIBLE-AI.md): it works on the Indonesian
 * reference text, not on the original local-language text.
 */

function llpc_stopwords()
{
    return [
        'yang', 'dan', 'di', 'ke', 'dari', 'untuk', 'dengan', 'atau', 'lebih', 'agar',
        'pada', 'adalah', 'ini', 'itu', 'dalam', 'para', 'bagi', 'serta', 'juga', 'akan',
        'the', 'and', 'for', 'with',
    ];
}

function llpc_tokens($text)
{
    $text  = function_exists('mb_strtolower') ? mb_strtolower((string) $text, 'UTF-8') : strtolower((string) $text);
    $parts = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
    $stop  = llpc_stopwords();
    $out   = [];
    $parts = $parts ? $parts : [];
    foreach ($parts as $word) {
        if (llpc_len($word) > 2 && !in_array($word, $stop, true)) {
            $out[$word] = true;
        }
    }
    return array_keys($out);
}

/** Order-independent key for a pair of proposal ids. */
function llpc_pair_key($a, $b)
{
    $ids = [(string) $a, (string) $b];
    sort($ids, SORT_STRING);
    return $ids[0] . '|' . $ids[1];
}

/**
 * Suggest proposals related to $current. Each result gains:
 *   _score (0-100), _shared (array of shared terms), _same_topic (bool), _pair_status (unreviewed|confirmed|rejected)
 */
function llpc_similar_proposals($current, $items, $limit = 5, $minScore = 10)
{
    $reviews   = llpc_load_pair_reviews();
    $results   = [];
    $currentId = isset($current['_id']) ? $current['_id'] : '';
    $topicA    = isset($current['suggested_topic']) ? (string) $current['suggested_topic'] : '';
    $a         = llpc_tokens((isset($current['indonesian_gloss']) ? $current['indonesian_gloss'] : '') . ' ' . $topicA);

    foreach ($items as $item) {
        $id = isset($item['_id']) ? $item['_id'] : '';
        if ($id === $currentId) {
            continue;
        }
        $topicB = isset($item['suggested_topic']) ? (string) $item['suggested_topic'] : '';
        $b      = llpc_tokens((isset($item['indonesian_gloss']) ? $item['indonesian_gloss'] : '') . ' ' . $topicB);
        $union  = array_unique(array_merge($a, $b));
        if (!$union) {
            continue;
        }
        $shared    = array_values(array_intersect($a, $b));
        $score     = count($shared) / count($union);
        $sameTopic = ($topicA !== '' && $topicA === $topicB);
        if ($sameTopic) {
            $score = min(1, $score + 0.25);
        }
        $pct = (int) round($score * 100);
        if ($pct < $minScore) {
            continue;
        }

        $key                 = llpc_pair_key($currentId, $id);
        $item['_score']      = $pct;
        $item['_shared']     = $shared;
        $item['_same_topic'] = $sameTopic;
        $item['_pair_status'] = (isset($reviews[$key]['status'])) ? $reviews[$key]['status'] : 'unreviewed';
        $results[]           = $item;
    }

    usort($results, function ($x, $y) {
        return $y['_score'] <=> $x['_score'];
    });
    return array_slice($results, 0, $limit);
}

/** @return bool */
function llpc_set_pair_review($idA, $idB, $decision)
{
    $key = llpc_pair_key($idA, $idB);
    return llpc_update_json(llpc_storage_path('similarity_reviews.json'), function ($data) use ($key, $decision) {
        if ($decision === 'reset') {
            unset($data[$key]);
        } else {
            $data[$key] = ['status' => $decision, 'reviewed_at' => date('c')];
        }
        return $data;
    }, []);
}
