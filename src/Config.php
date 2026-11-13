<?php

declare(strict_types=1);

namespace App;

/**
 * Reads configuration from environment variables, optionally loaded from a
 * `.env` file at the project root. No secret is ever stored in the code.
 */
final class Config
{
    private static bool $loaded = false;

    public static function load(string $root): void
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;
        $file = $root . '/.env';
        if (!is_file($file)) {
            return;
        }
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = array_map('trim', explode('=', $line, 2));
            if (getenv($key) === false) {
                putenv("$key=" . trim($value, "\"'"));
            }
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);
        return $value === false || $value === '' ? $default : $value;
    }
}
