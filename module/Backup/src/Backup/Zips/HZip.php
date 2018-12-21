<?php
namespace Backup\Zips;

class HZip
{
    /**
     * Add files and sub-directories in a folder to zip file.
     * @param string $folder
     * @param ZipArchive $zipFile
     * @param int $exclusiveLength Number of text to be exclusived from the file path.
     */
    protected static $log;
    
    private static function folderToZip($folder, &$zipFile, $exclusiveLength) {
        self::$log = "<br/>--上传文件备份--<br/>";
        $handle = opendir($folder);
        while (false !== $f = readdir($handle)) {
            if ($f != '.' && $f != '..') {
                $filePath = "$folder/$f";
                // Remove prefix from file path before add to zip.
                $localPath = substr($filePath, $exclusiveLength);
                self::$log .= $localPath . "<br/>";
                if (is_file($filePath)) {
                    $zipFile->addFile($filePath, $localPath);
                } elseif (is_dir($filePath)) {
                    // Add sub-directory.
                    $zipFile->addEmptyDir($localPath);
                    self::folderToZip($filePath, $zipFile, $exclusiveLength);
                }
            }
        }
        closedir($handle);
    }

    /**
     * Zip a folder (include itself).
     * Usage:
     *  HZip::zipDir('/path/to/sourceDir', '/path/to/out.zip');
      *
     * @param string $sourcePath Path of directory to be zip.
     * @param string $outZipPath Path of output zip file.
     */
    public static function zipDir($sourcePath, $outZipPath)
    {
        $pathInfo = pathInfo($sourcePath);
        $parentPath = $pathInfo['dirname'];
        $dirName = $pathInfo['basename'];

        $z = new \ZipArchive();
        $z->open($outZipPath, \ZIPARCHIVE::CREATE);
        $z->addEmptyDir($dirName);
        self::folderToZip($sourcePath, $z, strlen("$parentPath/"));
        $z->close();
        $result = self::$log;
        $result .= "<br/>^_^&nbsp;&nbsp;上传文件备份完成！";
        return $result;
    }
    
    public static function unzip($zipFilename, $outZipPath)
    {
        $archiv = new \ZipArchive();
        $archiv->open($zipFilename,\ZipArchive::CREATE);
        $archiv->extractTo($outZipPath);
        $archiv->close();
    }
}