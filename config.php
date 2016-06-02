<?
//This is only the configuration file, the actual script is generate.php
/*
Sitemap Generator by Slava Knyazev

Website: http://knyz.org/
I also live on GitHub: https://github.com/viruzx
Contact me: Slava@KNYZ.org
*/
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
?>
