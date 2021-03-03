<?php

use October\Rain\Filesystem\PathResolver;
use October\Rain\Support\Str;
use \October\Rain\Support\Facades\Config;

if (!function_exists('plugins_url')) {
    function plugins_url($path = '')
    {
        return url(Config::get('cms.pluginsPath') . '/' . $path);
    }
}

if (!function_exists('media_path')) {
    /**
     * Get the path to the media folder.
     *
     * @param  string  $path
     * @return string
     */
    function media_path($path = '')
    {
        return PathResolver::join(Config::get('cms.storage.media.path', app('path.media')), $path);
    }
}
