<?php namespace Xitara\Nexus\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * CustomMenus Back-end Controller
 */
class CustomMenus extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Xitara.Nexus', 'nexus', 'nexus.custommenus');

        $this->pageTitle = 'xitara.nexus::core.submenu.user_menus';
    }
}
