<?php

namespace App\Controllers;

use Core\Controller;

class AvatarController extends Controller
{
    /**
     * Serve an avatar image.
     */
    public function show(string $filename): void
    {
        $uploadDir = __DIR__ . '/../../uploads/avatars/';
        $filepath = $uploadDir . basename($filename); // basename() prevents directory traversal

        if (!file_exists($filepath)) {
            http_response_code(404);
            exit;
        }

        // Determine MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filepath);
        finfo_close($finfo);

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: public, max-age=31536000');

        readfile($filepath);
        exit;
    }
}
