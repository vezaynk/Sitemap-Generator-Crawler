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
    // curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    $data = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($data, 0, $header_size);
    $body = substr($data, $header_size);

    curl_close($ch);
    return array($header,$body);
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
function getLastModified($data)
{
  $lines = explode("\n",$data);
  $search = 'Last-Modified:';
  $key = array_keys(array_filter($lines, function($var) use ($search){
    return strpos($var, $search) !== false;
  }));
  if($key && $key[0]){
    $date = ltrim($lines[$key[0]],'Last-Modified: ');
    return date('c',strtotime($date));
  }
  return false;
}
?>
