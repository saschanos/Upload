# Upload 3.0.0

## Breaking Changes

* Revert removal of `\GravityPdf\Upload\File::__call()` magic method in 2.0.0, restoring API functionality to match v1
* Add `setNameWithExtension()` method to `\GravityPdf\Upload\FileInfoInterface`
* Change type signature `\GravityPdf\Upload\FileInfoInterface::setName(string $name)` 
* Change type signature `\GravityPdf\Upload\FileInfoInterface::setExtension(string $extension)`
* Remove `\GravityPdf\Upload\Validation\Dimensions` validation class

# Upload 2.0.0

## Breaking Changes
* PHP 7.3+ (previously PHP5.3+)
* Namespace Change: `\Upload` -> `\GravityPdf\Upload`
* Sanitize Filename and Extension: replace invalid/unsafe/reserved characters/words, trim, prevent < 255 byte filenames. [See the unit tests for the expected transformations](https://github.com/GravityPDF/Upload/blob/main/tests/Upload/FileInfoTest.php#L107-L152).
* Remove `\GravityPdf\Upload\File::__call()` magic method which would call the underlying `FileInfoInterface` object(s) and return the result as a string or array. Use `foreach($file as FileInfoInterface $fileInfo) { ... }` instead.
* Changed return value of `\GravityPdf\Upload\Storage\FileSystem::upload()` to the destination file path `string` (previously `void`)
* Strict type support added
* Replace `\Upload\Exception\UploadException` with `\GravityPdf\Upload\Excelption`
* `\GravityPdf\Upload\File` no longer extends `\SplFileInfo`
* Signature of `\GravityPdf\Upload\File::__construct()` changed to `__construct(string $key, \GravityPdf\Upload\StorageInterface $storage)`
* Signature of `\GravityPdf\Upload\File::addValidations()` changed to `addValidations(array $validations)`. Use new `addValidation(\GravityPdf\Upload\ValidationInterface $validation)` method to add single validation.
* `\GravityPdf\Upload\File::validate()` was replaced by `\GravityPdf\Upload\File::isValid()`
* Signature of `\GravityPdf\Upload\File::upload($newName = null)` changed to `upload()`. Use `\GravityPdf\Upload\File::setName(string $name)` or `\GravityPdf\Upload\File::setNameWithExtension(string $name)` before calling `upload()` to change the file name.
* `\Upload\Storage\Base` has been removed and `\GravityPdf\Upload\Storage\FileSystem` instead implements `\GravityPdf\Upload\StorageInterface`
* Signature of `\Upload\Storage\FileSystem::upload(\Upload\File $file, $newName = null)` changed to `\GravityPdf\Upload\Storage\FileSystem::upload(\GravityPdf\Upload\FileInfoInterface $fileInfo)`
* `\Upload\Validation\Base` has been removed and `\GravityPdf\Upload\Validation/*` classes instead implements `\GravityPdf\Upload\ValidationInterface`
* Classes that implement `\GravityPdf\Upload\ValidationInterface` must have `validate` method with the signature `validate(\Upload\FileInfoInterface $fileInfo)` and this method should throw the exception `\GravityPdf\Upload\Exception` if the file has failed validation

## New Features
* Support for UTF-8 Filenames
* Enhanced file name and extension sanitizing (**you still need to escape HTML on output or when inserting into database**)
* Added `\GravityPdf\Upload\FileInfo::setNameWithExtension(string $name)` (instead of using `setName()` and `setExtension()` separately)
* Added `\GravityPdf\Upload\Storage\FileSystem::getDirectory()` to return directory that has been set
* Added `beforeValidation(callable $callable)`, `afterValidation(callable $callable)`, `beforeUpload(callable $callable)`, and `afterUpload(callable $callable)` to `\GravityPdf\Upload\File`. The callable will receive a `\GravityPdf\Upload\FileInfoInterface` object as the first parameter.
* `\GravityPdf\Upload\File` implements `\ArrayAccess, \IteratorAggregate, \Countable` which allows you to treat `File` as an array and access the underlying `\GravityPdf\Upload\FileInfo` objects to get info about each individual file for the current `$key`. This is useful if your file upload HTML field accepts multiple files.
* Added `\GravityPdf\Upload\FileInfoInterface` and `\GravityPdf\Upload\FileInfo` objects to represent each individual image. The methods include:
  1. `getPathname()`
  2. `getName()`
  3. `setName(string $name)`
  4. `getExtension()`
  5. `setExtension(string $extension)`
  6. `getNameWithExtension()`
  7. `setNameWithExtension(string $filename)`
  8. `getMimetype()`
  9. `getSize()`
  10. `getMd5()`
  11. `getDimensions()`
  12. `isUploadedFile()`
*

## Bug Fixes
* Resolved PHP 8.1 warnings