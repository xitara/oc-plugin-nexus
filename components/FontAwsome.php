<?php namespace Xitara\Nexus\Components;

use Cms\Classes\ComponentBase;

class FontAwsome extends ComponentBase
{
    private static $defaultSprite;
    private $faVersion = '5.15.2';

    public function componentDetails()
    {
        return [
            'name' => 'FontAwsome Component',
            'description' => 'No description provided yet...',
        ];
    }

    public function onRun()
    {
        self::$defaultSprite = $this->property('default_sprite');

        $this->addCss(plugins_url('xitara/nexus/assets/css/fontawesome.css'), $this->faVersion);

        if ($this->property('regular')) {
            $this->addCss(plugins_url('xitara/nexus/assets/css/regular.css'), $this->faVersion);
        }

        if ($this->property('solid')) {
            $this->addCss(plugins_url('xitara/nexus/assets/css/solid.css'), $this->faVersion);
        }

        if ($this->property('brands')) {
            $this->addCss(plugins_url('xitara/nexus/assets/css/brands.css'), $this->faVersion);
        }
    }

    public function defineProperties()
    {
        return [
            'regular' => [
                'title' => 'xitara.nexus::components.regular.label',
                'description' => 'xitara.nexus::components.regular.description',
                'default' => 0,
                'type' => 'checkbox',
            ],
            'solid' => [
                'title' => 'xitara.nexus::components.solid.label',
                'description' => 'xitara.nexus::components.solid.description',
                'default' => 0,
                'type' => 'checkbox',
            ],
            'brands' => [
                'title' => 'xitara.nexus::components.brands.label',
                'description' => 'xitara.nexus::components.brands.description',
                'default' => 0,
                'type' => 'checkbox',
            ],
            'default_sprite' => [
                'title' => 'xitara.nexus::components.defaulr_sprite.label',
                'description' => 'xitara.nexus::components.defaulr_sprite.description',
                'default' => 'regular',
                'type' => 'dropdown',
                'options' => [
                    'regular' => 'Regular',
                    'solid' => 'Solid',
                    'brands' => 'Brands',
                ],
            ],
        ];
    }

    public static function getDefaultSprite()
    {
        return self::$defaultSprite;
    }
}
