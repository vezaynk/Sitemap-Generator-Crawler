<?php
/*
Sitemap Generator default values and config
Written by Santeri Kannisto <santeri.kannisto@2globalnomads.info>
Public domain, 2017
*/

// Default site to crawl
$site = "https://www.knyz.org/";

// Default sitemap filename
$file = "sitemap.xml";

// Depth of the crawl, 0 is unlimited
$max_depth = 0;

// Show changefreq
$enable_frequency = false;

// Show priority
$enable_priority = false;

// Default values for changefreq and priority
$freq = "daily";
$priority = "1";

// Add lastmod based on server response. Unreliable and disabled by default.
$enable_modified = false;

// Disable this for misconfigured, but tolerable SSL server.
$curl_validate_certificate = true;

// The pages will be excluded from crawl and sitemap.
// Use for exluding non-html files to increase performance and save bandwidth.
$blacklist = array(
    "*.jpg",
    "*/secrets/*",
    "https://www.knyz.org/supersecret"
);

// Enable this if your site do requires GET arguments to function
$ignore_arguments = false;

// Not yet implemented. See issue #19 for more information.
$index_img = false;

// Set the user agent for crawler
$crawler_user_agent = "Mozilla/5.0 (compatible; Sitemap Generator Crawler; +https://github.com/knyzorg/Sitemap-Generator-Crawler)";

// Header of the sitemap.xml
$xmlheader ='<?xml version="1.0" encoding="UTF-8"?>
<urlset
xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">';

// Optionally configure debug options
$debug = array(
    "add" => true,
    "reject" => false,
    "warn" => false
);
