<?php
/*
Sitemap Generator by Slava Knyazev

Website: https://www.knyz.org/
I also live on GitHub: https://github.com/knyzorg
Contact me: Slava@KNYZ.org
*/

//Make sure to use the latest revision by downloading from github: https://github.com/knyzorg/Sitemap-Generator-Crawler

/* Usage
Usage is pretty strait forward:
- Configure the crawler
- Select the file to which the sitemap will be saved
- Select URL to crawl
- Configure blacklists, accepts the use of wildcards (example: http://example.com/private/* and *.jpg)
- Generate sitemap
- Either send a GET request to this script or simply point your browser
- Submit to Google
- Setup a CRON Job to send web requests to this script every so often, this will keep the sitemap.xml file up to date

It is recommended you don't remove the above for future reference.
*/

// Add PHP CLI support
if (php_sapi_name() === 'cli') {
    parse_str(implode('&', array_slice($argv, 1)), $args);
}

//Site to crawl
$target = "https://www.knyz.org";

//Location to save file
$file = "sitemap.xml";

//If you don't know what these do, don't touch them ;)
$max_depth = 0;
$enable_frequency = false;
$enable_priority = false;
$enable_modified = false;
$curl_validate_certificate = true;
$freq = "daily";
$priority = "1";

//The pages will not be crawled and will not be included in sitemap
//Use this list to exlude non-html files to increase performance and save bandwidth
$blacklist = array(
    "*.jpg",
    "*.png",
    "*/secretstuff/*"
);



/* Coming soon
$debug = Array(
    "add" => true,
    "reject" => true,
    "manipulation" => true
);*/

/* NO NEED TO EDIT BELOW THIS LINE */

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }
    return (substr($haystack, -$length) === $needle);
}

function Path($p)
{
    $a = explode("/", $p);
    $len = strlen($a[count($a) - 1]);
    return (substr($p, 0, strlen($p) - $len));
}

function domain_root($href) {
    $url_parts = explode('/', $href);
    return $url_parts[0].'//'.$url_parts[2].'/';
}

function GetData($url)
{
    global $curl_validate_certificate;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $curl_validate_certificate);
    $data = curl_exec($ch);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $html = ($http_code != 200 || (!stripos($content_type, "html"))) ? false : $data;
    $timestamp = curl_getinfo($ch, CURLINFO_FILETIME);
    curl_close($ch);
    $modified = date('c', strtotime($timestamp));
    return array($html, $modified);
}


function CheckBlacklist($uri)
{
    global $blacklist;
    if (is_array($blacklist)) {
        $string = $uri;
        foreach ($blacklist as $illegal) {
            if (fnmatch($illegal,$string)) {
                return false;
            }
        }
    }
    return true;
}

function Scan($url)
{
    global $scanned, $pf, $freq, $priority, $enable_modified, $enable_priority, $enable_frequency, $max_depth, $depth, $target;
    $depth++;
    
    $proceed = true;
    echo "[!] Scanning $url\n";
    
    
    array_push($scanned, $url);
    list($html, $modified) = GetData($url);
    if (!$html){
        echo "[-] Invalid Document. Rejecting. \n";
        $proceed = false;
    }

    elseif (!($depth <= $max_depth || $max_depth == 0)){
        echo "[-] Maximum depth exceeded. Rejecting. \n";
        $proceed = false;
    }
    if ($proceed) {

        
        if (!$enable_modified) unset($modified);

        $map_row = "<url>\n";
        $map_row .= "<loc>$url</loc>\n";
        if ($enable_frequency) $map_row .= "<changefreq>$freq</changefreq>\n";
        if ($enable_priority) $map_row .= "<priority>$priority</priority>\n";
        if (!empty($modified)) $map_row .= "   <lastmod>$modified</lastmod>\n";
        $map_row .= "</url>\n";
        fwrite($pf, $map_row);

        echo "[+] Added: " . $url . ((!empty($modified)) ? " [Modified: " . $modified . "]" : '') . "\n";

        $regexp = "<a\s[^>]*href=(\"|'??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
        if (preg_match_all("/$regexp/siU", $html, $matches)) {
            if ($matches[2]) {
                $links = $matches[2];
                foreach ($links as $href) {
                    echo "[+] Found $href\n";
                    if (strpos($href, '?') !== false) list($href, $query_string) = explode('?', $href);
                    else $query_string = '';

                    if (strpos($href, "#") !== false){
                        echo "[!] Dropping pound.";
                        $href = strtok($href, "#");
                    }
                    if ((substr($href, 0, 7) != "http://") && (substr($href, 0, 8) != "https://")) {
                        // Link does not call (potentially) external page
                        
                        if ($href == '/') {
                            echo "[!] $href is domain root\n";
                            $href = $target . $href;
                        }
                        elseif (substr($href, 0, 1) == '/') {
                            echo "[!] $href is relative to root, convert to absolute\n";
                            $href = domain_root($target) . substr($href, 1);
                        } else {
                            echo "[!] $href is relative, convert to absolute\n";
                            $href = Path($url) . $href;
                        }
                    }
                        echo "[!] Result: $href\n";
                        //Assume that URL is okay until it isn't
                        $valid = true;

                        if (!filter_var($href, FILTER_VALIDATE_URL)) {
                            echo "[-] URL is not valid. Rejecting.\n";
                            $valid = false;
                        }

                        if (substr($href, 0, strlen($target)) != $target){
                            echo "[-] URL is not part of the target domain. Rejecting.\n";
                            $valid = false;
                        }
                        if (in_array($href . ($query_string?'?'.$query_string:''), $scanned)){
                            echo "[-] URL has already been scanned. Rejecting.\n";
                            $valid = false;
                        }
                        if (!CheckBlacklist($href)){
                            echo "[-] URL is blacklisted. Rejecting.\n";
                            $valid = false;
                        }
                        if ($valid) {

                            $href = $href . ($query_string?'?'.$query_string:'');

                            
                            Scan($href);
                        }

                }
            }
        }
    }
    $depth--;
}
header("Content-Type: text/plain");
if (isset($args['file'])) $file = $args['file'];
if (isset($args['url'])) $url = $args['url'];

$start = microtime(true);
$pf = fopen($file, "w");
if (!$pf) {
    echo "[-] Error: Could not create file - $file\n";
    exit;
}
fwrite($pf, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<urlset
      xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"
      xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
      xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">
");
$depth = 0;
$scanned = array();
Scan($target);
fwrite($pf, "</urlset>\n");
fclose($pf);
$time_elapsed_secs = microtime(true) - $start;
echo "[+] Sitemap has been generated in " . $time_elapsed_secs . " second" . ($time_elapsed_secs >= 1 ? 's' : '') . ".\n";