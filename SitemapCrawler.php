<?php

namespace knyzorg\sitemap;


class SitemapCrawler
{
    public $site = "https://www.knyz.org/";

    public $file = "sitemap.xml";

    public $debug = [
        "add" => true,
        "reject" => false,
        "warn" => false
    ];

    public $permissions = 0644;

    public $real_site;

    public $crawler_user_agent = "Mozilla/5.0 (compatible; Sitemap Generator Crawler; +https://github.com/knyzorg/Sitemap-Generator-Crawler)";

    public $curl_validate_certificate = true;

    public $blacklist = [
        "*.jpg",
        "*/secrets/*",
        "https://www.knyz.org/supersecret"
    ];

    public $ignore_arguments = false;

    public $max_depth = 0;

    public $enable_modified = false;

    public $enable_priority = false;

    public $enable_frequency = false;

    public $freq = "daily";

    public $priority = 1;

    public $index_img = false;

    public $index_pdf = true;

    public $xmlheader = '<?xml version="1.0" encoding="UTF-8"?>
<urlset
xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

    public $version_config = 2;

    public $color=false;
    private $deferredLinks = [];

    private $file_stream;

    private $depth = 0;

    private $indexed = 0;

    private $curl_client;

    private $scanned = [];

    public function __construct()
    {
        //The curl client is create outside of the function to avoid re-creating it for performance reasons
        $this->curl_client = curl_init();


    }

    public function loadConfig($config) {
        foreach ($config as $key => $value) {
            if(isset($this->{$key})) {
                $this->{$key}= $value;
            }
        }
    }
    /**
     * @return string|void
     */
    public function start()
    {
        $this->real_site = $this->getDomainRoot($this->site);

        if ($this->real_site != $this->site) {
            $this->logger("Reformatted site from $this->site to $this->real_site", 2);
        }

        //Begin stopwatch for statistics
        $start = microtime(true);

        //Setup file stream
        $tempfile = tempnam(sys_get_temp_dir(), 'sitemap.xml.');
        $this->file_stream = fopen($tempfile, "w");
        if($this->file_stream ==null) {
            return ("Error: Could not create temporary file $tempfile" . "\n");
        }

        fwrite($this->file_stream, $this->xmlheader);

        // Begin by crawling the original url
        $this->scanUrl($this->real_site);

        // Finalize sitemap
        fwrite($this->file_stream, "</urlset>\n");
        fclose($this->file_stream);

        // Pretty-print sitemap
        if ((PHP_OS == 'WINNT') ? `where xmllint` : `which xmllint`) {
            $this->logger("Found xmllint, pretty-printing sitemap", 0);
            $responsevalue = exec('xmllint --format ' . $tempfile . ' -o ' . $tempfile . ' 2>&1', $discardedoutputvalue, $returnvalue);
            if ($returnvalue) {
                return ("Error: " . $responsevalue . "\n");
            }
        }

        // Generate and print out statistics
        $time_elapsed_secs = round(microtime(true) - $start, 2);
        $this->logger("Sitemap has been generated in " . $time_elapsed_secs . " second" . (($time_elapsed_secs >= 1 ? 's' : '') . "and saved to $this->file"), 0);
        $size = sizeof($this->scanned);
        $this->logger("Scanned a total of $size pages and indexed $this->indexed pages.", 0);

        // Rename partial file to the real file name. `rename()` overwrites any existing files
        rename($tempfile, $this->file);

        // Apply permissions
        chmod($this->file, $this->permissions);

        // Declare that the script has finished executing and exit
        return $this->logger("Operation Completed", 0);
    }

