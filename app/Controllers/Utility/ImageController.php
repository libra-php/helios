<?php

namespace App\Controllers\Utility;

use App\Models\File;
use Helios\Web\Controller;
use StellarRouter\{Get, Group};

#[Group(prefix: "/image")]
class ImageController extends Controller
{
    #[Get("/resize/{width}/{height}/{uuid}", "image.resize")]
    public function resize(int $width, int $height, string $uuid) : void
    {
        $file = File::where("uuid", $uuid)->get();

        if ($file) {
            // Set headers
            $expires = 60 * 60 * 24 * 30; // about a month
            header("Cache-Control: public, max-age={$expires}");
            header("Expires: " . gmdate('D, d M Y H:i:s', time() + $expires) . ' GMT');
            header("Access-Control-Allow-Origin: *");
            header("Content-Type: image/png");
            $cache_directory = "/tmp/";

            // Generate a unique cache filename based on the parameters.
            $cache_filename = $file->uuid . '.png';
            $cache_filepath = $cache_directory . $cache_filename;

            // Check if the cached image exists.
            if (file_exists($cache_filepath)) {
                // Serve the cached image.
                readfile($cache_filepath);
                exit;
            }

            $image = $file->path;
            if (file_exists($image)) {
                $imagick = new \imagick($image);
                //crop and resize the image
                $imagick->cropThumbnailImage($width, $height);
                //remove the canvas
                $imagick->setImagePage(0, 0, 0, 0);
                $imagick->setImageFormat("png");
                // Save the resized image to the cache directory.
                $imagick->writeImage($cache_filepath);
                echo $imagick->getImageBlob();
                exit;
            }
        }
    }
}
