<?php
header('Content-Type: application/json');

// List your trusted travel/news RSS feeds here
$feeds = [
    'https://www.bbc.com/news/world/rss.xml',
    'https://www.aljazeera.com/xml/rss/all.xml',
    'https://www.reutersagency.com/feed/?best-topics=travel&post_type=best',
    'https://www.schengenvisainfo.com/news/feed/',
    'https://www.freightwaves.com/feed',
];

// Parse and collect news items
$items = [];
foreach ($feeds as $feed) {
    $rss = @simplexml_load_file($feed);
    if ($rss && isset($rss->channel)) {
        foreach ($rss->channel->item as $entry) {
            $items[] = [
                'Title' => (string) $entry->title,
                'Link' => (string) $entry->link,
                'Published Date' => isset($entry->pubDate) ? date(DATE_ATOM, strtotime($entry->pubDate)) : '',
                'Summary' => isset($entry->description) ? strip_tags((string) $entry->description) : ''
            ];
        }
    }
}

// Sort by date (most recent first)
usort($items, function ($a, $b) {
    return strtotime($b['Published Date']) - strtotime($a['Published Date']);
});

// Limit to 20 items
$items = array_slice($items, 0, 20);

echo json_encode($items, JSON_PRETTY_PRINT);
