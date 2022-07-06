<?php

namespace GravityPdf\Upload;

use InvalidArgumentException;
use GravityPdf\Upload\Storage\FileSystem;
use GravityPdf\Upload\Validation\Mimetype;
use GravityPdf\Upload\Validation\Size;
use Yoast\PHPUnitPolyfills\TestCases\TestCase;

class FileTest extends TestCase
{
    /**
     * @var string
     */
    protected $assetsDirectory;

    /**
     * @var StorageInterface
     */
    protected $storage;

    /* phpcs:ignore */
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

    public function testConstructionWithMultipleFiles(): void
    {
        $file = new File('multiple', $this->storage);
        $this->assertCount(2, $file);
        $this->assertEquals('foo.txt', $file[0]->getNameWithExtension()); /* @phpstan-ignore-line */
        $this->assertEquals('bar.txt', $file[1]->getNameWithExtension()); /* @phpstan-ignore-line */
    }

    public function testConstructionWithSingleFile(): void
    {
        $file = new File('single', $this->storage);
        $this->assertCount(1, $file);
        $this->assertEquals('single.txt', $file[0]->getNameWithExtension()); /* @phpstan-ignore-line */
    }

    public function testConstructionWithInvalidKey(): void
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
    public function testCallbacks(): void
    {
        $this->expectOutputString(
            "BeforeValidate: foo\n" .
            "AfterValidate: foo\n" .
            "BeforeValidate: bar\n" .
            "AfterValidate: bar\n" .
            "BeforeUpload: foo\n" .
            "AfterUpload: foo\n" .
            "BeforeUpload: bar\n" .
            "AfterUpload: bar\n"
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

    public function testAddSingleValidation(): void
    {
        $file = new File('single', $this->storage);
        $file->addValidation(
            new Mimetype([
                'text/plain',
            ])
        );

        $this->assertCount(1, $file->getValidations());
    }

    public function testAddMultipleValidations(): void
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

    public function testIsValidIfNoValidations(): void
    {
        $file = new File('single', $this->storage);
        $this->assertTrue($file->isValid());
    }

    public function testIsValidWithPassingValidations(): void
    {
        $file = new File('single', $this->storage);
        $file->addValidation(
            new Mimetype([
                'text/plain',
            ])
        );
        $this->assertTrue($file->isValid());
    }

    public function testIsInvalidWithFailingValidations(): void
    {
        $file = new File('single', $this->storage);
        $file->addValidation(
            new Mimetype([
                'text/csv',
            ])
        );
        $this->assertFalse($file->isValid());
    }

    public function testIsInvalidIfHttpErrorCode(): void
    {
        $file = new File('bad', $this->storage);
        $this->assertFalse($file->isValid());
    }

    public function testIsInvalidIfNotUploadedFile(): void
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

    public function testGetErrors(): void
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

    public function testWillUploadIfValid(): void
    {
        $file = new File('single', $this->storage);
        $this->assertTrue($file->isValid());

        try {
            $file->upload();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->fail('Unexpected exception thrown');
        }
    }

    public function testWillNotUploadIfInvalid(): void
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

    public function testParsesHumanFriendlyFileSizes(): void
    {
        $this->assertEquals(100, File::humanReadableToBytes('100'));
        $this->assertEquals(102400, File::humanReadableToBytes('100K'));
        $this->assertEquals(104857600, File::humanReadableToBytes('100M'));
        $this->assertEquals(107374182400, File::humanReadableToBytes('100G'));
        $this->assertEquals(100, File::humanReadableToBytes('100F')); // <-- Unrecognized. Assume bytes.
    }
}
