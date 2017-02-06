<?php
/*
Sitemap Generator by Slava Knyazev

Website: https://www.knyz.org/
I also live on GitHub: https://github.com/viruzx
Contact me: Slava@KNYZ.org
*/

//Make sure to use the latest revision by downloading from github: https://github.com/viruzx/Sitemap-Generator-Crawler

/* Usage
Usage is pretty strait forward:
- Configure the crawler
- Select the file to which the sitemap will be saved
- Select URL to crawl
- Select accepted extensions ("/" is manditory for proper functionality)
- Select change frequency (always, daily, weekly, monthly, never, etc...)
- Choose priority (It is all relative so it may as well be 1)
- Generate sitemap
- Either send a GET request to this script or simply point your browser
- A sitemap will be generated and displayed
- Submit to Google
- For better results
- Submit sitemap.xml to Google and not the script itself (Both still work)
- Setup a CRON Job to send web requests to this script every so often, this will keep the sitemap.xml file up to date

It is recommended you don't remove the above for future reference.
*/

// Add PHP CLI support
if (php_sapi_name() === 'cli') {
    parse_str(implode('&', array_slice($argv, 1)), $args);
}

$file = "sitemap.xml";
$url = "https://www.knyz.org";

$max_depth = 0;

$enable_frequency = false;
$enable_priority = false;
$enable_modified = false;

$extension = array(
    "/",
    "php",
    "html",
    "htm"
);
$freq = "daily";
$priority = "1";

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

function GetUrl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    $data = curl_exec($ch);
    $timestamp = curl_getinfo($ch, CURLINFO_FILETIME);
    curl_close($ch);
    $modified = date('c', strtotime($timestamp));
    return array($data, $modified);
}

function Check($uri)
{
    global $extension;
    if (is_array($extension)) {
        $string = $uri;
        foreach ($extension as $url) {
            if (endsWith($string, $url) !== FALSE) {
                return true;
            }
        }
    }
    return false;
}

function Scan($url)
{
    global $scanned, $pf, $freq, $priority, $enable_modified, $enable_priority, $enable_frequency, $max_depth, $depth;
    array_push($scanned, $url);
    $depth++;

    if (isset($max_depth) && ($depth <= $max_depth || $max_depth == 0)) {

        list($html, $modified) = GetUrl($url);
        if ($enable_modified != true) unset($modified);

        $regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
        if (preg_match_all("/$regexp/siU", $html, $matches)) {
            if ($matches[2]) {
                $links = $matches[2];
                unset($matches);
                foreach ($links as $href) {

                    list($href, $query_string) = explode('?', $href);

                    if ((substr($href, 0, 7) != "http://") && (substr($href, 0, 8) != "https://") && (substr($href, 0, 6) != "ftp://")) {
                        // If href does not starts with http:, https: or ftp:
                        if ($href == '/') {
                            $href = $scanned[0] . $href;
                        } elseif (substr($href, 0, 1) == '/') {
                            $href = domain_root($scanned[0]) . substr($href, 1);
                        } else {
                            $href = Path($url) . $href;
                        }
                    }

                    if (substr($href, 0, strlen($scanned[0])) == $scanned[0]) {
                        // If href is a sub of the scanned url
                        $ignore = false;

                        if ((!$ignore) && (!in_array($href . ($query_string?'?'.$query_string:''), $scanned)) && Check($href)) {

                            $href = $href . ($query_string?'?'.$query_string:'');

                            $map_row = "<url>\n";
                            $map_row .= "<loc>$href</loc>\n";
                            if ($enable_frequency) $map_row .= "<changefreq>$freq</changefreq>\n";
                            if ($enable_priority) $map_row .= "<priority>$priority</priority>\n";
                            if (!empty($modified)) $map_row .= "   <lastmod>$modified</lastmod>\n";
                            $map_row .= "</url>\n";

                            fwrite($pf, $map_row);

                            echo "Added: " . $href . ((!empty($modified)) ? " [Modified: " . $modified . "]" : '') . "\n";

                            Scan($href);
                        }
                    }

                }
            }
        }
    }
    $depth--;
}

if (isset($args['file'])) $file = $args['file'];
if (isset($args['url'])) $url = $args['url'];

if (endsWith($url, '/')) $url = substr($url, 0, strlen($url) - 1);

$start = microtime(true);
$pf = fopen($file, "w");
if (!$pf) {
    echo "Error: Could not create file - $file\n";
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
Scan($url);
fwrite($pf, "</urlset>\n");
fclose($pf);
$time_elapsed_secs = microtime(true) - $start;
echo "Sitemap has been generated in " . $time_elapsed_secs . " second" . ($time_elapsed_secs >= 1 ? 's' : '') . ".\n";