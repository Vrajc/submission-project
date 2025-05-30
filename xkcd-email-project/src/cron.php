<?php
require_once __DIR__ . '/functions.php';

function fetchAndFormatXKCDData() {
    $randomComicID = rand(1, 2800); // XKCD has over 2800 comics as of 2024
    $url = "https://xkcd.com/{$randomComicID}/info.0.json";
    $comicData = @file_get_contents($url);
    if ($comicData === false) {
        return "<p>Could not fetch XKCD comic at this time.</p>";
    }
    $comic = json_decode($comicData, true);

    if ($comic && isset($comic['img'], $comic['title'], $comic['alt'])) {
        $title = htmlspecialchars($comic['title']);
        $img = htmlspecialchars($comic['img']);
        $alt = htmlspecialchars($comic['alt']);
        $comicNum = intval($comic['num']);
        return "<h2>XKCD Comic #$comicNum: $title</h2>
                <img src=\"$img\" alt=\"$title\" style=\"max-width:100%;\">
                <p><em>$alt</em></p>";
    }
    return "<p>Could not fetch XKCD comic at this time.</p>";
}

function sendXKCDUpdatesToSubscribers() {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$emails) return;

    foreach ($emails as $email) {
        $comicHTML = fetchAndFormatXKCDData();
        $unsubscribeLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/unsubscribe.php?email=" . urlencode($email);
        $comicHTML .= "<p><a href=\"$unsubscribeLink\">Unsubscribe</a></p>";

        $subject = "Your XKCD Comic";
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: no-reply@example.com\r\n";

        mail($email, $subject, $comicHTML, $headers);
    }
}

// Execute the function to send XKCD updates
sendXKCDUpdatesToSubscribers();
?>