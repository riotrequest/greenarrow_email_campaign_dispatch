<?php

// Report all PHP errors on the screen
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('GAS_BASE_URL', 'https://YOUR_MAILER_DOMAIN/ga/api');
define('GAS_API_KEY', 'YOUR_API_KEY');

function extract_text_from_html($html) {
    $doc = new DOMDocument();
    @$doc->loadHTML($html); // suppress warnings
    $xpath = new DOMXPath($doc);

    $text = '';

    // Extract the site name from the first image tag and omit the image URL
    $logoNodes = $xpath->query('//img');
    if ($logoNode = $logoNodes->item(0)) { // Get the first image
        $siteName = $logoNode->getAttribute('alt');
        if (!empty($siteName)) {
            $text .= $siteName . "\n\n";
        }
    }

    // Extract titles, descriptions, and links
    $nodes = $xpath->query('//a');
    foreach ($nodes as $node) {
        $linkText = trim($node->nodeValue);
        $linkHref = $node->getAttribute('href');

        // Skip certain URLs and image links (which usually have empty text content)
        // This specifically skips an ad I include in my email sends. You can edit it or leave it as-is, it won't hurt anything.
        if (strpos($linkHref, 'rs-stripe.') !== false || empty($linkText)) {
            continue;
        }

        $text .= $linkText . " ( " . $linkHref . " )\n\n";
    }

    // Extract and append footer information
    $footerNodes = $xpath->query('//span[@class="email-footer"]');
    foreach ($footerNodes as $footerNode) {
        $footerText = trim($footerNode->nodeValue);
        if (!empty($footerText)) {
            $text .= $footerText . "\n";
        }
    }

    return html_entity_decode($text, ENT_QUOTES | ENT_HTML5); // Decode HTML entities
}

function greenarrow_studio_send_email($params) {
    $listID   = $params['mailing_list_id'];
    $url      = GAS_BASE_URL.'/v2/mailing_lists/'.$listID.'/campaigns';
    $ch       = curl_init($url);
    $campaign = array('campaign' => $params);
    $json     = json_encode($campaign);

    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, base64_decode(GAS_API_KEY));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($json),
    ));

    $response_raw = curl_exec($ch);
    $error_test   = curl_error($ch);
    $err          = curl_errno($ch);

    curl_close($ch);

    if ($err != 0) {
        return "ERROR: cURL - $err $error_test\n";
    }

    $result = json_decode($response_raw);

    if (isset($result->success)) {
        if ($result->success == false) {
            $return_value = "Error:";
            if (isset($result->error_message)) {
                $return_value .= " " . $result->error_message;
            }
        } else if ($result->success == true) {
            $return_value = "OK";
        } else {
            $return_value = "Error: unknown status";
        }
    } else {
        $return_value = "Error: unknown response from GAS Station";
    }

    return $return_value;
}

// Fetch and process the first template
$html1 = file_get_contents('https://YOUR_DOMAIN.com/email-template/');
@$doc1 = new DOMDocument();
$doc1->loadHTML($html1); // suppress warnings
$xpath1 = new DOMXPath($doc1);
$subject1 = $xpath1->query("//title")->item(0)->nodeValue;

$rand1 = time();
$rand1 = substr($rand1, -4); // Take the last 4 digits of the timestamp for variability

// I noticed it wont repeat an email send that shares the same name and it won't duplicate it automatically (if it does I haven't figured it out yet). So I wrote some logic to take care of that.
$title_words1 = explode(' ', $subject1);
$name1 = implode(' ', array_slice($title_words1, 0, 8)); // Limit to 8 words

if (count($title_words1) > 4) {
    $name1 = $rand1 . ' ' . $name1 . '...';
}

// I've noticed that if certain characters appear in the NAME part of what you're sending it won't process so I added logic for the ones I had issues with to remove them. It only affects the NAME and not the SUBJECT.
$search1 = ["'", ":", "-", "\"", ",", "|"];
$replace1 = ["", "", "", "", "", ""];
$name1 = str_replace($search1, $replace1, $name1);

$text1 = extract_text_from_html($html1); // Extract text from the first HTML

$date_ext = (new DateTime())->format('Y-n-j');
$time_ext0 = '04:00AM EST';
$time_ext1 = '11:00AM EST';
$formatted_time0 = "$date_ext $time_ext0";
$formatted_time1 = "$date_ext $time_ext1";

$dispatch_parameters = array(
    array(
        'name' => $name1,
        'mailing_list_id' => 01, // set your mailing list id
        'segmentation_criteria_id' => 01, // set your segment ID (can be null)
        'dispatch_attributes' => array(
            'state' => 'scheduled',
            'from_email' => 'YOUR_FROM_EMAIL',
            'from_name' => 'YOUR_FROM_NAME',
            'speed' => 4375, // set your sending speed
            'virtual_mta_id' => 01, // set your MTA ID
            'bounce_email_id' => '0@01', // set your bounce email ID
            'url_domain_id' => 01, // set your domain url ID
            'begins_at' => $formatted_time0,
            'track_opens' => true,
            'track_links' => '1',
        ),
        'contents' => array(
            array(
                'name' => $name1,
                'format'  => 'both',
                'subject' => $subject1,
                'html'    => $html1,
                'text'    => $text1,
            ),
        ),
    ),
);


foreach ($dispatch_parameters as $params) {
    $result = greenarrow_studio_send_email($params);
    echo $result . "\n";
}
?>
