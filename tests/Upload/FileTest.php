<?php

namespace Upload;

use InvalidArgumentException;
use Upload\Storage\FileSystem;
use Upload\Validation\Mimetype;
use Upload\Validation\Size;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class FileTest extends TestCase
{
    /**
     * @var string
     */
    protected $assetsDirectory;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FileSystem
     */
    protected $storage;

    public function set_up()
    {
        parent::set_up();

        // Set FileInfo factory
        FileInfo::setFactory(function ($tmpName, $name) {
            $fileInfo = $this->getMockBuilder(FileInfo::class)
                ->setConstructorArgs([$tmpName, $name])
                ->onlyMethods(['isUploadedFile'])
                ->getMock();

            $fileInfo
                ->method('isUploadedFile')
                ->willReturn(true);

            return $fileInfo;
        });

        // Path to test assets
        $this->assetsDirectory = __DIR__ . '/assets';

        // Mock storage
        $this->storage = $this->getMockBuilder(FileSystem::class)
            ->setConstructorArgs([$this->assetsDirectory])
            ->onlyMethods(['upload'])
            ->getMock();

        $this->storage
            ->method('upload')
            ->willReturn(true);

        // Prepare uploaded files
        $_FILES['multiple'] = [
            'name' => [
                'foo.txt',
                'bar.txt',
            ],
            'tmp_name' => [
                $this->assetsDirectory . '/foo.txt',
                $this->assetsDirectory . '/bar.txt',
            ],
            'error' => [
                UPLOAD_ERR_OK,
                UPLOAD_ERR_OK,
            ],
        ];
        $_FILES['single'] = [
            'name' => 'single.txt',
            'tmp_name' => $this->assetsDirectory . '/single.txt',
            'error' => UPLOAD_ERR_OK,
        ];
        $_FILES['bad'] = [
            'name' => 'single.txt',
            'tmp_name' => $this->assetsDirectory . '/single.txt',
            'error' => UPLOAD_ERR_INI_SIZE,
        ];
    }

    /********************************************************************************
     * Construction tests
     *******************************************************************************/

    public function testConstructionWithMultipleFiles()
    {
        $file = new File('multiple', $this->storage);
        $this->assertCount(2, $file);
        $this->assertEquals('foo.txt', $file[0]->getNameWithExtension());
        $this->assertEquals('bar.txt', $file[1]->getNameWithExtension());
    }

    public function testConstructionWithSingleFile()
    {
        $file = new File('single', $this->storage);
        $this->assertCount(1, $file);
        $this->assertEquals('single.txt', $file[0]->getNameWithExtension());
    }

    public function testConstructionWithInvalidKey()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot find uploaded file(s) identified by key: bar');

        $file = new File('bar', $this->storage);
    }

    /********************************************************************************
     * Callback tests
     *******************************************************************************/

    /**
     * Test callbacks
     *
     * This test will make sure callbacks are called for each FileInfoInterface
     * object in the correct order.
     */
    public function testCallbacks()
    {
        $this->expectOutputString(
            "BeforeValidate: foo\nAfterValidate: foo\nBeforeValidate: bar\nAfterValidate: bar\nBeforeUpload: foo\nAfterUpload: foo\nBeforeUpload: bar\nAfterUpload: bar\n"
        );

        $callbackBeforeValidate = function (FileInfoInterface $fileInfo) {
            echo 'BeforeValidate: ' . $fileInfo->getName(), PHP_EOL;
        };

        $callbackAfterValidate = function (FileInfoInterface $fileInfo) {
            echo 'AfterValidate: ' . $fileInfo->getName(), PHP_EOL;
        };

        $callbackBeforeUpload = function (FileInfoInterface $fileInfo) {
            echo 'BeforeUpload: ' . $fileInfo->getName(), PHP_EOL;
        };

        $callbackAfterUpload = function (FileInfoInterface $fileInfo) {
            echo 'AfterUpload: ' . $fileInfo->getName(), PHP_EOL;
        };

        $file = new File('multiple', $this->storage);
        $file->beforeValidate($callbackBeforeValidate);
        $file->afterValidate($callbackAfterValidate);
        $file->beforeUpload($callbackBeforeUpload);
        $file->afterUpload($callbackAfterUpload);
        $file->upload();
    }

    /********************************************************************************
     * Validation tests
     *******************************************************************************/

    public function testAddSingleValidation()
    {
        $file = new File('single', $this->storage);
        $file->addValidation(
            new Mimetype([
                'text/plain',
            ])
        );

        $this->assertCount(1, $file->getValidations());
    }

    public function testAddMultipleValidations()
    {
        $file = new File('single', $this->storage);
        $file->addValidations([
            new Mimetype([
                'text/plain',
            ]),
            new Size(50) // minimum bytesize
        ]);

        $this->assertCount(2, $file->getValidations());
    }

    public function testIsValidIfNoValidations()
    {
        $file = new File('single', $this->storage);
        $this->assertTrue($file->isValid());
    }

    public function testIsValidWithPassingValidations()
    {
        $file = new File('single', $this->storage);
        $file->addValidation(
            new Mimetype([
                'text/plain',
            ])
        );
        $this->assertTrue($file->isValid());
    }

    public function testIsInvalidWithFailingValidations()
    {
        $file = new File('single', $this->storage);
        $file->addValidation(
            new Mimetype([
                'text/csv',
            ])
        );
        $this->assertFalse($file->isValid());
    }

    public function testIsInvalidIfHttpErrorCode()
    {
        $file = new File('bad', $this->storage);
        $this->assertFalse($file->isValid());
    }

    public function testIsInvalidIfNotUploadedFile()
    {
        FileInfo::setFactory(function ($tmpName, $name) {
            $fileInfo = $this->getMockBuilder(FileInfo::class)
                ->setConstructorArgs([$tmpName, $name])
                ->onlyMethods(['isUploadedFile'])
                ->getMock();

            $fileInfo
                ->method('isUploadedFile')
                ->willReturn(false);

            return $fileInfo;
        });

        $file = new File('single', $this->storage);
        $this->assertFalse($file->isValid());
    }

    /********************************************************************************
     * Error message tests
     *******************************************************************************/

    public function testGetErrors()
    {
        $file = new File('single', $this->storage);
        $file->addValidation(
            new Mimetype([
                'text/csv',
            ])
        );
        $file->isValid();
        $this->assertCount(1, $file->getErrors());
    }

    /********************************************************************************
     * Upload tests
     *******************************************************************************/

    public function testWillUploadIfValid()
    {
        $file = new File('single', $this->storage);
        $this->assertTrue($file->isValid());
        $this->assertTrue($file->upload());
    }

    public function testWillNotUploadIfInvalid()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File validation failed');

        $file = new File('bad', $this->storage);
        $this->assertFalse($file->isValid());
        $file->upload();
    }

    /********************************************************************************
     * Helper tests
     *******************************************************************************/

    public function testParsesHumanFriendlyFileSizes()
    {
        $this->assertEquals(100, File::humanReadableToBytes('100'));
        $this->assertEquals(102400, File::humanReadableToBytes('100K'));
        $this->assertEquals(104857600, File::humanReadableToBytes('100M'));
        $this->assertEquals(107374182400, File::humanReadableToBytes('100G'));
        $this->assertEquals(100, File::humanReadableToBytes('100F')); // <-- Unrecognized. Assume bytes.
    }
}
