<?php namespace Xitara\Nexus\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use System\Classes\PluginManager;
use Xitara\Nexus\Models\Menu as MenuModel;

/**
 * Menu Back-end Controller
 */
class Menu extends Controller
{
    public $implement = [
        'Backend.Behaviors.ReorderController',
    ];

    public $reorderConfig = 'config_reorder.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Xitara.Nexus', 'nexus', 'nexus.menu');
        $this->collectMenuItems();
        $this->pageTitle = 'xitara.nexus::core.submenu.menu_order';
    }

    private function collectMenuItems()
    {
        foreach (PluginManager::instance()->getPlugins() as $name => $plugin) {
            $namespace = str_replace('.', '\\', $name) . '\Plugin';

            if (method_exists($namespace, 'injectSideMenu')) {
                $menu = $namespace::injectSideMenu();

                if (empty($menu)) {
                    continue;
                }

                $item = array_shift($menu);

                if (isset($item['attributes']['no_reorder']) &&
                    $item['attributes']['no_reorder'] == true) {
                    continue;
                }

                $group = $item['group'] ?? $item['attributes']['group'] ?? null;

                if ($group === null) {
                    continue;
                }

                $code = explode('::', $group);
                $code = $code[0];
                $model = MenuModel::find($code);

                if ($model !== null) {
                    continue;
                }

                $model = new MenuModel;
                $model->code = $code;
                $model->sort_order = 9999;
                $model->save();
            }
        }

        /**
         * resort sort_order
         */
        $i = 100;
        foreach (MenuModel::orderBy('sort_order', 'asc')->get() as $model) {
            $model->sort_order = $i;
            $model->save();
            $i += 100;
        }
    }
}
