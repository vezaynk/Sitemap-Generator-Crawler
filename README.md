# Sitemap Generator

## Features
 - Actually crawls webpages like Google would
 - Generates a seperate XML file which gets updated every time the script gets executed (Runnable via CRON)
 - Awesome for SEO
 - Crawls faster than online services
 - Verbose logging
 - Completely usable through CLI
 - Color support in CLI mode
 - Customizable
 - Author is active on Github, open an issue for support
 - Literally the best open-source sitemap script written in PHP
 - Non-restrictive licensing
 
## Usage
Usage is pretty strait forward:
 - Configure the crawler by modifying the config section of the `sitemap.php` file
    - Select the file to which the sitemap will be saved
    - Select URL to crawl
    - Configure blacklists, accepts the use of wildcards (example: http://example.com/private/* and *.jpg)
 - Generate sitemap
    - Either send a GET request to this script or use it from the CLI as seen below
    - A sitemap will be generated and saved
    - Submit to Google
 - For better results
    - Setup a CRON Job to execute the php script

# CLI Usage

Sometimes you need to run the script for a large number of domains (If you are a webhost for example). This sitemap generator allows you to override any variable on-the-fly in CLI.

## Basic usage

Scan `http://www.mywebsite.com/` and output the sitemap to `/home/user/public_html/sitemap.xml`:

`php sitemap.php file=/home/user/public_html/sitemap.xml site=http://www.mywebsite.com/`

## Advanced usage

While the above is the most common use-case, sometimes you need to modify other things such as `$debug` or `$blacklist`. I will do a bit of explaining about how shells work so you don't mess up.

Lets start with the blacklist which is a one-dimensional array. This is how you would pass an array as a `GET` request.

~~`php sitemap.php blacklist[]="foo"&blacklist[]="bar"`~~

Shells are different however as `[]` is parsed as a shell expansion and `&` as a fork-to-background. You want neither of those things. As such, you want to escape both of them resulting in the following:

`php sitemap.php blacklist\[]="foo"\&blacklist\[]="bar"`

Next, let's tackle the `$debug` variable. All the same concepts apply but the syntax is slightly different:

`php sitemap.php debug\["add"]=true\&debug\["warn"]=false\&debug\["reject"]=true`

**Important note**: Overriding an array does exactly what it means. Previously defined elements are destroyed.

## Running Tests

# Acknowledgements

This section is devoted as a *thank you* for everybody who helped create this script.

[Richard Leishman](https://github.com/mrl22) and [Web Forward](http://www.webfwd.co.uk/) for the regex at the heart of the script.  
[Anatoli Nicolae](https://github.com/anatolinicolae) for fixing a bug in the regex  
[Mario Bouchard](https://github.com/mbouchard) for fixing #32 and #35 with his first pull request  
[Santeri Kannisto](https://github.com/2globalnomads) from [2 Global Nomads](https://www.2globalnomads.info/) for a number of features and many, many bug reports


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