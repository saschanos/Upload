<?php

namespace Upload;

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class FileInfoTest extends TestCase
{
    /**
     * @var FileInfo
     */
    protected $fileWithExtension;

    /**
     * @var FileInfo
     */
    protected $fileWithoutExtension;

    /* phpcs:ignore */
    public function set_up()
    {
        parent::set_up();

        $this->fileWithExtension = new FileInfo(__DIR__ . '/assets/foo.txt', 'foo.txt');
        $this->fileWithoutExtension = new FileInfo(__DIR__ . '/assets/foo_wo_ext', 'foo_wo_ext');
    }

    public function testGetName(): void
    {
        $this->assertSame('foo', $this->fileWithExtension->getName());
        $this->assertSame('foo_wo_ext', $this->fileWithoutExtension->getName());
    }

    public function testSetName(): void
    {
        $this->fileWithExtension->setName('bar');
        $this->assertSame('bar', $this->fileWithExtension->getName());
    }

    public function testGetNameWithExtension(): void
    {
        $this->assertSame('foo.txt', $this->fileWithExtension->getNameWithExtension());
        $this->assertSame('foo_wo_ext', $this->fileWithoutExtension->getNameWithExtension());
    }

    public function testGetExtension(): void
    {
        $this->assertSame('txt', $this->fileWithExtension->getExtension());
        $this->assertSame('', $this->fileWithoutExtension->getExtension());
    }

    public function testSetExtension(): void
    {
        $this->fileWithExtension->setExtension('csv');
        $this->assertSame('csv', $this->fileWithExtension->getExtension());
    }

    public function testGetMimetype(): void
    {
        $this->assertSame('text/plain', $this->fileWithExtension->getMimetype());
    }

    public function testGetMd5(): void
    {
        $hash = md5_file(__DIR__ . '/assets/foo.txt');

        $this->assertSame($hash, $this->fileWithExtension->getMd5());
    }

    public function testGetHash(): void
    {
        $sha1Hash = hash_file('sha1', __DIR__ . '/assets/foo.txt');
        $this->assertSame($sha1Hash, $this->fileWithExtension->getHash('sha1'));

        $md5Hash = hash_file('md5', __DIR__ . '/assets/foo.txt');

        $this->assertSame($md5Hash, $this->fileWithExtension->getHash('md5'));
        $this->assertSame($md5Hash, $this->fileWithExtension->getHash());
    }
}