    private function getData($url)
    {
        //Set URL
        curl_setopt($this->curl_client, CURLOPT_URL, $url);
        //Follow redirects and get new url
        curl_setopt($this->curl_client, CURLOPT_RETURNTRANSFER, 1);
        //Get headers
        curl_setopt($this->curl_client, CURLOPT_HEADER, 1);
        //Optionally avoid validating SSL
        curl_setopt($this->curl_client, CURLOPT_SSL_VERIFYPEER, $this->curl_validate_certificate);
        //Set user agent
        curl_setopt($this->curl_client, CURLOPT_USERAGENT, $this->crawler_user_agent);

        //Get data
        $data = curl_exec($this->curl_client);
        $content_type = curl_getinfo($this->curl_client, CURLINFO_CONTENT_TYPE);
        $http_code = curl_getinfo($this->curl_client, CURLINFO_HTTP_CODE);
        $redirect_url = curl_getinfo($this->curl_client, CURLINFO_REDIRECT_URL);

        //Scan new url, if redirect
        if ($redirect_url) {
            $this->logger("URL is a redirect.", 1);
            if (strpos($redirect_url, '?') !== false) {
                $redirect_url = explode($redirect_url, "?")[0];
            }
            unset($url, $data);

            if (!$this->checkBlacklist($redirect_url)) {
                echo $this->logger("Redirected URL is in blacklist", 1);

            } else {
                $this->scanUrl($redirect_url);
            }
        }

        //If content acceptable, return it. If not, `false`
        $html = ($http_code != 200 || (!stripos($content_type, "html"))) ? false : $data;

        //Additional data
        $timestamp = curl_getinfo($this->curl_client, CURLINFO_FILETIME);
        $modified = date('c', strtotime($timestamp));
        if (stripos($content_type, "application/pdf") !== false && $this->index_pdf) {
            $html = "This is a PDF";
        }
        //Return it as an array
        return array($html, $modified, (stripos($content_type, "image/") && $index_img));
    }

    private function logger($message, $type)
    {
        if ($this->color) {
            switch ($type) {
                case 0:
                    //add
                    echo $this->debug["add"] ? "\033[0;32m [+] $message \033[0m\n" : "";
                    break;
                case 1:
                    //reject
                    echo $this->debug["reject"] ? "\033[0;31m [-] $message \033[0m\n" : "";
                    break;
                case 2:
                    //manipulate
                    echo $this->debug["warn"] ? "\033[1;33m [!] $message \033[0m\n" : "";
                    break;
                case 3:
                    //critical
                    echo "\033[1;33m [!] $message \033[0m\n";
                    break;
            }
            return;
        }
        switch ($type) {
            case 0:
                //add
                echo $this->debug["add"] ? "[+] $message\n" : "";
                break;
            case 1:
                //reject
                echo $this->debug["reject"] ? "31m [-] $message\n" : "";
                break;
            case 2:
                //manipulate
                echo $this->debug["warn"] ? "[!] $message\n" : "";
                break;
            case 3:
                //critical
                echo "[!] $message\n";
                break;
        }
    }

    private function checkBlacklist($string)
    {
        if (is_array($this->blacklist)) {
            foreach ($this->blacklist as $illegal) {
                if (fnmatch($illegal, $string)) {
                    return false;
                }
            }
        }
        return true;
    }

