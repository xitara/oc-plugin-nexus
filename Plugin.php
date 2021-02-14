<?php namespace Xitara\Nexus;

use App;
use Backend;
use BackendAuth;
use BackendMenu;
use Backend\Controllers\Users;
use Backend\Models\User;
use Backend\Models\UserRole;
use Config;
use Event;
use Redirect;
use Str;
use System\Classes\PluginBase;
use System\Classes\PluginManager;
use Xitara\Nexus\Classes\TwigFilter;
use Xitara\Nexus\Models\CustomMenu;
use Xitara\Nexus\Models\Menu;
use Xitara\Nexus\Models\Settings as NexusSettings;

class Plugin extends PluginBase
{
    public $require = [
        'Romanov.ClearCacheWidget',
    ];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'xitara.nexus::lang.plugin.name',
            'description' => 'xitara.nexus::lang.plugin.description',
            'author' => 'xitara.nexus::lang.plugin.author',
            'homepage' => 'xitara.nexus::lang.plugin.homepage',
            'icon' => '',
            'iconSvg' => 'plugins/xitara/nexus/assets/images/icon-nexus.svg',
        ];
    }

    public function register()
    {
        BackendMenu::registerContextSidenavPartial(
            'Xitara.Nexus',
            'nexus',
            '$/xitara/nexus/partials/_sidebar.htm'
        );

        $this->registerConsoleCommand('xitara.fakeblog', 'Xitara\Nexus\Console\FakeBlog');
        $this->registerConsoleCommand('xitara.fakeuser', 'Xitara\Nexus\Console\FakeUser');
    }

    public function boot()
    {
        // Check if we are currently in backend module.
        if (!App::runningInBackend()) {
            return;
        }

        /**
         * set new backend-skin
         */
        Config::set('cms.backendSkin', 'Xitara\Nexus\Classes\BackendSkin');

        /**
         * add items to sidemenu
         */
        $this->getSideMenu('Xitara.Nexus', 'nexus');

        Event::listen('backend.page.beforeDisplay', function ($controller, $action, $params) {
            if (NexusSettings::get('is_compact_display')) {
                $controller->addCss('/plugins/xitara/nexus/assets/css/compact.css');
            }

            $controller->addCss('/plugins/xitara/nexus/assets/css/app.css');
            $controller->addCss('/plugins/xitara/nexus/assets/css/darkmode.css');
            // $controller->addCss('/plugins/xitara/nexus/assets/css/app.css');
            $controller->addJs('/plugins/xitara/nexus/assets/js/backend.js');

            if ($controller instanceof Backend\Controllers\Index) {
                return Redirect::to('/backend/xitara/nexus/dashboard');
            }
        });

        /**
         * remove original dashboard
         */
        Event::listen('backend.menu.extendItems', function ($navigationManager) {
            $navigationManager->removeMainMenuItem('October.Backend', 'dashboard');
        });

        /**
         * remove roles publisher and developer if user is not an superuser
         */
        User::extend(function ($model) {
            $model->addDynamicMethod('getMyRoleOptions', function ($model) {
                $result = [];

                $user = BackendAuth::getUser();

                if ($user->is_superuser == 1) {
                    $roles = UserRole::all();
                }

                if ($user->is_superuser == 0) {
                    $roles = UserRole::where('is_system', 0)->get();
                }

                foreach ($roles as $role) {
                    $result[$role->id] = [$role->name, $role->description];
                }

                return $result;
            });
        });

        Event::listen('backend.form.extendFieldsBefore', function ($widget) {
            if (!($widget->getController() instanceof Users && $widget->model instanceof User)) {
                return;
            }

            $widget->tabs['fields']['role']['options'] = 'getMyRoleOptions';
        });

        /**
         * add new toolbor for disable group and permission tab for non superuser
         */
        Users::extend(function ($controller) {
            $controller->listConfig = $controller->makeConfig($controller->listConfig);
            $controller->listConfig->toolbar = array_merge($controller->listConfig->toolbar, ['buttons' => '$/xitara/nexus/partials/toolbar.users.htm']);
        });

        /**
         * remove groups and permission columns from non superuser
         */
        Users::extendListColumns(function ($list, $model) {
            if (BackendAuth::getUser()->isSuperUser()) {
                return;
            }

            $list->removeColumn('permissions');
            $list->removeColumn('groups');
        });

        /**
         * remove groups and permission tabs from non superuser
         */
        Users::extendFormFields(function ($form, $model, $context) {
            if (BackendAuth::getUser()->isSuperUser()) {
                return;
            }

            $form->removeField('permissions');
            $form->removeField('groups');
        });
    }

    public function registerSettings()
    {
        if (($category = NexusSettings::get('menu_text')) == '') {
            $category = 'xitara.nexus::core.settings.name';
        }

        return [
            'settings' => [
                'category' => $category,
                'label' => 'xitara.nexus::lang.settings.label',
                'description' => 'xitara.nexus::lang.settings.description',
                'icon' => 'icon-wrench',
                'class' => 'Xitara\Nexus\Models\Settings',
                'order' => 0,
                'permissions' => ['xitara.nexus.settings'],
            ],
        ];
    }

    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'xitara.nexus.mainmenu' => [
                'tab' => 'Xitara Nexus',
                'label' => 'xitara.nexus::permissions.mainmenu',
            ],
            'xitara.nexus.settings' => [
                'tab' => 'Xitara Nexus',
                'label' => 'xitara.nexus::permissions.settings',
            ],
            'xitara.nexus.dashboard' => [
                'tab' => 'Xitara Nexus',
                'label' => 'xitara.nexus::permissions.dashboard',
            ],
            'xitara.nexus.menu' => [
                'tab' => 'Xitara Nexus',
                'label' => 'xitara.nexus::permissions.menu',
            ],
            'xitara.nexus.custommenus' => [
                'tab' => 'Xitara Nexus',
                'label' => 'xitara.nexus::permissions.custommenus',
            ],
        ];
    }

    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        $iconSvg = NexusSettings::get('menu_icon');
        if ($iconSvg == '' && NexusSettings::get('menu_icon_text', '') == '') {
            $iconSvg = 'plugins/xitara/nexus/assets/images/icon-nexus.svg';
        } elseif ($iconSvg != '') {
            $iconSvg = url(Config::get('cms.storage.media.path') . $iconSvg);
        }

        if (($label = NexusSettings::get('menu_text')) == '') {
            $label = 'xitara.nexus::lang.submenu.label';
        }

        return [
            'nexus' => [
                'label' => $label,
                'url' => Backend::url('xitara/nexus/dashboard'),
                'icon' => NexusSettings::get('menu_icon_text', 'icon-leaf'),
                'iconSvg' => $iconSvg,
                'permissions' => ['xitara.nexus.*'],
                'order' => 50,
            ],
        ];
    }

    /**
     * grab sidemenu items
     * $inject contains addidtional menu-items with the following strcture
     *
     * name = [
     *     group => [string],
     *     label => string|'placeholder', // placeholder only
     *     url => [string], (full backend url)
     *     icon => [string],
     *     'attributes' => [
     *         'target' => [string],
     *         'placeholder' => true|false, // placeholder after elm
     *         'keywords' => [string],
     *         'description' => [string],
     *         'group' => [string], // group the items and set the heading of group
     *     ],
     * ]
     *
     * name -> unique name
     * group -> name to sort menu items
     * label -> shown name in menu
     * url -> url relative to backend
     * icon -> icon left of label
     * attribures -> array (optional)
     *     target -> _blank|_self (optional)
     *     keywords -> only for searching (optional)
     *     description -> showed under label (optional)
     *
     * @autor   mburghammer
     * @date    2018-05-15T20:49:04+0100
     * @version 0.0.3
     * @since   0.0.1
     * @since   0.0.2 added groups
     * @since   0.0.3 added attributes
     * @param   string                   $owner
     * @param   string                   $code
     * @param   array                   $inject
     */
    public static function getSideMenu(string $owner, string $code)
    {
        // Log::debug(NexusSettings::get('menu_text'));
        if (($group = NexusSettings::get('menu_text')) == '') {
            $group = 'xitara.nexus::lang.submenu.label';
        }
        // Log::debug($group);

        $items = [
            'nexus.dashboard' => [
                'label' => 'xitara.nexus::lang.nexus.dashboard',
                'url' => Backend::url('xitara/nexus/dashboard'),
                'icon' => 'icon-dashboard',
                'order' => 1,
                'permissions' => [
                    'xitara.nexus.mainmenu',
                    'xitara.nexus.dashboard',
                ],
                'attributes' => [
                    'group' => $group,
                ],
            ],
            'nexus.menu' => [
                'label' => 'xitara.nexus::lang.nexus.menu',
                'url' => Backend::url('xitara/nexus/menu/reorder'),
                'icon' => 'icon-sort',
                'order' => 2,
                'permissions' => ['xitara.nexus.menu'],
                'attributes' => [
                    'group' => $group,
                ],
            ],
            'nexus.custommenus' => [
                'label' => 'xitara.nexus::lang.custommenu.label',
                'url' => Backend::url('xitara/nexus/custommenus'),
                'icon' => 'icon-link',
                'order' => 3,
                'permissions' => ['xitara.nexus.custommenus'],
                'attributes' => [
                    'group' => $group,
                ],
            ],
        ];

        foreach (PluginManager::instance()->getPlugins() as $name => $plugin) {
            $namespace = str_replace('.', '\\', $name) . '\Plugin';

            if (method_exists($namespace, 'injectSideMenu')) {
                $inject = $namespace::injectSideMenu();
                // var_dump($namespace);

                $items = array_merge($items, $inject);
            }
        }
        // var_dump($items);

        Event::listen('backend.menu.extendItems', function ($manager) use ($owner, $code, $items) {
            $manager->addSideMenuItems($owner, $code, $items);
        });
    }

    public static function getMenuOrder(String $code): int
    {
        $item = Menu::find($code);

        if ($item === null) {
            return 9999;
        }

        return $item->sort_order;
    }

    /**
     * inject into sidemenu
     * @autor   mburghammer
     * @date    2020-06-26T21:13:34+02:00
     *
     * @see Xitara\Nexus::getSideMenu
     * @return  array                   sidemenu-data
     */
    public static function injectSideMenu()
    {
        // Log::debug(__METHOD__);

        $custommenus = CustomMenu::where('is_submenu', 1)
            ->where('is_active', 1)
            ->get();

        $inject = [];
        foreach ($custommenus as $custommenu) {
            $count = 0;
            foreach ($custommenu->links as $text => $link) {
                if ($link['is_active'] == 1) {
                    $icon = $iconSvg = null;

                    if (isset($link['icon']) && $link['icon'] != '') {
                        $icon = $link['icon'];
                    }

                    if (isset($link['icon_image']) && $link['icon_image'] != '') {
                        $iconSvg = url(Config::get('cms.storage.media.path') . $link['icon_image']);
                    }

                    // Log::debug($icon);
                    // Log::debug($iconSvg);

                    $inject['custommenulist.' . $custommenu->slug . '.' . Str::slug($link['text'])] = [
                        'label' => $link['text'],
                        'url' => $link['link'],
                        'icon' => $icon ?? null,
                        'iconSvg' => $iconSvg,
                        'permissions' => ['submenu.custommenu.' . $custommenu->slug . '.'
                            . Str::slug($link['text'])],
                        'attributes' => [
                            'group' => 'xitara.custommenulist.' . $custommenu->slug,
                            'groupLabel' => $custommenu->name,
                            'target' => ($link['is_blank'] == 1) ? '_blank' : null,
                            'keywords' => $link['keywords'] ?? null,
                            'description' => $link['description'] ?? null,
                        ],
                        'order' => self::getMenuOrder('xitara.custommenulist.' . $custommenu->slug) + $count++,
                    ];
                }
            }
        }

        return $inject;
    }

    public function registerComponents()
    {
        return [
            'Xitara\Nexus\Components\FontAwsome' => 'fontAwsome',
        ];
    }

    public function registerMarkupTags()
    {
        $twigfilter = new TwigFilter;

        $filters = [];

        if (NexusSettings::get('is_twig_filters')) {
            $filters = [
                'phone_link' => [$twigfilter, 'filterPhoneLink'],
                'email_link' => [$twigfilter, 'filterEmailLink'],
                'mediadata' => [$twigfilter, 'filterMediaData'],
                'filesize' => [$twigfilter, 'filterFileSize'],
                'regex_replace' => [$twigfilter, 'filterRegexReplace'],
                'slug' => 'str_slug',
                'strip_html' => [$twigfilter, 'filterStripHtml'],
                'truncate_html' => [$twigfilter, 'filterTruncateHtml'],
            ];

            $functions = [
                'uid' => [$twigfilter, 'functionGenerateUid'],
            ];
        }

        $filters['fa'] = [$twigfilter, 'filterFontAwesome'];

        return [
            'filters' => $filters,
            'functions' => $functions,
        ];
    }
}
