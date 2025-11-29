<?php

namespace PomatioFramework\ImportExport;

class Domain_Replacer {
    public static function normalize_domain(?string $url): string {
        if (empty($url)) {
            return '';
        }

        $url = trim($url);
        $parsed = parse_url($url);
        $host = $parsed['host'] ?? $url;

        $host = preg_replace('/^www\./i', '', (string) $host);

        return strtolower($host);
    }

    public static function replace_in_array($value, string $source_domain, string $destination_domain) {
        if (empty($source_domain) || empty($destination_domain)) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = self::replace_in_array($item, $source_domain, $destination_domain);
            }

            return $value;
        }

        if (!is_string($value)) {
            return $value;
        }

        return self::replace_in_string($value, $source_domain, $destination_domain);
    }

    public static function replace_in_string(string $content, string $source_domain, string $destination_domain): string {
        if (empty($content) || empty($source_domain) || empty($destination_domain)) {
            return $content;
        }

        $escaped_source = preg_quote($source_domain, '#');

        $patterns = [
            '#https?://' . $escaped_source . '#i' => function (array $match) use ($destination_domain): string {
                $is_https = stripos($match[0], 'https://') === 0;

                return ($is_https ? 'https://' : 'http://') . $destination_domain;
            },
            '#(?<=//)' . $escaped_source . '#i' => $destination_domain,
            '#(?<=^|[^A-Za-z0-9.\-])' . $escaped_source . '(?=[^A-Za-z0-9.\-]|$)#i' => $destination_domain,
        ];

        foreach ($patterns as $pattern => $replacement) {
            $content = is_callable($replacement)
                ? preg_replace_callback($pattern, $replacement, $content)
                : preg_replace($pattern, (string) $replacement, $content);
        }

        return $content;
    }
}