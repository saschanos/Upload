<?php

namespace Upload\Validation;

use Upload\Exception;
use Upload\FileInfo;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class DimensionsTest extends TestCase
{
    /**
     * @var string
     */
    protected $assetsDirectory;

    public function set_up()
    {
        parent::set_up();

        $this->assetsDirectory = dirname(__DIR__) . '/assets';
    }

    public function testWidthAndHeight()
    {
        $dimensions = new Dimensions(100, 100);
        $file = new FileInfo($this->assetsDirectory . '/foo.png', 'foo.png');

        try {
            $dimensions->validate($file);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail('Unexpected exception thrown');
        }
    }

    public function testWidthDoesntMatch()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('foo.png: Image width(100px) does not match required width(200px)');

        $dimensions = new Dimensions(200, 100);
        $file = new FileInfo($this->assetsDirectory . '/foo.png', 'foo.png');
        $dimensions->validate($file);
    }

    public function testHeightDoesntMatch()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('foo.png: Image height(100px) does not match required height(200px)');

        $dimensions = new Dimensions(100, 200);
        $file = new FileInfo($this->assetsDirectory . '/foo.png', 'foo.png');
        $dimensions->validate($file);
    }
}