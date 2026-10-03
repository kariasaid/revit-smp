<?php

namespace App\Libraries;

class ProgressPhotoCompressor
{
    private const MAX_OUTPUT_BYTES = 5 * 1024 * 1024;
    private const MAX_INPUT_PIXELS = 16_000_000;

    public function compress(string $sourcePath): ?string
    {
        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo || ($imageInfo[0] * $imageInfo[1]) > self::MAX_INPUT_PIXELS) {
            return null;
        }

        $source = @imagecreatefromstring((string) file_get_contents($sourcePath));
        if (!$source) {
            return null;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        for ($maxDimension = 2400; $maxDimension >= 960; $maxDimension = (int) ($maxDimension * 0.8)) {
            $scale = min(1, $maxDimension / max($sourceWidth, $sourceHeight));
            $width = max(1, (int) round($sourceWidth * $scale));
            $height = max(1, (int) round($sourceHeight * $scale));
            $image = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $white);
            imagecopyresampled($image, $source, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

            foreach ([85, 75, 65, 55, 45, 35] as $quality) {
                ob_start();
                $encoded = imagejpeg($image, null, $quality);
                $compressed = ob_get_clean();
                if ($encoded && strlen($compressed) <= self::MAX_OUTPUT_BYTES) {
                    imagedestroy($image);
                    imagedestroy($source);
                    return $compressed;
                }
            }

            imagedestroy($image);
        }

        imagedestroy($source);
        return null;
    }
}
