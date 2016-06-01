<?
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
?>
