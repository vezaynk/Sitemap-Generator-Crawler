<?

header("Content-type: text/xml; charset=utf-8");
require("config.php");
require("basic.php");
require("scan.php");
echo file_get_contents("sitemap.xml");
?>