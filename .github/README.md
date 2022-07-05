# Upload

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/GravityPDF/Upload/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/GravityPDF/Upload/?branch=main)
[![Build Status](https://scrutinizer-ci.com/g/GravityPDF/Upload/badges/build.png?b=main)](https://scrutinizer-ci.com/g/GravityPDF/Upload/build-status/main)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/GravityPDF/Upload/badges/code-intelligence.svg?b=main)](https://scrutinizer-ci.com/code-intelligence)
[![codecov](https://codecov.io/gh/GravityPDF/Upload/branch/main/graph/badge.svg)](https://codecov.io/gh/GravityPDF/Upload)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

This component simplifies file validation and uploading.

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
<?php
$storage = new \Upload\Storage\FileSystem('/path/to/directory');
$file = new \Upload\File('foo', $storage);

// Optionally you can rename the file on upload
$new_filename = uniqid();
$file->setName($new_filename);

// Validate file upload
// MimeType List => http://www.iana.org/assignments/media-types/media-types.xhtml
$file->addValidations(array(
    // Ensure file is of type "image/png"
    new \Upload\Validation\Mimetype('image/png'),

    //You can also add multi mimetype validation
    //new \Upload\Validation\Mimetype(array('image/png', 'image/gif'))

    // Ensure file is no larger than 5M (use "B", "K", M", or "G")
    new \Upload\Validation\Size('5M')
));

// Access data about the file that has been uploaded
$data = array(
    'name'       => $file->getNameWithExtension(),
    'extension'  => $file->getExtension(),
    'mime'       => $file->getMimetype(),
    'size'       => $file->getSize(),
    'md5'        => $file->getMd5(),
    'dimensions' => $file->getDimensions()
);

// Try to upload file
try {
    // Success!
    $file->upload();
} catch (\Exception $e) {
    // Fail!
    $errors = $file->getErrors();
}
```

## How to Install

Install composer in your project:

```
curl -s https://getcomposer.org/installer | php
```

Require the package with composer:

```
php composer.phar require codeguy/upload
```

## Author

[Josh Lockhart](https://github.com/codeguy)

## License

MIT Public License
