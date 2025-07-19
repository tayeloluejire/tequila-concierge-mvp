<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Sorted: Football channels first, then global sports
$channelIds = [
    'UCNAf1k0yIjyGu3k9BwAg3lg', // Sky Sports Football
    'UC1U-8zrc6RklwYBNgoynFQg', // LaLiga
    'UCJx5KP-pCUmL9eGD5HjBReg', // UEFA
    'UC1w4Tg3tT1RzL0zR2wPrhgQ', // Serie A
    'UCqZQlzSHbVJrwrn5XvzrzcA', // Bundesliga
    'UCpcTrCXblq78GZrTUTLWeBw', // FIFA
    'UCvt3vVg3xC6m9doiL9dfn2A', // Premier League
    'UCWJ2lWNubArHWmf3FIHbfcQ', // NBA
    'UCVYamHliCI9rw1tHR1xbkfw', // Formula 1
    'UCDVYQ4Zhbm3S2dlz7P1GBDg', // NFL
    'UCvgfXK4nTYKudb0rFR6noLA', // UFC
    'UCbnYKOJGOJvZf1dzdS1UiqA', // Horse racing
    'UCqTW3LDbErTSrDwBK5KJuHw', // TVG
    'UCdKOr9DZysuY_OkZGOv13xQ'  // Dog racing
];

$videos = [];

foreach ($channelIds as $channelId) {
    $feedUrl = "https://www.youtube.com/feeds/videos.xml?channel_id=$channelId";
    $xml = @simplexml_load_file($feedUrl);
    if (!$xml || empty($xml->entry)) continue;

    foreach ($xml->entry as $entry) {
        $ns = $entry->getNamespaces(true);
        $yt = $entry->children($ns['yt']);
        $media = $entry->children($ns['media']);

        $videoId = (string)$yt->videoId;
        $title = (string)$media->group->title;
        $desc = (string)$media->group->description;
        $published = strtotime((string)$entry->published);

        if (
            !$videoId || stripos($title, 'shorts') !== false ||
            stripos($title, 'trailer') !== false || stripos($title, 'deleted') !== false
        ) continue;

        $score = 0;
        if (stripos($title, 'highlights') !== false) $score += 5;
        if (stripos($title, 'final') !== false) $score += 3;
        if (stripos($title, 'goal') !== false || stripos($title, 'match') !== false) $score += 2;

        $videos[] = [
            'video_id' => $videoId,
            'title' => $title,
            'description' => $desc,
            'published' => $published,
            'score' => $score
        ];
    }
}

// Sort by score + publish time
usort($videos, function($a, $b) {
    return ($b['score'] + $b['published']) <=> ($a['score'] + $a['published']);
});

// Limit to 12–16
$final = array_slice($videos, 0, 12);

// Strip score/published before returning
$clean = array_map(function($v) {
    return [
        'video_id' => $v['video_id'],
        'title' => $v['title'],
        'description' => $v['description']
    ];
}, $final);

echo json_encode($clean);
