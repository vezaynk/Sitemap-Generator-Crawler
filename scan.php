<?
function Scan($url)
{
    global $scanned, $pf, $skip, $freq, $priority;
    array_push($scanned, $url);
    $html = GetUrl($url);
    $modified = getLastModified($html[0]);
    $a1   = explode("<a", $html[1]);
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
                if(!empty($modified))$map_row .= "<lastmod>$modified</lastmod>";
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
?>
