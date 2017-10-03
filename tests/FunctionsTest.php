<?php

require_once(__DIR__.'/../sitemap.functions.php');

class FunctionsTest extends \PHPUnit_Framework_TestCase
{

    public function test_ends_with_validCase()
    {
        $this->assertTrue(ends_with('foobar', 'bar'));
    }

    public function test_ends_with_emptyString()
    {
        $this->assertTrue(ends_with('foobar', ''));
    }

    public function test_ends_with_invalidCase()
    {
        $this->assertFalse(ends_with('foobar', 'foo'));
        $this->assertFalse(ends_with('bar', 'foobar'));
    }

    public function test_check_blacklist_with_an_allowed_string()
    {
        $GLOBALS['blacklist'] = array('http://example.com/private/*');
        $this->assertTrue(check_blacklist('http://example.com/public/page.php'));
    }

    public function test_check_blacklist_with_a_forbidden_string()
    {
        $GLOBALS['blacklist'] = array('http://example.com/private/*');
        $this->assertFalse(check_blacklist('http://example.com/private/page.php'));
    }

    public function test_is_scanned()
    {
        $GLOBALS['scanned'] = array(
            'http://example.com/both',
            'http://example.com/both/',
            'http://example.com/without',
            'http://example.com/withslash/',
        );

        $this->assertTrue(is_scanned('http://example.com/both'));
        $this->assertTrue(is_scanned('http://example.com/both/'));

        $this->assertTrue(is_scanned('http://example.com/withslash'));
        $this->assertTrue(is_scanned('http://example.com/withslash/'));

        $this->assertTrue(is_scanned('http://example.com/without'));
        $this->assertTrue(is_scanned('http://example.com/without/'));
    }


}

