<?php namespace Xitara\Nexus\Models;

use Model;

/**
 * Config Model
 */
class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];
    public $settingsCode = 'xitara_nexus_setting';
    public $settingsFields = 'fields.yaml';

    public $attachOne = [
        'menu_icon_uploaded' => [
            '\System\Models\File',
            'public' => false,
        ],
    ];

}
