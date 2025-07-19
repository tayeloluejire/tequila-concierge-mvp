<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$apiKey = 'AIzaSyACeMZ_OkLGawcm8nY3WZjqQdhTRYPZH_Y'; // ✅ your working API key
$searchQuery = 'sports highlights';
$maxResults = 6;

$params = [
    'key' => $apiKey,
    'q' => $searchQuery,
    'part' => 'snippet',
    'type' => 'video',
    'maxResults' => $maxResults,
    'videoEmbeddable' => 'true',
    'relevanceLanguage' => 'en',
    'regionCode' => 'US',
    'safeSearch' => 'strict'
];

$apiUrl = "https://www.googleapis.com/youtube/v3/search?" . http_build_query($params);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$results = [];

if (isset($data['items'])) {
    foreach ($data['items'] as $item) {
        $results[] = [
            'video_id' => $item['id']['videoId'],
            'title' => $item['snippet']['title'],
            'description' => $item['snippet']['description'],
            'thumbnail' => $item['snippet']['thumbnails']['high']['url']
        ];
    }
}

echo json_encode($results);
