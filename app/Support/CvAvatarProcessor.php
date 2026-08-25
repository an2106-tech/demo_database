<?php

namespace App\Support;

class CvAvatarProcessor
{
    /**
     * Process and crop avatar image for CV templates (1:1 square or 3:4 portrait)
     * Automatically trims letterbox black bars and centers/zooms on the person.
     */
    public static function process(?string $avatarPath, string $targetRatio = '1:1'): ?string
    {
        if (! $avatarPath || ! file_exists($avatarPath)) {
            return null;
        }

        try {
            $imageInfo = @getimagesize($avatarPath);
            if (! $imageInfo || ! function_exists('imagecreatetruecolor')) {
                return base64_encode(file_get_contents($avatarPath));
            }

            $srcW = $imageInfo[0];
            $srcH = $imageInfo[1];
            $srcMime = $imageInfo['mime'];

            $srcImg = match ($srcMime) {
                'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($avatarPath),
                'image/png' => @imagecreatefrompng($avatarPath),
                'image/webp' => @imagecreatefromwebp($avatarPath),
                default => null,
            };

            if (! $srcImg) {
                return base64_encode(file_get_contents($avatarPath));
            }

            // Step 1: Detect letterbox boundaries (black bars top/bottom/left/right)
            $minY = 0;
            $maxY = $srcH - 1;
            $minX = 0;
            $maxX = $srcW - 1;

            // Scan top black bar
            for ($y = 0; $y < min($srcH * 0.4, 600); $y += 4) {
                $isBlack = true;
                for ($x = (int) ($srcW * 0.2); $x <= (int) ($srcW * 0.8); $x += (int) ($srcW * 0.15)) {
                    $c = imagecolorat($srcImg, $x, $y);
                    $r = ($c >> 16) & 0xFF;
                    $g = ($c >> 8) & 0xFF;
                    $b = $c & 0xFF;
                    if ($r > 30 || $g > 30 || $b > 30) {
                        $isBlack = false;
                        break;
                    }
                }
                if (! $isBlack) {
                    $minY = max(0, $y - 4);
                    break;
                }
            }

            // Scan bottom black bar
            for ($y = $srcH - 1; $y >= max($srcH * 0.6, 0); $y -= 4) {
                $isBlack = true;
                for ($x = (int) ($srcW * 0.2); $x <= (int) ($srcW * 0.8); $x += (int) ($srcW * 0.15)) {
                    $c = imagecolorat($srcImg, $x, $y);
                    $r = ($c >> 16) & 0xFF;
                    $g = ($c >> 8) & 0xFF;
                    $b = $c & 0xFF;
                    if ($r > 30 || $g > 30 || $b > 30) {
                        $isBlack = false;
                        break;
                    }
                }
                if (! $isBlack) {
                    $maxY = min($srcH - 1, $y + 4);
                    break;
                }
            }

            $contentX = $minX;
            $contentY = $minY;
            $contentW = $maxX - $minX + 1;
            $contentH = $maxY - $minY + 1;

            // Step 2: Calculate crop box based on content and target ratio
            if ($targetRatio === '3:4') {
                $destW = 300;
                $destH = 400;
                $targetAspect = 3 / 4;

                if ($contentW / $contentH > $targetAspect) {
                    // Wider than 3:4
                    $cropH = $contentH;
                    $cropW = (int) ($contentH * $targetAspect);
                    $cropX = (int) ($contentX + ($contentW - $cropW) / 2);
                    $cropY = $contentY;
                } else {
                    // Taller than 3:4 (e.g. portrait photo with person)
                    // Zoom slightly to frame the upper body & face
                    $cropW = (int) ($contentW * 0.85);
                    $cropH = (int) ($cropW / $targetAspect);
                    if ($cropH > $contentH) {
                        $cropH = $contentH;
                        $cropW = (int) ($cropH * $targetAspect);
                    }
                    $cropX = (int) ($contentX + ($contentW - $cropW) / 2);
                    // Center vertically around the person's upper body (40% down the content)
                    $cropY = (int) ($contentY + ($contentH - $cropH) * 0.45);
                    $cropY = max($contentY, min($contentY + $contentH - $cropH, $cropY));
                }
            } else {
                // 1:1 Square
                $destW = 300;
                $destH = 300;

                if ($contentW > $contentH) {
                    // Wider photo
                    $cropSize = $contentH;
                    $cropX = (int) ($contentX + ($contentW - $cropSize) / 2);
                    $cropY = $contentY;
                } else {
                    // Taller photo (zoom in on head/upper body)
                    $cropSize = (int) min($contentW, $contentH * 0.7);
                    $cropX = (int) ($contentX + ($contentW - $cropSize) / 2);
                    // Focus on upper body / person
                    $cropY = (int) ($contentY + ($contentH - $cropSize) * 0.5);
                    $cropY = max($contentY, min($contentY + $contentH - $cropSize, $cropY));
                }
                $cropW = $cropSize;
                $cropH = $cropSize;
            }

            $destImg = imagecreatetruecolor($destW, $destH);
            imagealphablending($destImg, false);
            imagesavealpha($destImg, true);

            imagecopyresampled($destImg, $srcImg, 0, 0, $cropX, $cropY, $destW, $destH, $cropW, $cropH);

            ob_start();
            imagejpeg($destImg, null, 94);
            $data = ob_get_clean();

            imagedestroy($srcImg);
            imagedestroy($destImg);

            return base64_encode($data);
        } catch (\Throwable $e) {
            return base64_encode(file_get_contents($avatarPath));
        }
    }
}
