<?php
// Set headers to ensure proper HTML rendering and allow cross-origin requests (adjust CORS for production)
header('Content-Type: text/html; charset=UTF-8');
header('Access-Control-Allow-Origin: *'); // IMPORTANT: In production, change '*' to your specific domain(s) for security.

// IMPORTANT: Replace with your actual Gemini API key.
// For production, store this in an environment variable or a secure configuration file, NOT directly in the code.
$apiKey = "AIzaSyBWiMCNL6gYBPW14HIKkWq24cQSPvxbLsc";

// Topics to rotate daily
$topics = [
  "Visa-free travel hacks for 2025",
  "Top 10 underrated travel destinations this year",
  "Budget-friendly luxury travel tips",
  "How to plan a multi-country trip in one go",
  "Flight delays and insurance: What travelers must know",
  "The future of African travel: Trends and insights",
  "Best months to visit Europe, Asia, and the Americas",
  "Real stories from Nigerians relocating legally via tourism"
];

// Calculate the current day of the year (0 to 365/366) to select a daily rotating topic
$day = date('z');
$topic = $topics[$day % count($topics)];

$prompt = <<<PROMPT
Write a detailed, SEO-friendly travel blog article (800–1200 words) in a warm, helpful tone.
Topic: "$topic"

Instructions:
- Start with an H1 title.
- Use H2 for major sections and H3 for subpoints.
- Provide useful travel tips, human experiences, and cultural insights.
- Close with a call to action: "Need help planning your next big trip? Tequila Concierge is here to help."
- Add metadata (title, description, Open Graph tags) in the <head>.
PROMPT;

// Gemini Flash Model
$model = "models/gemini-1.5-flash";

// Prepare payload for the Gemini API request
$data = json_encode([
  "contents" => [
    [
      "parts" => [
        ["text" => $prompt]
      ]
    ]
  ]
]);

$headers = [
  "Content-Type: application/json",
  "X-Goog-Api-Key: $apiKey"
];

$url = "https://generativelanguage.googleapis.com/v1beta/" . $model . ":generateContent";