    // Check if a URL has already been scanned
    public function scanUrl($url)
    {
        $this->depth++;

        $this->logger("Scanning $url", 2);
        if ($this->isScanned($url)) {
            $this->logger("URL has already been scanned. Rejecting.", 1);
            return $this->depth--;
        }
        if (substr($url, 0, strlen($this->real_site)) != $this->real_site) {
            $this->logger("URL is not part of the target domain. Rejecting.", 1);
            return $this->depth--;
        }
        if (!($this->depth <= $this->max_depth || $this->max_depth == 0)) {
            $this->logger("Maximum depth exceeded. Rejecting.", 1);
            return $this->depth--;
        }

        //Note that URL has been scanned
        $this->scanned[$url] = 1;

        //Send cURL request
        list($html, $modified, $is_image) = $this->getData($url);

        if ($is_image) {
            //Url is an image
        }

        if (!$html) {
            $this->logger("Invalid Document. Rejecting.", 1);
            return $this->depth--;
        }
        if (!$this->enable_modified) {
            unset($modified);
        }

        if (strpos($url, "&") && strpos($url, ";") === false) {
            $url = str_replace("&", "&amp;", $url);
        }

        $map_row = "<url>\n";
        $map_row .= "<loc>$url</loc>\n";
        if ($this->enable_frequency) {
            $map_row .= "<changefreq>$this->freq</changefreq>\n";
        }
        if ($this->enable_priority) {
            $map_row .= "<priority>$this->priority</priority>\n";
        }
        if (!empty($modified)) {
            $map_row .= "   <lastmod>$modified</lastmod>\n";
        }
        $map_row .= "</url>\n";
        fwrite($this->file_stream, $map_row);
        $this->indexed++;
        $this->logger("Added: " . $url . ((!empty($modified)) ? " [Modified: " . $modified . "]" : ''), 0);
        unset($is_image, $map_row);

        // Extract urls from <a href="??"></a>
        $ahrefs = $this->getLinks($html, $url, "<a\s[^>]*href=(\"|'??)([^\" >]*?)\\1[^>]*>(.*)<\/a>");

        // Extract urls from <frame src="??">
        $framesrc = $this->getLinks($html, $url, "<frame\s[^>]*src=(\"|'??)([^\" >]*?)\\1[^>]*>");
        $deferredLinks = $this->deferredLinks;
        $links = array_filter(array_merge($ahrefs, $framesrc), function ($item) use (&$deferredLinks) {
            return $item && !isset($deferredLinks[$item]);
        });
        unset($html, $url, $ahrefs, $framesrc);

        $this->logger("Found urls: " . join(", ", $links), 2);

        //Note that URL has been deferred
        foreach ($links as $href) {
            if ($href) {
                $this->deferredLinks[$href] = 1;
            }
        }

        foreach ($links as $href) {
            if ($href) {
                $this->scanUrl($href);
            }
        }
        $this->depth--;
    }

    private function isScanned($url)
    {

        if (isset($this->scanned[$url])) {
            return true;
        }

        //Check if in array as dir and non-dir
        $url = $this->endsWith($url, "/") ? substr($url, 0, -1) : $url . "/";
        if (isset($this->scanned[$url])) {
            return true;
        }

        return false;
    }

