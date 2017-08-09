# Sitemap Generator

## Features
 - Actually crawls webpages like Google would
 - Generates a seperate XML file which gets updated every time the script gets executed (Runnable via CRON)
 - Awesome for SEO
 - Crawls faster than online services
 - Verbose logging
 - Customizable
 - Author is active on Github, open an issue for support
 - Literally the best open-source sitemap generally written in PHP
 
## Usage
Usage is pretty strait forward:
 - Configure the crawler by modifying the config section of the `sitemap.php` file
    - Select the file to which the sitemap will be saved
    - Select URL to crawl
    - Configure blacklists, accepts the use of wildcards (example: http://example.com/private/* and *.jpg)
 - Generate sitemap
    - Either send a GET request to this script or simply point your browser
    - A sitemap will be generated and saved
    - Submit to Google
 - For better results
    - Setup a CRON Job to send web requests to this script every so often, this will keep the sitemap.xml file up to date

Alternatively, you can run via SSH using CLI `php sitemap.php file=/home/user/public_html/sitemap.xml url=http://www.mywebsite.com/`

# License

```
MIT License

Copyright (c) 2017 Slava Knyazev <slava@knyz.org>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```