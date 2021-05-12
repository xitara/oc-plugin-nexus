<?php namespace Xitara\Nexus\Classes;

use Carbon\Carbon;
use Cms\Classes\Theme;
use Config;
use File;
use Html;
use Kuse\Core\Plugin as Core;
use League\Flysystem\FileNotFoundException;
use October\Rain\Parse\Bracket;
use Storage;

/**
 * additional twig filters
 */
class TwigFilter
{
    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'phone_link' => [$this, 'filterPhoneLink'],
                'email_link' => [$this, 'filterEmailLink'],
                'mediadata' => [$this, 'filterMediaData'],
                'filesize' => [$this, 'filterFileSize'],
                'regex_replace' => [$this, 'filterRegexReplace'],
                'slug' => 'str_slug',
                'strip_html' => [$this, 'filterStripHtml'],
                'truncate_html' => [$this, 'filterTruncateHtml'],
                'inject' => [$this, 'filterInject'],
                'image_text' => [$this, 'filterAddImageText'],
                'parentlink' => [$this, 'filterParentLink'],
                'localize' => [$this, 'filterLocalize'],
                'css_var' => [$this, 'filterCssVars'],
                'fa' => [$this, 'filterFontAwesome'],
            ],
            'functions' => [
                'uid' => [$this, 'functionGenerateUid'],
                'config' => [$this, 'functionConfig'],
            ],
        ];
    }

    /**
     * adds link to given phone - |phone_link
     *
     * options: {
     *     'classes': 'class1 class2 classN',
     *     'text_before': '<strong>sample</strong>',
     *     'text_after': '<strong>sample</strong>',
     *     'hide_mail': true|false (hide mail-address in text or not)
     * }
     *
     * example:
     * <img src="{{ store.image.getPath() }}"{{ store.image|image_text({
     *     default: {title: store.name}
     * }) }}>
     *
     * @param  string $text    text from twig
     * @param  array $options options from twig
     * @return string          complete link in html
     */
    public function filterPhoneLink($text, $options = null): string
    {
        /**
         * process options
         */
        $textBefore = $options['text_before'] ?? '';
        $textAfter = $options['text_after'] ?? '';
        $classes = $options['classes'] ?? null;
        $hideNubmer = $options['hide_number'] ?? false;

        /**
         * generate link
         */
        $link = '<a';

        if ($classes !== null) {
            $link .= ' class="' . $classes . '"';
        }

        $link .= ' href="tel:';
        $link .= preg_replace('/\(0\)|[^0-9\+]|\s+/', '', $text) . '">';
        $link .= $textBefore;

        if ($hideNubmer === false) {
            $link .= $text;
        }

        $link .= $textAfter;
        $link .= '</a>';

        return $link;
    }

    /**
     * adds link to given email - |email_link
     *
     * options: {
     *     'classes': 'class1 class2 classN',
     *     'text_before': '<strong>sample</strong>',
     *     'text_after': '<strong>sample</strong>',
     *     'hide_mail': true|false (hide mail-address in text or not),
     *     'image': this.theme.icon_email|media
     * }
     *
     * @param  string $text    text from twig
     * @param  array $options options from twig
     * @return string          complete link in html
     */
    public function filterEmailLink($text, $options = null): string
    {
        /**
         * remove subject and body from mail if given
         */
        $parts = explode('?', $text);
        $mail = $parts[0];
        $query = isset($parts[1]) ? '?' . $parts[1] : '';

        /**
         * process options
         */
        $textBefore = $options['text_before'] ?? '';
        $textAfter = $options['text_after'] ?? '';
        $classes = $options['classes'] ?? null;
        $hideMail = $options['hide_mail'] ?? false;
        $image = $options['image'] ?? null;

        /**
         * generate link
         */
        $link = '<a';

        if ($classes !== null) {
            $link .= ' class="' . $classes . '"';
        }

        $link .= ' href="mailto:' . Html::email($mail) . $query . '">';
        $link .= $textBefore;

        if ($image !== null) {
            $link .= '<img src="' . $image . '" alt="' . $mail . '">';
        }

        if ($hideMail === false) {
            $link .= $mail;
        }

        $link .= $textAfter;
        $link .= '</a>';

        return $link;
    }

    /**
     * mediadata filter - |mediadata
     *
     * file should be in storage/app/[path], where path-default is "media"
     * for the media-manager
     *
     * @param  string $media filename
     * @param  string $path  relativ path in storage/app
     * @return array|boolean        filedata or false if file not exists
     */
    public function filterMediaData($media, $path = 'media'): array
    {
        $empty = [
            'size' => 0,
            'mime_type' => 'none/none',
            'type' => 'none',
            'art' => 'none',
        ];

        if ($media === null) {
            return $empty;
        }

        // return [Storage::getMimetype($path . $media)];

        // Log::debug($media[0]);
        // Log::debug($path);

        try {
            if ($media[0] != '/') {
                $media = '/' . $media;
            }
            // Log::debug($media);

            if (strpos(Storage::getMimetype($path . $media), '/')) {
                list($type, $art) = explode('/', Storage::getMimetype($path . $media));
            }

            if ($art == 'svg+xml') {
                $art = 'svg';
            }

            $data = [
                'size' => Storage::size($path . $media),
                'mime_type' => Storage::getMimetype($path . $media),
                'type' => $type ?? null,
                'art' => $art ?? null,
            ];

            return $data;
        } catch (FileNotFoundException $e) {
            return $empty;
        }
    }

    /**
     * filesize filter - |filesize
     *
     * returns filesize of given file
     *
     * @param  string $filename filename
     * @param  string $path      path relative to storage/app, default "media"
     * @return int|boolean           filesize in bytes or false if file not exists
     */
    public function filterFileSize($filename, $path = 'media'): string
    {
        $size = Storage::size($path . $filename);
        return $size;
    }

    /**
     * filter regex replace - |regex_replace
     *
     * replace a regex pattern with replacement in a given string
     *
     * @param  string $subject     source string
     * @param  string $pattern     pattern to replace
     * @param  string $replacement replacement string
     * @return string              new string
     */
    public function filterRegexReplace($subject, $pattern, $replacement): string
    {
        return preg_replace($pattern, $replacement, $subject);
    }

    /**
     * strip html from a string - |strip_html
     * @param  string $text string to replace html within
     * @return string       string without html
     */
    public function filterStripHtml($text)
    {
        return Html::strip($text);
    }

    /**
     * truncate text and check html tags - |truncate_html
     * @param  string $text   string to truncate
     * @param  integer $lenght string length after truncate. Default: 100
     * @param  string $hint   hint after truncated text, default '...'
     * @return string         truncated string with html
     */
    public function filterTruncateHtml($text, $lenght = 100, $hint = '...'): string
    {
        return Html::limit($text, $lenght, $hint);
    }

    /**
     * inject filecontent directly inside html. useful for svg or so - |inject
     * @param  string $text filename relative to project root
     * @return string       content of file
     */
    public function filterInject($text): string
    {
        $fileContent = File::get(base_path($text));
        $fileContent = preg_replace('/<\?xml(.|\s)*?\?>/', '', $fileContent);

        return $fileContent;
    }

    /**
     * get data from config files - |config
     * @param  string $text config route like Config::get() -> example: app.name
     * @return string       config-data or null
     */
    public function functionConfig($text)
    {
        return Config::get($text);
    }

    /**
     * adds alt and optional title attributes - |image_text
     *
     * options: {
     *     'first': 'title|description', // outputs title or description as default. default: title
     *     'alt': true|false, // show alt-attribute, default: true
     *     'title': true|false, // show title-attribute, default: false
     *     'default': { // optional. will be used if image has no title and description
     *         title: 'Foo',
     *         description: 'Bar',
     *     }
     * }
     *
     * @param  object $image   image object from attached image
     * @param  array $options some optional options
     * @return string          prefixed text with $art
     */
    public function filterAddImageText($image, $options = null): string
    {
        if ($image === null) {
            return '';
        }

        $alt = $title = null;

        /**
         * generate alt, display as default -> alt: false
         */
        if ($options['alt'] ?? true === true) {
            $alt = $this->checkImageText($image, $options, 'alt');
        }

        /**
         * generate title, display as option -> title: true
         */
        if ($options['title'] ?? false === true) {
            $title = $this->checkImageText($image, $options, 'title');
        }

        return $alt . $title;
    }

    /**
     * adds alt or title text and return prefixed string
     *
     * @see self::filterAddImageText()
     * @param  object $image   image object from attached image
     * @param  array $options some optional options
     * @param  string $art     alt or title
     * @return string          prefixed text with $art
     */
    private function checkImageText($image, $options, $art)
    {
        $text = $options['default']['description'] ?? '';

        if (isset($image->description) && $image->description != '') {
            $text = Html::strip($image->description);
        }

        if ($text == '') {
            $text = $options['default']['title'] ?? '';
        }

        if (isset($image->title)
            && $image->title !== null
            && $image->title != ''
            && ($text == '' || ($options['first'] ?? 'title') == 'title')
        ) {
            $text = Html::strip($image->title);
        }

        if ($text != '') {
            $text = ' ' . $art . '="' . $text . '"';
        }

        return $text;
    }

    /**
     * uid() - generates a unique id
     *
     * @autor   mburghammer
     * @date    2021-01-01T15:26:37+01:00
     * @version 0.0.1
     * @since   0.0.1
     * @return  string      unique id
     */
    public function functionGenerateUid(): string
    {
        $id = uniqid(rand(), true);
        $id = str_replace('.', '-', $id);
        return $id;
    }

    /**
     * creates a link to parent page (one level up) - |parentlink
     * @param  string $text filename relative to project root
     * @return string       content of file
     */
    public function filterParentLink($text): string
    {
        $parts = explode('/', $text);
        array_pop($parts);

        return join('/', $parts);
    }

    /**
     * generates date and time with utcOffset  - |localize(this.param.utcOffset)
     * @param  array $data   datetime-string, utc-offset
     * @return string       patched timestamp
     */
    public function filterLocalize(...$data)
    {
        $utcOffset = $data[1] ?? null;

        if ($utcOffset !== null && $utcOffset !== false) {
            $utcOffset *= -1;
            $utcOffset /= 60;

            $timezone = Carbon::now($utcOffset)->tzName;
        } else {
            $timezone = Core::getTimezone();
        }

        \Log::debug($timezone);

        $time = Carbon::parse($data[0]);
        $time->setTimezone($timezone);

        return $time->toDateTimeString();
    }
    /**
     * fa() - generates full path to fa-sprites
     *
     * @autor   mburghammer
     * @date    2021-02-14T00:22:14+01:00
     * @version 0.0.1
     * @since   0.0.1
     * @return  string      sprite with full path
     */
    public function filterFontAwesome($icon, $collection = null)
    {
        if ($collection === null) {
            $collection = \Xitara\Nexus\Components\FontAwsome::getDefaultSprite();
        }

        return plugins_url('xitara/nexus/assets/sprites/' . $collection . '.svg#' . $icon);
    }

    /**
     * |css_var - parse string to math pathes
     *
     * @autor   mburghammer
     * @date    2021-02-14T00:22:14+01:00
     * @version 0.0.1
     * @since   0.0.1
     * @todo <mid>check for avtive URL with Briddle.MultiSite</mid>
     *
     * @param  string $string string to parse
     * @param  array $vars optional vars
     * @return  string      sprite with full path
     */

    public function filterCssVars($string, ...$vars)
    {
        $theme = Theme::getActiveTheme();
        $mediaUrl = str_replace(base_path() . '/', '', storage_path('app/media'));

        return Bracket::parse($string, [
            'theme' => Config::get('app.url') . $theme->getDirName(),
            'media' => Config::get('app.url') . $mediaUrl,
        ]);
    }
}