// Initialize cURL session
$ch = curl_init($url);
curl_setopt_array($ch, [
  CURLOPT_RETURNTRANSFER => true, // Return the response as a string
  CURLOPT_POST => true,          // Set as a POST request
  CURLOPT_HTTPHEADER => $headers, // Set request headers
  CURLOPT_POSTFIELDS => $data,   // Set the POST body
  CURLOPT_SSL_VERIFYPEER => true, // Crucial for security: verify SSL certificate
  CURLOPT_SSL_VERIFYHOST => 2,    // Crucial for security: verify host against certificate
  CURLOPT_FOLLOWLOCATION => true  // Follow any redirects
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// Basic error handling for cURL execution
if ($response === false) {
  // Log the error (for server administrators to see)
  error_log("cURL Error for blog generation: " . $error);
  // Output a user-friendly error message
  echo "<h1>Error generating blog post</h1><p>Failed to connect to the AI service. Please try again later.</p><p>Details: " . htmlspecialchars($error) . "</p>";
  exit;
} elseif ($httpCode !== 200) {
  // Attempt to decode and display API-specific error message
  $errorData = json_decode($response, true);
  $errorMessage = $errorData['error']['message'] ?? 'Unknown API error';
  error_log("Gemini API Error (HTTP " . $httpCode . ") for blog generation: " . $errorMessage . " Raw response: " . $response);
  echo "<h1>Error generating blog post</h1><p>AI service returned an error. Please try again later.</p><p>Details: " . htmlspecialchars($errorMessage) . " (HTTP Status: " . $httpCode . ")</p>";
  exit;
}

$json = json_decode($response, true);

// Check if the expected content part exists in the API response
if (!isset($json['candidates'][0]['content']['parts'][0]['text'])) {
  error_log("No content returned from Gemini API for blog post. Raw Response: " . $response);
  echo "<h1>Error generating blog post</h1><p>AI service did not return any content. Please try again later.</p>";
  exit;
}

$htmlContent = $json['candidates'][0]['content']['parts'][0]['text'];

// Use DOMDocument to parse the AI-generated HTML and extract useful meta info
$dom = new DOMDocument();
// Suppress warnings that might arise from HTML5 tags or malformed fragments in the AI output
@$dom->loadHTML($htmlContent, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

$generatedTitle = '';
$generatedDescription = '';

// Extract the H1 title
$titleNodes = $dom->getElementsByTagName('h1');
if ($titleNodes->length > 0) {
    $generatedTitle = $titleNodes->item(0)->textContent;
}

// Extract a description from the first paragraph, truncated for meta description length
$pNodes = $dom->getElementsByTagName('p');
if ($pNodes->length > 0) {
    $generatedDescription = substr($pNodes->item(0)->textContent, 0, 160); // Standard meta description length
    if (strlen($pNodes->item(0)->textContent) > 160) {
        $generatedDescription .= '...';
    }
}

// Construct the full HTML page, including dynamic meta tags and your site's standard includes
$finalHtml = "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>" . htmlspecialchars($generatedTitle ?: $topic . " | Tequila Concierge Blog") . "</title>
    <meta name=\"description\" content=\"" . htmlspecialchars($generatedDescription ?: "Read the latest travel tips and insights from Tequila Concierge.") . "\">
    <meta property=\"og:title\" content=\"" . htmlspecialchars($generatedTitle ?: $topic . " | Tequila Concierge Blog") . "\">
    <meta property=\"og:description\" content=\"" . htmlspecialchars($generatedDescription ?: "Read the latest travel tips and insights from Tequila Concierge.") . "\">
    <meta property=\"og:type\" content=\"article\">
    <meta property=\"og:url\" content=\"https://tequilaconciergehub.com/travelblog.php\"> <meta property=\"og:image\" content=\"https://tequilaconciergehub.com/images/blog_social_banner.jpg\"> <meta name=\"twitter:card\" content=\"summary_large_image\">
    <link rel=\"canonical\" href=\"https://tequilaconciergehub.com/travelblog.php\"> <link href=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css\" rel=\"stylesheet\" />
    <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css\">
    <link rel=\"stylesheet\" href=\"css/custom_styles.css\" />
    <style>
        body { padding-top: 70px; } /* Ensure space for fixed navbar */
        .blog-post-content { padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .blog-post-content h1, .blog-post-content h2, .blog-post-content h3 { color: var(--primary-teal); margin-top: 1.5em; margin-bottom: 0.5em; }
        .blog-post-content p { line-height: 1.7; margin-bottom: 1em; }
        .blog-header {
            background: linear-gradient(rgba(0, 77, 64, 0.8), rgba(0, 77, 64, 0.8)), url('images/blog-banner.jpg') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        .blog-header h1 { font-size: 3rem; font-weight: bold; }
        .blog-header p { font-size: 1.2rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg sticky-top" id="mainNavbar">
        <div class="container-fluid px-lg-5">
            <a class="navbar-brand d-flex align-items-center" href="https://tequilaconciergehub.com">
                <img src="images/logo.png" alt="Tequila Concierge Logo" class="navbar-logo" />
                <span class="ms-2 fw-bold text-primary-teal">TEQUILA CONCIERGE</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="https://tequilaconciergehub.com">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownOurServices" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Our Services
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownOurServices">
                            <li><a class="dropdown-item" href="https://flights.tequilaconciergehub.com/">Flight Booking</a></li>
                            <li><a class="dropdown-item" href="https://tequilaconciergehub.com/Tequila_Concierge_Visa_Assitance.html">Visa Assistance</a></li>
                            <li><a class="dropdown-item" href="https://tequilaconciergehub.com/Tequila_Passport_Escape.html">Summer Passport Escape</a></li>
                            <li><a class="dropdown-item" href="https://tequilaconciergehub.com/Tequila_Rest&Recharge.html">Rest and Recharge</a></li>
                            <li><a class="dropdown-item" href="https://tequilaconciergehub.com/Tequila_Students_flyback.html">Student Fly Back</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle active" aria-current="page" href="#" id="navbarDropdownTravelBlog" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Travel Blog
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownTravelBlog">
                            <li><a class="dropdown-item" href="https://tequilaconciergehub.com/travelblog.php">Inspiration Hub</a></li>
                            <li><a class="dropdown-item" href="https://tequilaconciergehub.com/live-visa-news-feed.html">Travel/Visa News</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownPartners" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Partners & Affiliates
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownPartners">
                            <li><a class="dropdown-item" href="https://flights.tequilaconciergehub.com">Flight Search</a></li>
                            <li><a class="dropdown-item" href="https://www.expedia.com/Cars?affcid=1011l402750&utm_source=tequilaconciergehub&utm_campaign=car" target="_blank" rel="noopener noreferrer">Car Ride</a></li>
                            <li><a class="dropdown-item" href="https://www.expedia.com/Hotels?affcid=1011l402750&utm_source=tequilaconciergehub&utm_campaign=car" target="_blank" rel="noopener noreferrer">Hotel & Accommodation</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownCompany" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Company
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownCompany">
                            <li><a class="dropdown-item" href="https://tequilaconciergehub.com/company.html">About Us</a></li>
                            <li><a class="dropdown-item" href="#">Careers</a></li>
                            <li><a class="dropdown-item" href="#">Testimonials</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://tequilaconciergehub.com/Tequila_Global_Events.html">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="https://tequilaconciergehub.com/Tequila_Global_sports.html">Sports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-gold-accent text-dark px-3 py-2 ms-lg-3 rounded-pill" href="https://flights.tequilaconciergehub.com/find-my-perfect-flight.html">Submit
                            Request</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <section class="blog-header">
        <div class="container">
            <h1>" . htmlspecialchars($generatedTitle ?: $topic) . "</h1>
            <p>" . htmlspecialchars($generatedDescription ?: "Dive into the world of travel with Tequila Concierge.") . "</p>
        </div>
    </section>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <article class="blog-post-content">
                    " . $htmlContent . "
                </article>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5>Tequila Concierge</h5>
                    <p class="text-muted">Your Global Travel Ally.</p>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="https://tequilaconciergehub.com" class="text-white-50 text-decoration-none">Home</a></li>
                        <li><a href="https://tequilaconciergehub.com/company.html" class="text-white-50 text-decoration-none">About Us</a></li>
                        <li><a href="https://tequilaconciergehub.com/travelblog.php" class="text-white-50 text-decoration-none">Travel Blog</a></li>
                        <li><a href="https://flights.tequilaconciergehub.com/find-my-perfect-flight.html" class="text-white-50 text-decoration-none">Submit Request</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-3">
                    <h5>Contact Us</h5>
                    <p class="text-muted">Email: info@tequilaconciergehub.com</p>
                    <p class="text-muted">Phone: +234 816 250 9258</p>
                    <div class="social-icons mt-3">
                        <a href="https://facebook.com/tequilaconcierge" target="_blank" class="text-white me-2"><i class="bi bi-facebook"></i></a>
                        <a href="https://twitter.com/tequilaconcierge" target="_blank" class="text-white me-2"><i class="bi bi-twitter"></i></a>
                        <a href="https://instagram.com/tequilaconcierge" target="_blank" class="text-white me-2"><i class="bi bi-instagram"></i></a>
                        <a href="https://linkedin.com/company/tequilaconcierge" target="_blank" class="text-white me-2"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
            <div class="text-center text-muted border-top pt-3 mt-3">
                © <span id="currentYear"></span> Tequila Concierge. All rights reserved.
            </div>
        </div>
    </footer>

    <script src=\"https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js\"></script>
    <script>
        document.getElementById('currentYear').textContent = new Date().getFullYear();
    </script>
</body>
</html>";

echo $finalHtml;
?>