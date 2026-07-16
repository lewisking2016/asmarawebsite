<?php
/**
 * Image Optimizer Utility
 * Automatically compresses and resizes images to keep file sizes small while maintaining quality.
 */

class ImageOptimizer {

    /**
     * Optimize an image file in-place.
     * 
     * @param string $filePath Absolute path to the image file.
     * @param int $maxDimension Maximum width or height of the image.
     * @param int $quality Compression quality (0-100) for JPG/WEBP.
     * @return bool True if optimized successfully, false otherwise.
     */
    public static function optimize($filePath, $maxDimension = 1600, $quality = 80) {
        if (!file_exists($filePath)) {
            return false;
        }

        // Check if GD library is enabled
        if (!extension_loaded('gd')) {
            error_log("GD extension is not loaded. Cannot optimize image: " . $filePath);
            return false;
        }

        // Get image info
        $imageInfo = @getimagesize($filePath);
        if (!$imageInfo) {
            return false;
        }

        $width = $imageInfo[0];
        $height = $imageInfo[1];
        $mime = $imageInfo['mime'];

        // 1. Load image based on MIME type
        $srcImage = null;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $srcImage = @imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $srcImage = @imagecreatefrompng($filePath);
                break;
            case 'image/webp':
                $srcImage = @imagecreatefromwebp($filePath);
                break;
            case 'image/gif':
                $srcImage = @imagecreatefromgif($filePath);
                break;
            default:
                // Unsupported format
                return false;
        }

        if (!$srcImage) {
            return false;
        }

        // 2. Handle EXIF orientation for JPEGs (so images don't end up rotated sideways)
        if (($mime === 'image/jpeg' || $mime === 'image/jpg') && function_exists('exif_read_data')) {
            $exif = @exif_read_data($filePath);
            if ($exif && isset($exif['Orientation'])) {
                switch ($exif['Orientation']) {
                    case 3:
                        $srcImage = imagerotate($srcImage, 180, 0);
                        break;
                    case 6:
                        $srcImage = imagerotate($srcImage, -90, 0);
                        // Swap dimensions
                        $temp = $width;
                        $width = $height;
                        $height = $temp;
                        break;
                    case 8:
                        $srcImage = imagerotate($srcImage, 90, 0);
                        // Swap dimensions
                        $temp = $width;
                        $width = $height;
                        $height = $temp;
                        break;
                }
            }
        }

        // 3. Calculate new dimensions if image exceeds maxDimension
        $newWidth = $width;
        $newHeight = $height;

        if ($width > $maxDimension || $height > $maxDimension) {
            if ($width > $height) {
                $newWidth = $maxDimension;
                $newHeight = round(($height / $width) * $maxDimension);
            } else {
                $newHeight = $maxDimension;
                $newWidth = round(($width / $height) * $maxDimension);
            }
        }

        // 4. Create new image canvas
        $dstImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG and WEBP
        if ($mime === 'image/png' || $mime === 'image/webp') {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
            imagefilledrectangle($dstImage, 0, 0, $newWidth, $newHeight, $transparent);
        }

        // Resize / copy
        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // 5. Save image back to the same path
        $success = false;
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $success = imagejpeg($dstImage, $filePath, $quality);
                break;
            case 'image/png':
                // PNG quality is 0 (no compression) to 9 (maximum compression)
                $pngQuality = round((100 - $quality) / 10);
                if ($pngQuality > 9) $pngQuality = 9;
                if ($pngQuality < 0) $pngQuality = 0;
                $success = imagepng($dstImage, $filePath, $pngQuality);
                break;
            case 'image/webp':
                $success = imagewebp($dstImage, $filePath, $quality);
                break;
            case 'image/gif':
                $success = imagegif($dstImage, $filePath);
                break;
        }

        // Free memory
        imagedestroy($srcImage);
        imagedestroy($dstImage);

        return $success;
    }

    /**
     * Optimize all images in a directory.
     * 
     * @param string $dirPath Path to the directory.
     * @return array List of optimized files.
     */
    public static function optimizeDirectory($dirPath) {
        $optimizedFiles = [];
        if (!is_dir($dirPath)) {
            return $optimizedFiles;
        }

        $files = scandir($dirPath);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $dirPath . '/' . $file;
            if (is_file($filePath)) {
                // Check if it's an image file by extension
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                    // Only optimize if file size is > 100KB to avoid unnecessary work
                    if (filesize($filePath) > 100 * 1024) {
                        if (self::optimize($filePath)) {
                            $optimizedFiles[] = $filePath;
                        }
                    }
                }
            }
        }

        return $optimizedFiles;
    }
}
