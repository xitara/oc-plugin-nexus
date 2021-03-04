<?php

use \October\Rain\Support\Facades\Config;

if (!function_exists('plugins_url')) {
    function plugins_url($path = '')
    {
        return url(Config::get('cms.pluginsPath') . '/' . $path);
    }
}
