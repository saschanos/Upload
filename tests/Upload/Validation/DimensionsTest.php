<?php

namespace GravityPdf\Upload\Validation;

use GravityPdf\Upload\Exception;
use GravityPdf\Upload\FileInfo;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class DimensionsTest extends TestCase
{
    /**
     * @var string
     */
    protected $assetsDirectory;

    /* phpcs:ignore */
    public function set_up()
    {
        parent::set_up();

        $this->assetsDirectory = dirname(__DIR__) . '/assets';
    }

    public function testWidthAndHeight(): void
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

    public function testWidthDoesntMatch(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('foo.png: Image width(100px) does not match required width(200px)');

        $dimensions = new Dimensions(200, 100);
        $file = new FileInfo($this->assetsDirectory . '/foo.png', 'foo.png');
        $dimensions->validate($file);
    }

    public function testHeightDoesntMatch(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('foo.png: Image height(100px) does not match required height(200px)');

        $dimensions = new Dimensions(100, 200);
        $file = new FileInfo($this->assetsDirectory . '/foo.png', 'foo.png');
        $dimensions->validate($file);
    }
}
