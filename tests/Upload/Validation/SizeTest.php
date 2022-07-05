<?php

namespace Upload\Validation;

use Upload\Exception;
use Upload\FileInfo;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class SizeTest extends TestCase
{
    /**
     * @var string
     */
    private $assetsDirectory;

    public function set_up()
    {
        parent::set_up();

        $this->assetsDirectory = dirname(__DIR__) . '/assets';
    }

    public function testValidFileSize()
    {
        $file = new FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
        $validation = new Size(500);

        try {
            $validation->validate($file);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail('Unexpected exception thrown');
        }
    }

    public function testValidFileSizeWithHumanReadableArgument()
    {
        $file = new FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
        $validation = new Size('500B');

        try {
            $validation->validate($file);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail('Unexpected exception thrown');
        }
    }

    public function testInvalidFileSize()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File size is too large. Must be less than: 400');

        $file = new FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
        $validation = new Size(400);
        $validation->validate($file);
    }

    public function testInvalidFileSizeWithHumanReadableArgument()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File size is too large. Must be less than: 400');

        $file = new FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
        $validation = new Size('400B');
        $validation->validate($file);
    }
}
