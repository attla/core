<?php

namespace Attla;

class Minify
{
    /** @var string */
    protected const BLOCK_TAGS_REGEX = 'area|article|aside|base(?:font)?|blockquote|body|'
        . 'canvas|caption|center|col(?:group)?|dd|dir|div|dl|dt|fieldset|figcaption|figure|'
        . 'footer|form|frame(?:set)?|h[1-6]|head|header|hgroup|hr|html|legend|li|link|main|'
        . 'map|menu|meta|nav|ol|opt(?:group|ion)|output|p|param|section|table|tbody|thead|'
        . 'td|th|tr|tfoot|title|ul|video';

    /** @var string */
    protected const INLINE_TAGS_REGEX = 'a|abbr|acronym|b|bdo|big|br|button|cite|dfn|em|i|'
        . 'img|input|kbd|label|map|object|q|samp|select|small|span|strong|sub|sup|time|tt|var';

    /** @var array */
    protected static $preservedContents = [];

    /**
     * Minify an Blade page
     *
     * @param string $blade
     * @param array $opt
     * @return string
     */
    public static function compile($blade, $opt = [])
    {
        $html = $blade;
        $replacementHash = 'MINIFYHTML' . md5(uniqid(mt_rand(), true));

        $options = [
            'disable_comments' => $opt['disable_comments'] ?? true,
            'preserve_conditional_comments' => $opt['preserve_conditional_comments'] ?? true
        ];

        if ($options['disable_comments']) {
            $html = preg_replace(
                empty($options['preserve_conditional_comments']) ?
                    '/<!--(.|\s)*?-->/' : '/<!--((?![\[]{1,1}).|\s)*?-->/',
                '',
                $html
            );
        }

        // minify javascript if have it
        if (strpos($html, '</script>') !== false) {
            $html = preg_replace_callback('#<script(.*?)>(.*?)</script>#is', function ($matches) {
                return '<script' . $matches[1] . '>' . self::minifyJs($matches[2]) . '</script>';
            }, $html);
        }

        // preserves contents of special elements
        $html = self::preserve($html, [
            '/<textarea(?:(?:.|\n)*?)>((?:.|\n)*?)<\/textarea>/',
            '/placeholder="((?:.|\n)*?)"/',
        ]);

        // trim each line
        $html = preg_replace('/\\s+/u', ' ', $html);

        // trim string in inline tags
        $html = preg_replace(
            '/>\\s?([^<]|(?!%' . $replacementHash . '[0-9]+%)\\S+)\\s?<\\//iu',
            '>$1</',
            $html
        );

        // remove ws around block/undisplayed elements
        $html = preg_replace('/\\s+(<\\/?(?:' . self::BLOCK_TAGS_REGEX . ')\\b[^>]*>)/iu', '$1', $html);

        // remove ws before and after a single string in tags
        $html = preg_replace(
            '/<([a-zA-Z](?:[a-zA-Z-]+)?)([^>]+)?>\\s([^\\s](?:[^<]+)?)<\\/\\1>/iu',
            '<$1$2>$3</$1>',
            $html
        );
        $html = preg_replace(
            '/<([a-zA-Z](?:[a-zA-Z-]+)?)([^>]+)?>((?:[^<]+[^\\s])?)\\s<\\/\\1>/iu',
            '<$1$2>$3</$1>',
            $html
        );

        // remove ws outside of block/undisplayed elements with placeholders
        $html = preg_replace(
            '/(<\\/?(?:' . self::BLOCK_TAGS_REGEX . ')\\b[^>]*>)(?:\\s(?:\\s*))?'
            . '(%' . $replacementHash . '[0-9]+%)(?:\\s(?:\\s*))?/iu',
            '$1$2',
            $html
        );

        // remove ws between block and inline tags
        $html = preg_replace(
            '/(<\\/?(?:' . self::BLOCK_TAGS_REGEX . ')\\b[^>]*>)'
            . '\\s+(<\\/?(?:' . self::INLINE_TAGS_REGEX . ')\\b[^>]*>)/iu',
            '$1$2',
            $html
        );

        // remove ws at the front of opening inline tags (ex. <a>hello <span>world)
        $html = preg_replace(
            '/(<(?:' . self::INLINE_TAGS_REGEX . ')\\b[^>]*>)'
            . '\\s([^\\s][^<]+[\\s]?)?(<(?:' . self::INLINE_TAGS_REGEX . ')\\b[^>]*>)/iu',
            '$1$2$3',
            $html
        );

        // remove ws closing adjacent inline tags (ex. </span></label>)
        $html = preg_replace(
            '/(<\\/(?:' . self::INLINE_TAGS_REGEX . ')>)'
            . '\\s+(<\\/(?:' . self::INLINE_TAGS_REGEX . ')>)/iu',
            '$1$2',
            $html
        );

        // restore preserved special contents
        $html = self::restorePreservedContents($html);

        return $html;
    }

    /**
     * Minify a javascript block
     *
     * @param string $script
     * @return string
     */
    private static function minifyJs($script)
    {
        if (trim($script) === '') {
            return '';
        }

        $pattern = [
            '/\/\*.*?\*\//ms' => '',
            '/\s+\/\/[^\n]+/m' => '',
            // another way to remove comments //
            // '/\/\*[\s\S]*?\*\/|([^\\:]|^)\/\/.*$/' => '',
            // Remove comment(s)
            '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)'
                . '(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#' => '$1',
            // Remove white-space(s) outside the string and regex
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)'
                . '[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s' => '$1$2',
            // Remove the last semicolon
            '#;+\}#' => '}',
            // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
            '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i' => '$1$3',
            // --ibid. From `foo['bar']` to `foo.bar`
            // '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i' => '$1.$3'
        ];

        return preg_replace(array_keys($pattern), array_values($pattern), $script);
    }

    /**
     * Preserves element content
     *
     * @param string $html
     * @param array $patterns
     * @return string
     */
    private static function preserve($html, $patterns)
    {
        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $html, $match);
            if ($match) {
                foreach ($match[1] as $content) {
                    self::$preservedContents[] = $content;
                    $lastPos = count(self::$preservedContents) - 1;
                    $html = str_replace($content, "[PRESERVED$lastPos]", $html);
                }
            }
        }

        return $html;
    }

    /**
     * Replaces all stored preserves contents
     *
     * @param string $html
     * @return string
     */
    private static function restorePreservedContents($html)
    {
        foreach (self::$preservedContents as $key => $content) {
            $html = str_replace("[PRESERVED$key]", $content, $html);
        }
        return $html;
    }
}
