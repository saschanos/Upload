<?php

namespace Upload\Validation;

use Upload\Exception;
use Upload\FileInfo;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class MimetypeTest extends TestCase
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

    public function testValidMimetype()
    {
        $file = new FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
        $validation = new Mimetype(['text/plain']);

        try {
            $validation->validate($file);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail('Unexpected exception thrown');
        }
    }

    public function testInvalidMimetype()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid mimetype. Must be one of: image/png');

        $file = new FileInfo($this->assetsDirectory . '/foo.txt', 'foo.txt');
        $validation = new Mimetype(['image/png']);
        $validation->validate($file);
    }
}
