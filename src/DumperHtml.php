<?php

namespace Attla;

use Symfony\Component\VarDumper\Dumper\HtmlDumper;

class DumperHtml extends HtmlDumper
{
    /**
     * Colour definitions for output
     *
     * @var array
    */
    protected static $themes = [
        'dark' => [
            'default' => 'font:16px Monaco, Consolas, monospace;background-color:#fff;color:#222;line-height:1.2em;'
                . 'font-weight:normal;word-wrap:break-word;white-space:pre-wrap;position:relative;z-index:100000',
            'num' => 'color:#a71d5d',
            'const' => 'color:#795da3',
            'str' => 'color:#df5000',
            'cchr' => 'color:#222',
            'note' => 'color:#a71d5d',
            'ref' => 'color:#a0a0a0',
            'public' => 'color:#795da3',
            'protected' => 'color:#795da3',
            'private' => 'color:#795da3',
            'meta' => 'color:#b729d9',
            'key' => 'color:#df5000',
            'index' => 'color:#a71d5d',
        ],
        'light' => [
            'default' => 'font:16px Monaco, Consolas, monospace;background-color:#fff;color:#222;line-height:1.2em;'
                . 'font-weight:normal;word-wrap:break-word;white-space:pre-wrap;position:relative;z-index:100000',
            'num' => 'color:#a71d5d',
            'const' => 'color:#795da3',
            'str' => 'color:#df5000',
            'cchr' => 'color:#222',
            'note' => 'color:#a71d5d',
            'ref' => 'color:#a0a0a0',
            'public' => 'color:#795da3',
            'protected' => 'color:#795da3',
            'private' => 'color:#795da3',
            'meta' => 'color:#b729d9',
            'key' => 'color:#df5000',
            'index' => 'color:#a71d5d',
        ]
    ];
}
