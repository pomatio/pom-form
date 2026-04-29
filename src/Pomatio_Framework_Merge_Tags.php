<?php

namespace PomatioFramework;

class Pomatio_Framework_Merge_Tags {
    public static function tokens(string $content): array {
        $tokens = [];
        $offset = 0;
        $length = strlen($content);

        while ($offset < $length) {
            $start = strpos($content, '{{', $offset);
            if ($start === false) {
                break;
            }

            $end = self::find_closing_position($content, $start + 2);
            if ($end === null) {
                break;
            }

            $full = substr($content, $start, $end + 2 - $start);
            $inner = trim(substr($content, $start + 2, $end - $start - 2));
            $parsed = self::parse_token($inner);

            $tokens[] = [
                'full' => $full,
                'inner' => $inner,
                'name' => $parsed['name'],
                'attrs' => $parsed['attrs'],
                'attrs_raw' => $parsed['attrs_raw'],
                'offset' => $start,
                'length' => strlen($full),
            ];

            $offset = $end + 2;
        }

        return $tokens;
    }

    public static function variables(string $content): array {
        $variables = [];

        foreach (self::tokens($content) as $token) {
            if (($token['inner'] ?? '') !== '') {
                $variables[] = (string) $token['inner'];
            }
        }

        return array_values(array_unique($variables));
    }

    public static function parse_token(string $inner, array $attribute_options = []): array {
        $inner = trim($inner);
        if ($inner === '') {
            return ['name' => '', 'attrs' => [], 'attrs_raw' => ''];
        }

        $space_position = strcspn($inner, " \t\r\n");
        if ($space_position >= strlen($inner)) {
            return ['name' => $inner, 'attrs' => [], 'attrs_raw' => ''];
        }

        $name = substr($inner, 0, $space_position);
        $attrs_raw = trim(substr($inner, $space_position + 1));

        return [
            'name' => $name,
            'attrs' => self::attributes($attrs_raw, $attribute_options),
            'attrs_raw' => $attrs_raw,
        ];
    }

    public static function attributes(string $attribute_string, array $options = []): array {
        $attributes = [];
        $length = strlen($attribute_string);
        $index = 0;
        $resolve_callback = $options['resolve_translatable_callback'] ?? null;

        while ($index < $length) {
            self::skip_whitespace($attribute_string, $index);
            if ($index >= $length) {
                break;
            }

            $key_start = $index;
            while ($index < $length && !ctype_space($attribute_string[$index]) && $attribute_string[$index] !== '=') {
                $index++;
            }

            $key = trim(substr($attribute_string, $key_start, $index - $key_start));
            if ($key === '') {
                $index++;
                continue;
            }

            self::skip_whitespace($attribute_string, $index);
            if ($index >= $length || $attribute_string[$index] !== '=') {
                $attributes[$key] = '';
                continue;
            }

            $index++;
            self::skip_whitespace($attribute_string, $index);
            $value = self::read_attribute_value($attribute_string, $index);

            if (is_callable($resolve_callback)) {
                $value = self::resolve_translatable_tags($value, $resolve_callback);
            }

            $attributes[$key] = $value;
        }

        return $attributes;
    }

    public static function resolve_translatable_tags(string $value, callable $callback): string {
        if (strpos($value, '{{') === false) {
            return $value;
        }

        $result = $value;
        foreach (self::tokens($value) as $token) {
            if (($token['name'] ?? '') !== 'translatable_string') {
                continue;
            }

            $text = isset($token['attrs']['text']) ? (string) $token['attrs']['text'] : '';
            $result = str_replace((string) ($token['full'] ?? ''), (string) $callback($text), $result);
        }

        return $result;
    }

    private static function find_closing_position(string $content, int $offset): ?int {
        $length = strlen($content);
        $quote = '';
        $literal = false;

        for ($index = $offset; $index < $length; $index++) {
            $pair = substr($content, $index, 2);

            if ($literal) {
                if ($pair === '%%' && !self::is_escaped($content, $index)) {
                    $literal = false;
                    $index++;
                }
                continue;
            }

            if ($quote !== '') {
                if ($pair === '{{') {
                    $nested_end = self::find_closing_position($content, $index + 2);
                    if ($nested_end === null) {
                        return null;
                    }
                    $index = $nested_end + 1;
                    continue;
                }

                if ($content[$index] === $quote && !self::is_escaped($content, $index)) {
                    $quote = '';
                }
                continue;
            }

            if ($pair === '%%') {
                $literal = true;
                $index++;
                continue;
            }

            if ($content[$index] === '"' || $content[$index] === "'") {
                $quote = $content[$index];
                continue;
            }

            if ($pair === '{{') {
                $nested_end = self::find_closing_position($content, $index + 2);
                if ($nested_end === null) {
                    return null;
                }
                $index = $nested_end + 1;
                continue;
            }

            if ($pair === '}}') {
                return $index;
            }
        }

        return null;
    }

    private static function read_attribute_value(string $attribute_string, int &$index): string {
        $length = strlen($attribute_string);
        if ($index >= $length) {
            return '';
        }

        $char = $attribute_string[$index];
        if ($char === '"' || $char === "'") {
            return self::read_quoted_attribute_value($attribute_string, $index, $char);
        }

        if (substr($attribute_string, $index, 2) === '%%') {
            return self::read_literal_attribute_value($attribute_string, $index);
        }

        $value = '';
        while ($index < $length && !ctype_space($attribute_string[$index])) {
            $pair = substr($attribute_string, $index, 2);
            if ($pair === '{{') {
                $nested_end = self::find_closing_position($attribute_string, $index + 2);
                if ($nested_end === null) {
                    break;
                }
                $value .= substr($attribute_string, $index, $nested_end + 2 - $index);
                $index = $nested_end + 2;
                continue;
            }

            if ($pair === '%%') {
                $value .= self::read_literal_attribute_value($attribute_string, $index);
                continue;
            }

            $value .= $attribute_string[$index];
            $index++;
        }

        return $value;
    }

    private static function read_quoted_attribute_value(string $attribute_string, int &$index, string $quote): string {
        $length = strlen($attribute_string);
        $index++;
        $value = '';

        while ($index < $length) {
            $pair = substr($attribute_string, $index, 2);
            if ($pair === '{{') {
                $nested_end = self::find_closing_position($attribute_string, $index + 2);
                if ($nested_end === null) {
                    break;
                }
                $value .= substr($attribute_string, $index, $nested_end + 2 - $index);
                $index = $nested_end + 2;
                continue;
            }

            if ($attribute_string[$index] === $quote && !self::is_escaped($attribute_string, $index)) {
                $index++;
                break;
            }

            $value .= $attribute_string[$index];
            $index++;
        }

        return stripcslashes($value);
    }

    private static function read_literal_attribute_value(string $attribute_string, int &$index): string {
        $length = strlen($attribute_string);
        $index += 2;
        $value = '';

        while ($index < $length) {
            if (substr($attribute_string, $index, 2) === '%%' && !self::is_escaped($attribute_string, $index)) {
                $index += 2;
                break;
            }

            $value .= $attribute_string[$index];
            $index++;
        }

        return str_replace('\%%', '%%', $value);
    }

    private static function skip_whitespace(string $attribute_string, int &$index): void {
        $length = strlen($attribute_string);
        while ($index < $length && ctype_space($attribute_string[$index])) {
            $index++;
        }
    }

    private static function is_escaped(string $content, int $position): bool {
        $slashes = 0;
        for ($index = $position - 1; $index >= 0 && $content[$index] === '\\'; $index--) {
            $slashes++;
        }

        return ($slashes % 2) === 1;
    }
}
