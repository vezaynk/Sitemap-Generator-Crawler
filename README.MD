#Sitemap Generator

##Features
 - Actually crawls webpages like Google would
 - Generates seperate XML file which gets updated every time the script gets executed (Runnable via CRON)
 - Awesome for SEO
 - Crawls faster than online services
 - Adaptable
 - Also fetches last modified HTTP header (Thanks to @Z01DTech)
 
##Usage
Usage is pretty strait forward:
 - Configure the crawler by modifying the config section of the `sitemap.php` file
    - Select the file to which the sitemap will be saved
    - Select URL to crawl
    - Select accepted extensions ("/" is manditory for proper functionality)
    - Configure blacklists, accepts the use of wildcards (example: http://example.com/private/*) 
    - Select change frequency (always, daily, weekly, monthly, never, etc...)
    - Choose priority (It is all relative so it may as well be 1)
 - Generate sitemap
    - Either send a GET request to this script or simply point your browser
    - A sitemap will be generated and displayed
    - Submit to Google
 - For better results
    - Submit sitemap.xml to Google and not the script itself (Both still work)
    - Setup a CRON Job to send web requests to this script every so often, this will keep the sitemap.xml file up to date

Alternatively, you can run via SSH using CLI `php sitemap.php file=/home/user/public_html/sitemap.xml url=http://www.mywebsite.com/`