<?php

namespace Upload\Storage;

use InvalidArgumentException;
use Upload\Exception;
use Upload\FileInfo;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class FileSystemTest extends TestCase
{
    /**
     * @var string
     */
    protected $assetsDirectory;

    /* phpcs:ignore */
    public function set_up()
    {
        parent::set_up();

        // Path to test assets
        $this->assetsDirectory = dirname(__DIR__) . '/assets';

        // Reset $_FILES superglobal
        $_FILES['foo'] = [
            'name' => 'foo.txt',
            'tmp_name' => $this->assetsDirectory . '/foo.txt',
            'error' => 0,
        ];
    }

    public function testInstantiationWithValidDirectory(): void
    {
        try {
            $storage = $this->getMockBuilder(FileSystem::class)
                ->setConstructorArgs([$this->assetsDirectory])
                ->getMock();

            $this->assertTrue(true);
        } catch (InvalidArgumentException $e) {
            $this->fail('Unexpected argument thrown during instantiation with valid directory');
        }
    }

    public function testInstantiationWithInvalidDirectory(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Directory does not exist');

        $storage = $this->getMockBuilder(FileSystem::class)
            ->setConstructorArgs(['/foo'])
            ->getMock();
    }

    /**
     * Test won't overwrite existing file
     */
    public function testWillNotOverwriteFile(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File already exists');

        $storage = new FileSystem($this->assetsDirectory, false);
        $storage->upload(new FileInfo('foo.txt', dirname(__DIR__) . '/assets/foo.txt'));
    }

    /**
     * Test will overwrite existing file
     */
    public function testWillOverwriteFile(): void
    {
        $storage = $this->getMockBuilder(FileSystem::class)
            ->setConstructorArgs([$this->assetsDirectory, true])
            ->onlyMethods(['moveUploadedFile'])
            ->getMock();

        $storage
            ->method('moveUploadedFile')
            ->willReturn(true);

        $fileInfo = $this->getMockBuilder(FileInfo::class)
            ->setConstructorArgs([dirname(__DIR__) . '/assets/foo.txt', 'foo.txt'])
            ->onlyMethods(['isUploadedFile'])
            ->getMock();

        $fileInfo
            ->method('isUploadedFile')
            ->willReturn(true);

        try {
            $storage->upload($fileInfo);
            $this->assertTrue(true);
        } catch (Exception $e) {
            $this->fail('Unexpected exception thrown');
        }
    }

    public function testReturnsUploadedFileName(): void
    {
        $storage = $this->getMockBuilder(FileSystem::class)
            ->setConstructorArgs([$this->assetsDirectory, true])
            ->onlyMethods(['moveUploadedFile'])
            ->getMock();

        $storage
            ->method('moveUploadedFile')
            ->willReturn(true);

        $fileName = dirname(__DIR__) . '/assets/foo.txt';

        $fileInfo = new FileInfo(
            $fileName
        );

        $this->assertSame($fileName, $storage->upload($fileInfo));
        $this->assertSame($this->assetsDirectory, $storage->getDirectory());
    }
}
