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

    /**
     * @dataProvider providerSetNameSanitizing
     */
    public function testSetNameSanitizing(string $expectedName, string $expectedExtension, string $filename): void
    {
        $file = new FileInfo(__DIR__ . '/assets/foo.txt', $filename);
        $this->assertSame($expectedName, $file->getName());
        $this->assertSame($expectedExtension, $file->getExtension());
    }

    /**
     * @dataProvider providerSetNameSanitizing
     */
    public function testSetNameWithExtension(string $expectedName, string $expectedExtension, string $filename): void
    {
        $this->fileWithExtension->setNameWithExtension($filename);
        $this->assertSame($expectedName, $this->fileWithExtension->getName());
        $this->assertSame($expectedExtension, $this->fileWithExtension->getExtension());
    }

    /**
     * @return array<int, array<int,string>>
     */
    public function providerSetNameSanitizing(): array
    {
        return [
            0 => ['àáâãäåæçèéêëìíîïñòóôõöøùúûüýÿ', 'png', 'àáâãäåæçèéêëìíîïñòóôõöøùúûüýÿ.png'],
            1 => ['საბეჭდი_მანქანა', 'txt', 'საბეჭდი_მანქანა.txt'],
            2 => ['unencoded space', 'php20', 'unencoded space.php%20'],
            3 => ['encoded-space', 'php0a', 'encoded-space.php%0a'],
            4 => ['plus-space', 'php0d0a', 'plus+space.php%0d%0a'],
            5 => ['multi-space', 'php', 'multi %20 +space.php/'],
            6 => ['file name-php', '', 'file   name.php.\\'],
            7 => ['file_name', '', 'file___name . '],
            8 => ['file-name-php', 'png', 'file - -name.php.png'],
            9 => ['file - name', 'png', 'file - name.png'],
            10 => ['file-name', 'exe', 'file--name.exe'],
            11 => ['file- - name', 'php7', 'file-- - name.php7'],
            12 => ['file - _ - name', 'php5', 'file - _ - name.Php5'],
            13 => ['file- name-php', '', 'file-- name.php...'],
            14 => ['file', 'name', 'file-- . --.-.--name'],
            15 => ['file -name', '', 'file ..name ..'],
            16 => ['file name', 'txt', ' . file name . .txt'],
            17 => ['file', 'name', ' file . name '],
            18 => ['file', 'name', '_file . name_'],
            19 => ['a-t-t', '', 'a----t----t'],
            20 => ['a-t-t-php-x00', 'png', 'a----t----t----.php\x00.png'],
            21 => ['a t', '', 'a          t'],
            22 => ['a -t-php-0a', 'png', "a    \n\n\nt.php%0a.png"],
            23 => ['a - 22b', '', 'a % 22b'],
            24 => ['file-nam', 'e', 'file...nam . e'],
            25 => ['unnamed-file', '', ''],
            26 => ['unnamed-file', '', '_'],
            27 => ['sample file', '', ' ../sample file'],
            28 => ['sample file', 'txt', ' ../sample file.txt'],
            29 => ['sample file', '', ' ./sample file'],
            30 => ['unnamed-file', 'samplefile', ' . sample file'],
            31 => ['Sample File-s', '', '"Sample File\'s'],
            32 => ['S-am-ple-20 - -File', 'txt', 'S@{am}^(ple)!$20 %<>:" \|?*[File]#.txt'],
            33 => [str_repeat('A', 251), 'txt', str_repeat('A', 300) . '.txt'],
            34 => ['unnamed-file', '', '.'],
            35 => ['unnamed-file', '', '..'],
            36 => ['unnamed-file', '', '...'],
            37 => ['here', 'txt', '/file/name/here.txt'],
            38 => ['unnamed-file', 'txt', 'con.txt'],
            39 => ['text', '', 'text.con'],
            40 => ['file-con', 'txt', 'file-con.txt'],
            41 => ['lol', 'png', '../../../tmp/lol.png'],
            42 => ['sleep-10', 'jpg', 'sleep(10)-- -.jpg'],
            43 => ['svg onload-alert-document', 'domain', '<svg onload=alert(document.domain)>'],
            44 => ['sleep 10', '', '; sleep 10;'],
            45 => ['This-Is-My-Sample', 'txt', 'This\\Is\\My\\Sample.txt'],
        ];
    }
}
