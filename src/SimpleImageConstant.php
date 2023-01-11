<?php

namespace NickLabs\SimpleImage;

class SimpleImageConstant{

    /** @var $mimeMatchExtensions string[]  */
    public static $mimeMatchExtensions = [
        'image/png' => 'png',
        'image/jpeg' => 'jpeg',
        'image/gif' => 'gif',
        'image/bmp' => 'bmp',
        'image/vnd.microsoft.icon' => 'ico',
        'image/tiff' => 'tiff',
        'image/svg+xml' => 'svg',
    ];
}