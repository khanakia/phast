<?php

namespace Kibo\Phast\ValueObjects;

use PHPUnit\Framework\TestCase;

class URLTest extends TestCase {

    public function testParsing() {
        $string = 'http://user:pass@test.com:8080/path/file.php?query#hash';
        $parsed = parse_url($string);
        $url = URL::fromString($string);

        foreach ($parsed as $key => $value) {
            $method = 'get' . ucfirst($key);
            $actual = $url->$method();
            $this->assertEquals($value, $actual, "Error for $key: expected $value, actual $actual");
        }
    }

    public function testCompiling() {
        $string = 'http://user:pass@test.com:8080/path/file.php?query#hash';
        $url = URL::fromString($string);
        $this->assertEquals($string, (string)$url);
    }

    public function testWithBase() {
        $base = URL::fromString('http://test.com/path/index.php?query#hash');

        $relative = URL::fromString('relative/path')->withBase($base);
        $this->assertEquals('http://test.com/path/relative/path', (string)$relative);

        $root = URL::fromString('/absolute/path')->withBase($base);
        $this->assertEquals('http://test.com/absolute/path', (string)$root);

        $absolute = URL::fromString('https://example.com')->withBase($base);
        $this->assertEquals('https://example.com', (string)$absolute);

        $query = URL::fromString('?q#f')->withBase($base);
        $this->assertEquals('http://test.com/path/index.php?q#f', (string)$query);

        $protocol = URL::fromString('//example.com')->withBase($base);
        $this->assertEquals('http://example.com', (string)$protocol);

        $baseInDir = URL::fromString('http://test.com/path?query#hash');
        $relativeToDir = URL::fromString('relative/path')->withBase($baseInDir);
        $this->assertEquals('http://test.com/path/relative/path', (string)$relativeToDir);

        $baseFileAsDir = URL::fromString('http://test.com/path/index.php/');
        $relativeToFile = URL::fromString('relative/path')->withBase($baseFileAsDir);
        $this->assertEquals('http://test.com/path/index.php/relative/path', (string)$relativeToFile);
    }

}
