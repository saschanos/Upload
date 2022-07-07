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

## New Features
* Support for UTF-8 Filenames
* Enhanced file name and extension sanitizing (**you still need to escape HTML on output or when inserting into database**)
* Added `\GravityPdf\Upload\FileInfo::setNameWithExtension(string $name)` (instead of using `setName()` and `setExtension()` separately)
* Added `\GravityPdf\Upload\Storage\FileSystem::getDirectory()` to return directory that has been set

## Bug Fixes
* Resolved PHP 8.1 warnings