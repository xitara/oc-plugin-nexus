<?php namespace Xitara\Nexus\Classes;

use Html;
use Storage;

/**
 * additional twig filters
 */
class TwigFilter
{
    /**
     * |phone_link - adds link to given phone
     *
     * options: {
     *     'classes': 'class1 class2 classN',
     *     'text_before': '<strong>sample</strong>',
     *     'text_after': '<strong>sample</strong>',
     *     'hide_mail': true|false (hide mail-address in text or not)
     * }
     *
     * @param  string $text    text from twig
     * @param  array $options options from twig
     * @return string          complete link in html
     */
    public function filterPhoneLink($text, $options = null)
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
     * |email_link - adds link to given email
     *
     * options: {
     *     'classes': 'class1 class2 classN',
     *     'text_before': '<strong>sample</strong>',
     *     'text_after': '<strong>sample</strong>',
     *     'hide_mail': true|false (hide mail-address in text or not)
     * }
     *
     * @param  string $text    text from twig
     * @param  array $options options from twig
     * @return string          complete link in html
     */
    public function filterEmailLink($text, $options = null)
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

        /**
         * generate link
         */
        $link = '<a';

        if ($classes !== null) {
            $link .= ' class="' . $classes . '"';
        }

        $link .= ' href="mailto:' . Html::email($mail) . $query . '">';
        $link .= $textBefore;

        if ($hideMail === false) {
            $link .= $mail;
        }

        $link .= $textAfter;
        $link .= '</a>';

        return $link;
    }

    /**
     * |mediadata - mediadata filter
     *
     * file should be in storage/app/[path], where path-default is "media"
     * for the media-manager
     *
     * @param  string $media filename
     * @param  string $path  relativ path in storage/app
     * @return array|boolean        filedata or false if file not exists
     */
    public function filterMediaData($media, $path = 'media')
    {
        if ($media === null) {
            return false;
        }

        if (strpos(Storage::getMimetype($path . $media), '/')) {
            list($type, $art) = explode('/', Storage::getMimetype($path . $media));
        }

        $data = [
            'size' => Storage::size($path . $media),
            'mime_type' => Storage::getMimetype($path . $media),
            'type' => $type ?? null,
            'art' => $art ?? null,
        ];

        return $data;
    }

    /**
     * |filesize - filesize filter
     *
     * returns filesize of given file
     *
     * @param  string $filename filename
     * @param  string $path      path relative to storage/app, default "media"
     * @return int|boolean           filesize in bytes or false if file not exists
     */
    public function filterFileSize($filename, $path = 'media')
    {
        $size = Storage::size($path . $filename);
        return $size;
    }

    /**
     * |regex_replace - replaces text with a regex with preg_replace
     *
     * @autor   mburghammer
     * @date    2021-01-01T15:20:11+01:00
     * @version 0.0.1
     * @since   0.0.1
     * @param   string      $subject     haystack
     * @param   string      $pattern     needle (regex)
     * @param   string      $replacement replacement
     * @return  string                   replaced string
     */
    public function filterRegexReplace($subject, $pattern, $replacement)
    {
        return preg_replace($pattern, $replacement, $subject);
    }

    /**
     * |strip_html - strips all html from string
     *
     * @autor   mburghammer
     * @date    2021-01-01T15:22:33+01:00
     * @version 0.0.1
     * @since   0.0.1
     * @param   string      $text text to strip html from
     * @return  string            plain text without html
     */
    public function filterStripHtml($text)
    {
        return Html::strip($text);
    }

    /**
     * |truncate_html - truncate a string and repeairs html if needed
     *
     * @autor   mburghammer
     * @date    2021-01-01T15:23:13+01:00
     * @version 0.0.1
     * @since   0.0.1
     * @param   string      $text   string to truncate
     * @param   integer     $lenght length of string without hint
     * @param   string      $hint   hint-string if string is truncated
     * @return  string              truncasted string
     */
    public function filterTruncateHtml($text, $lenght, $hint = '...')
    {
        return Html::limit($text, $lenght, $hint);
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
    public function functionGenerateUid()
    {
        return uniqid();
    }
}