    // Gets path for a relative linl
    // https://somewebsite.com/directory/file => https://somewebsite.com/directory/
    // https://somewebsite.com/directory/subdir/ => https://somewebsite.com/directory/subdir/
    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }

    //Get the root of the domain
    private function getLinks($html, $parent_url, $regexp)
    {
        if (preg_match_all("/$regexp/siU", $html, $matches)) {
            if ($matches[2]) {
                $found = array_map(function ($href) use (&$parent_url) {

                    $this->logger("Checking $href", 2);

                    if (strpos($href, "#") !== false) {
                        $this->logger("Dropping pound.", 2);
                        $href = preg_replace('/\#.*/', '', $href);
                    }

                    //Seperate $href from $query_string
                    $query_string = '';
                    if (strpos($href, '?') !== false) {
                        list($href, $query_string) = explode('?', $href);

                        //Parse &amp to not break curl client. See issue #23
                        $query_string = str_replace('&amp;', '&', $query_string);
                    }
                    if ($this->ignore_arguments) {
                        $query_string = '';
                    }
                    if (strpos($href, '?') !== false) {
                        echo "EFEASDEFSED";
                    }

                    if ((substr($href, 0, 7) != "http://") && (substr($href, 0, 8) != "https://")) {
                        // Link does not call (potentially) external page
                        if (strpos($href, ":")) {
                            $this->logger("URL is an invalid protocol", 1);
                            return false;
                        }
                        if ($href == '/') {
                            $this->logger("$href is domain root", 2);
                            $href = $this->real_site;
                        } elseif (substr($href, 0, 1) == '/') {
                            $this->logger("$href is relative to root, convert to absolute", 2);
                            $href = $this->getDomainRoot($this->real_site) . substr($href, 1);
                        } else {
                            $this->logger("$href is relative, convert to absolute", 2);
                            $href = $this->getPath($parent_url) . $href;
                        }
                    }
                    $this->logger("Result: $href", 2);
                    if (!filter_var($href, FILTER_VALIDATE_URL)) {
                        $this->logger("URL is not valid. Rejecting.", 1);
                        return false;
                    }
                    if (substr($href, 0, strlen($this->real_site)) != $this->real_site) {
                        $this->logger("URL is not part of the target domain. Rejecting.", 1);
                        return false;
                    }
                    if ($this->isScanned($href . ($query_string ? '?' . $query_string : ''))) {
                        //logger("URL has already been scanned. Rejecting.", 1);
                        return false;
                    }
                    if (!$this->checkBlacklist($href)) {
                        $this->logger("URL is blacklisted. Rejecting.", 1);
                        return false;
                    }
                    return $this->flattenUrl($href . ($query_string ? '?' . $query_string : ''));
                }, $matches[2]);
                return $found;
            }
        }
        $this->logger("Found nothing", 2);
        return array();
    }

    private function getDomainRoot($href)
    {
        $url_parts = explode('/', $href);
        return $url_parts[0] . '//' . $url_parts[2] . '/';
    }

    //Try to match string against blacklist

    private function getPath($path)
    {
        $path_depth = explode("/", $path);
        $len = strlen($path_depth[count($path_depth) - 1]);
        return (substr($path, 0, strlen($path) - $len));
    }

    //Extract array of URLs from html document inside of `href`s

    private function flattenUrl($url)
    {
        $path = explode($this->real_site, $url)[1];
        return $this->real_site . $this->removeDotSeg($path);
    }

    /**
     * Remove dot segments from a URI path according to RFC3986 Section 5.2.4
     *
     * @param $path
     * @return string
     * @link http://www.ietf.org/rfc/rfc3986.txt
     */
    private function removeDotSeg($path)
    {
        if (strpos($path, '.') === false) {
            return $path;
        }

        $inputBuffer = $path;
        $outputStack = [];

        /**
         * 2.  While the input buffer is not empty, loop as follows:
         */
        while ($inputBuffer != '') {
            /**
             * A.  If the input buffer begins with a prefix of "../" or "./",
             *     then remove that prefix from the input buffer; otherwise,
             */
            if (strpos($inputBuffer, "./") === 0) {
                $inputBuffer = substr($inputBuffer, 2);
                continue;
            }
            if (strpos($inputBuffer, "../") === 0) {
                $inputBuffer = substr($inputBuffer, 3);
                continue;
            }

            /**
             * B.  if the input buffer begins with a prefix of "/./" or "/.",
             *     where "." is a complete path segment, then replace that
             *     prefix with "/" in the input buffer; otherwise,
             */
            if ($inputBuffer === "/.") {
                $outputStack[] = '/';
                break;
            }
            if (substr($inputBuffer, 0, 3) === "/./") {
                $inputBuffer = substr($inputBuffer, 2);
                continue;
            }

            /**
             * C.  if the input buffer begins with a prefix of "/../" or "/..",
             *     where ".." is a complete path segment, then replace that
             *     prefix with "/" in the input buffer and remove the last
             *     segment and its preceding "/" (if any) from the output
             *     buffer; otherwise,
             */
            if ($inputBuffer === "/..") {
                array_pop($outputStack);
                $outputStack[] = '/';
                break;
            }
            if (substr($inputBuffer, 0, 4) === "/../") {
                array_pop($outputStack);
                $inputBuffer = substr($inputBuffer, 3);
                continue;
            }

            /**
             * D.  if the input buffer consists only of "." or "..", then remove
             *     that from the input buffer; otherwise,
             */
            if ($inputBuffer === '.' || $inputBuffer === '..') {
                break;
            }

            /**
             * E.  move the first path segment in the input buffer to the end of
             *     the output buffer, including the initial "/" character (if
             *     any) and any subsequent characters up to, but not including,
             *     the next "/" character or the end of the input buffer.
             */
            if (($slashPos = stripos($inputBuffer, '/', 1)) === false) {
                $outputStack[] = $inputBuffer;
                break;
            } else {
                $outputStack[] = substr($inputBuffer, 0, $slashPos);
                $inputBuffer = substr($inputBuffer, $slashPos);
            }
        }

        return ltrim(implode($outputStack), "/");
    }

}