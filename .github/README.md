# Upload

[![codecov](https://codecov.io/gh/GravityPDF/Upload/branch/main/graph/badge.svg)](https://codecov.io/gh/GravityPDF/Upload)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

This component simplifies file validation and uploading.

**Why was this library forked?**

* Original library was abandoned (untouched since 2018)
* Adjusted namespace from \Upload to \GravityPdf\Upload
* Bumped minimum PHP version to 7.3+
* Sanitized filename and extension, and add UTF-8 filename support
* Strict type checking
* Added FileSystem::getDirectory() and FileInfo::setNameWithExtension() methods
* PSR-12 Code Formatting
* Automated tools: PHPUnit, PHPStan, PHPCS, and PHP Syntax Checker

TODO: [PSR-7 and PSR-17 support (help wanted)](https://github.com/GravityPDF/Upload/issues/8)

## Installation

```
composer require gravitypdf/upload
```

## Usage

Assume a file is uploaded with this HTML form:

```html
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="foo" value=""/>
    <input type="submit" value="Upload File"/>
</form>
```

When the HTML form is submitted, the server-side PHP code can validate and upload the file like this:

```php
$storage = new \GravityPdf\Upload\Storage\FileSystem('/path/to/directory');
$file = new \GravityPdf\Upload\File('foo', $storage);

// Validate file upload
// MimeType List => http://www.iana.org/assignments/media-types/media-types.xhtml
$file->addValidations([
    // Ensure file is of type "image/png"
    new \GravityPdf\Upload\Validation\Mimetype('image/png'),
    new \GravityPdf\Upload\Validation\Extension('png'),

    //You can also add multi mimetype validation or extensions
    //new \Upload\Validation\Mimetype(array('image/png', 'image/gif'))
    //new \Upload\Validation\Extension(['png', 'gif']),

    // Ensure file is no larger than 5M (use "B", "K", M", or "G")
    new \GravityPdf\Upload\Validation\Size('5M'),
]);

// Access data about the file
// If upload accepts multiple files an array will be returned for each of these
$data = [
    'name' => $file->getNameWithExtension(),
    'extension' => $file->getExtension(),
    'mime' => $file->getMimetype(),
    'size' => $file->getSize(),
    'md5' => $file->getMd5(),
    'dimensions' => $file->getDimensions(),
];

// If you have an upload field that accepts multiple files you can access each file's info individually
$firstFileName = $file[0]->getNameWithExtension();
$secondFileName = $file[1]->getNameWithExtension();

// or loop over all files for this key
foreach($file as $upload) {
    $name = $upload->getNameWithExtension();
    $upload->setName(uniqid());
}

// Try to upload file(s)
try {
    // Success!
    $file->upload();
} catch (\Exception $e) {
    // Fail!
    $errors = $file->getErrors();
}
```

## Authors

* [Josh Lockhart](https://github.com/codeguy)
* [Gravity PDF](https://github.com/GravityPDF)

## License

MIT Public License
