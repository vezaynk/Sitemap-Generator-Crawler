<?
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
$file      = "sitemap.xml";
$url       = "https://www.knyz.org";
$extension = array(
    "/",
    "php",
    "html",
    "htm"
);
$freq      = "daily";
$priority  = "1";

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
    $a   = explode("/", $p);
    $len = strlen($a[count($a) - 1]);
    return (substr($p, 0, strlen($p) - $len));
}
function GetUrl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
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
        return false;
    }
}
function GetUrlModified($url)
{
  $hdr = get_headers($url, 1);
  if(!empty($hdr['Last-Modified'])){
    return date('c', strtotime($hdr['Last-Modified']));
  }else{
    return false;
  }
}
function Scan($url)
{
    global $scanned, $pf, $skip, $freq, $priority;
    array_push($scanned, $url);
    $html = GetUrl($url);
    $modified = GetUrlModified($url);
    $a1   = explode("<a", $html);
    foreach ($a1 as $key => $val) {
        $parts      = explode(">", $val);
        $a          = $parts[0];
        $aparts     = explode("href=", $a);
        $hrefparts  = explode(" ", $aparts[1]);
        $hrefparts2 = explode("#", $hrefparts[0]);
        $href       = str_replace("\"", "", $hrefparts2[0]);
        if ((substr($href, 0, 7) != "http://") && (substr($href, 0, 8) != "https://") && (substr($href, 0, 6) != "ftp://")) {
            if ($href[0] == '/')
                $href = "$scanned[0]$href";
            else
                $href = Path($url) . $href;
        }
        if (substr($href, 0, strlen($scanned[0])) == $scanned[0]) {
            $ignore = false;
            if (isset($skip))
                foreach ($skip as $k => $v)
                    if (substr($href, 0, strlen($v)) == $v)
                        $ignore = true;
            if ((!$ignore) && (!in_array($href, $scanned)) && Check($href)) {
                
                $map_row = "<url>\n  <loc>$href</loc>\n" . "  <changefreq>$freq</changefreq>\n" . "  <priority>$priority</priority>\n";
                if(!empty($modified))$map_row .= "   <lastmod>$modified</lastmod>\n";
                $map_row .= "</url>\n";
              
                fwrite($pf, $map_row);
                Scan($href);
            }
        }
    }
}
$pf = fopen($file, "w");
if (!$pf) {
    echo "cannot create $file\n";
    return;
}
fwrite($pf, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<urlset
      xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\"
      xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\"
      xsi:schemaLocation=\"http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd\">
<url>
  <loc>$url/</loc>
  <changefreq>daily</changefreq>
</url>
");
$scanned = array();
Scan($url);
fwrite($pf, "</urlset>\n");
fclose($pf);
echo "Sitemap Generated";
?>

