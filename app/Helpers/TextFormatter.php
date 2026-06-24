<?php

namespace App\Helpers;

class TextFormatter
{
    public static function toParagraphs(string $text): string
    {
        $text = trim($text);
        if (empty($text)) {
            return '';
        }

        $text = str_replace("\r\n", "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        $blocks = preg_split("/\n\s*\n/", $text);

        $paragraphs = array_map(function (string $block): string {
            $block = trim($block);
            if (empty($block)) {
                return '';
            }

            $lines = explode("\n", $block);
            $lines = array_map('trim', $lines);
            $lines = array_filter($lines, fn (string $line): bool => $line !== '');

            if (empty($lines)) {
                return '';
            }

            return implode(' ', $lines);
        }, $blocks);

        $paragraphs = array_values(array_filter($paragraphs, fn (string $p): bool => $p !== ''));

        return implode("</p>\n<p>", array_map('e', $paragraphs));
    }

    public static function paragraphHtml(string $text): string
    {
        $inner = static::toParagraphs($text);

        if (empty($inner)) {
            return '';
        }

        return '<p>'.$inner.'</p>';
    }
}
