
<?php

/**
 * Initial lexical similarity prototype.
 * Compares Indonesian reference text, not semantic meaning.
 */

function tokenizeProposal($text) {
    $text = function_exists('mb_strtolower')
        ? mb_strtolower($text, 'UTF-8')
        : strtolower($text);

    $words = preg_split('/[^\p{L}\p{N}]+/u', $text);

    $stopwords = [
        'yang', 'dan', 'di', 'ke', 'dari', 'untuk',
        'dengan', 'atau', 'lebih', 'agar', 'pada',
        'adalah', 'ini', 'itu', 'dalam', 'membutuhkan'
    ];

    $words = array_filter($words, function ($word) use ($stopwords) {
        return strlen($word) > 2 && !in_array($word, $stopwords);
    });

    return array_values(array_unique($words));
}

function calculateSimilarity($textA, $textB) {
    $a = tokenizeProposal($textA);
    $b = tokenizeProposal($textB);

    if (empty($a) || empty($b)) {
        return 0;
    }

    $intersection = count(array_intersect($a, $b));
    $union = count(array_unique(array_merge($a, $b)));

    if ($union === 0) {
        return 0;
    }

    return round(($intersection / $union) * 100);
}

function getSimilarProposals($current, $proposals, $limit = 2) {
    $results = [];

    foreach ($proposals as $proposal) {
        if ($proposal['id'] === $current['id']) {
            continue;
        }

        $score = calculateSimilarity(
            $current['indonesian_gloss'] ?? '',
            $proposal['indonesian_gloss'] ?? ''
        );

        if ($score > 0) {
            $proposal['similarity_score'] = $score;
            $results[] = $proposal;
        }
    }

    usort($results, function ($a, $b) {
        return $b['similarity_score'] <=> $a['similarity_score'];
    });

    return array_slice($results, 0, $limit);
}
