<?php
/*
 * DiogoBenica\FileUpload\ImageManipulator.php
 * https://github.com/diogobenica/file-upload
 *
 * Copyright 2012, Diogo Benicá
 * http://diogobenica.com
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */
namespace DiogoBenica\FileUpload;

class ImageManipulator
{
	protected function createScaledImage($filename, $version, $options)
	{
        $filepath = $this->get_upload_path($filename);
        if (!empty($version)) {
            $version_dir = $this->get_upload_path(null, $version);
            if (!is_dir($version_dir)) {
                mkdir($version_dir, $this->options['mkdir_mode']);
            }
            $new_filepath = $version_dir.'/'.$filename;
        } else {
            $new_filepath = $filepath;
        }
        list($img_width, $img_height) = @getimagesize($filepath);
        if (!$img_width || !$img_height) {
            return false;
        }
        $scale = min(
            $options['max_width'] / $img_width,
            $options['max_height'] / $img_height
        );
        if ($scale >= 1) {
            if ($filepath !== $new_filepath) {
                return copy($filepath, $new_filepath);
            }
            return true;
        }
        $new_width = $img_width * $scale;
        $new_height = $img_height * $scale;
        $new_img = @imagecreatetruecolor($new_width, $new_height);
        switch (strtolower(substr(strrchr($filename, '.'), 1))) {
            case 'jpg':
            case 'jpeg':
                $src_img = @imagecreatefromjpeg($filepath);
                $write_image = 'imagejpeg';
                $image_quality = isset($options['jpeg_quality']) ?
                    $options['jpeg_quality'] : 75;
                break;
            case 'gif':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                $src_img = @imagecreatefromgif($filepath);
                $write_image = 'imagegif';
                $image_quality = null;
                break;
            case 'png':
                @imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
                @imagealphablending($new_img, false);
                @imagesavealpha($new_img, true);
                $src_img = @imagecreatefrompng($filepath);
                $write_image = 'imagepng';
                $image_quality = isset($options['png_quality']) ?
                    $options['png_quality'] : 9;
                break;
            default:
                $src_img = null;
        }
        $success = $src_img && @imagecopyresampled(
            $new_img,
            $src_img,
            0, 0, 0, 0,
            $new_width,
            $new_height,
            $img_width,
            $img_height
        ) && $write_image($new_img, $new_filepath, $image_quality);

        @imagedestroy($src_img);
        @imagedestroy($new_img);

        return $success;
    }

    protected function orientImage($filepath)
    {
        $exif = @exif_read_data($filepath);
        if ($exif === false) {
            return false;
        }
        $orientation = intval(@$exif['Orientation']);
        if (!in_array($orientation, array(3, 6, 8))) {
            return false;
        }
        $image = @imagecreatefromjpeg($filepath);
        switch ($orientation) {
            case 3:
                $image = @imagerotate($image, 180, 0);
                break;
            case 6:
                $image = @imagerotate($image, 270, 0);
                break;
            case 8:
                $image = @imagerotate($image, 90, 0);
                break;
            default:
                return false;
        }
        $success = imagejpeg($image, $filepath);
        @imagedestroy($image);

        return $success;
    }
}