# Xitara Nexus Plugin [![devDependency Status](https://david-dm.org/xitara/oc-plugin-nexus/dev-status.svg)](https://david-dm.org/xitara/oc-plugin-nexus/?type=dev) [![Known Vulnerabilities](https://snyk.io/test/github/xitara/oc-plugin-nexus/badge.svg)](https://snyk.io//test/github/xitara/oc-plugin-nexus)

Implements backend sidemenu, custom menus, menu sorting

## Getting started

- clone the repo to folder `plugins/xitara/nexus`
- cd to `plugins/xitara/nexus`
- run `yarn` to fetch all the dependencies

## Commands

- `start` - start the dev server
- `cleanup` - remove compiled data, node_modules, vendor, etc. don't delete any sources
- `watch` - start webpack --watch
- `dwatch` - start webpack --watch --mode development
- `build` - build the complete app including copying static content
- `dbuild` - build the complete app including copying static content with --mode development
- `zip` - zips a package with only needed files without overhead
- `deploy` - deploys a package with only needed files without overhead in a folder without zipping
- `ftp` - uploads a minimizes package to a configured server (needs lftp)
- `analyze` - analyze your production bundle
- `lint-code` - run an ESLint check
- `lint-style` - run a Stylelint check
- `check-eslint-config` - check if ESLint config contains any rules that are unnecessary or conflict with Prettier
- `check-stylelint-config` - check if Stylelint config contains any rules that are unnecessary or conflict with Prettier

## Register new Plugin to Sidemenu

### Add on top of Plugin.php
```php
use App;
use Backend;
use BackendMenu;
use Event;
use System\Classes\PluginBase;
use System\Classes\PluginManager;
```

### Add to boot() method to catch event and display new sidemenu.
```php
/**
 * Check if we are currently in backend module.
 */
if (!App::runningInBackend()) {
    return;
}

/**
 * get sidemenu if nexus-plugin is loaded
 */
if (PluginManager::instance()->exists('Xitara.Nexus') === true) {
    Event::listen('backend.page.beforeDisplay', function ($controller, $action, $params) {
        $namespace = (new \ReflectionObject($controller))->getNamespaceName();

        if ($namespace == '[VENDOR]\[PLUGIN]\Controllers') {
            \Xitara\Nexus\Plugin::getSideMenu('[VENDOR].[PLUGIN]', '[PLUGIN-SLUG]');
        }
    });
}
```

### Register sidemenu partial
```php
public function register()
{
    if (PluginManager::instance()->exists('Xitara.Nexus') === true) {
        BackendMenu::registerContextSidenavPartial(
            '[VENDOR].[PLUGIN]',
            '[PLUGIN-SLUG]',
            '$/xitara/nexus/partials/_sidebar.htm'
        );
    }
    // ...
}
```

### Extend your navigation label with ::hidden to hide it from top navigation
```php
public function registerNavigation()
{
    $label = '[VENDOR-SLUG].[PLUGIN-SLUG]::lang.plugin.name';

    if (PluginManager::instance()->exists('Xitara.Nexus') === true) {
        $label .= '::hidden';
    }

    return [
        '[VENDOR-SLUG]' => [
            'label' => $label,
            'url' => Backend::url('[VENDOR-SLUG]/[PLUGIN-SLUG]/[CONTROLLER-SLUG]'),
            'icon' => 'icon-leaf',
            'permissions' => ['[VENDOR-SLUG].[PLUGIN-SLUG].*'],
            'order' => 500,
        ],
    ];
}
```

### Inject menu items
```php
public static function injectSideMenu()
{
    $i = 0;
    return [
        '[PLUGIN-SLUG].[CONTROLLER-SLUG]' => [
            'label' => '[VENDOR-SLUG].[PLUGIN-SLUG]::lang.submenu.[CONTROLLER-SLUG]',
            'url' => Backend::url('[VENDOR-SLUG]/[PLUGIN-SLUG]/[CONTROLLER-SLUG]'),
            'icon' => 'icon-archive',
            'permissions' => ['[VENDOR-SLUG].[PLUGIN-SLUG].*'],
            'attributes' => [ // can be extendet if you need, no limitations
                'group' => '[VENDOR-SLUG].[PLUGIN-SLUG]::lang.submenu.label',
                'level' => 1, // optional, default is level 0. adds css-class level-X to li
            ],
            'order' => Nexus::getMenuOrder('[VENDOR-SLUG].[PLUGIN-SLUG]') + $i++,
        ],
        ...
    ];
}
```

### Set backend menu context in controller
```php
public function __construct()
{
    parent::__construct();
    BackendMenu::setContext('[VENDOR].[PLUGIN]', '[PLUGIN-SLUG]', 'nexus.[CONTROLLER-SLUG]');
}
```

## Translation

- `[VENDOR-SLUG].[PLUGIN-SLUG]::lang.submenu.label` is the heading of your menu items
- `[VENDOR-SLUG].[PLUGIN-SLUG]::lang.submenu.[CONTROLLER]` is the your menu item

## Register backend settings
### You must implement your own settings model in your plugin
On top of `Plugin.php`:
```php
use Xitara\Nexus\Models\Setting;
```

and as registration method
```php
public function registerSettings()
{
    if (($category = Settings::get('menu_text')) == '') {
        $category = 'xitara.nexus::core.setting.name';
    }

    return [
        'settings' => [
            'category' => $category,
            'label' => '[VENDOR_SLUG].[PLUGIN_SLUG]::lang.submenu.label',
            'description' => '[VENDOR_SLUG].[PLUGIN_SLUG]::lang.submenu.description',
            'icon' => 'icon-comments-o',
            'class' => '[VENDOR]\[PLUGIN]\Models\Settings',
            'order' => 20,
        ],
    ];
}
```
