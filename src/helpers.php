<?php

declare(strict_types=1);

/**
 * Small global helpers used by the templates.
 */

/** Escapes output for HTML (prevents XSS — the old pages echoed raw database values). */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Formats an amount stored in cents, e.g. 18900 → "189,00 DT". */
function money(int $cents): string
{
    return number_format($cents / 100, 2, ',', ' ') . ' DT';
}

function url(string $path = '/', array $query = []): string
{
    $query = array_filter($query, static fn ($v) => $v !== null && $v !== '');
    return $path . ($query ? '?' . http_build_query($query) : '');
}

/** Only allows redirects inside the site (blocks open redirects such as //evil.com). */
function safe_path(?string $path, string $fallback = '/'): string
{
    if (!is_string($path)) {
        return $fallback;
    }
    $parsed = parse_url($path);
    if ($parsed === false || isset($parsed['scheme']) || isset($parsed['host']) || !str_starts_with($path, '/') || str_starts_with($path, '//')) {
        return $fallback;
    }
    return $path;
}

function redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

/** One-time messages displayed after a redirect. */
function flash(?string $type = null, ?string $message = null): array
{
    if ($type !== null) {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
        return [];
    }
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/** Previous form input after a validation error. */
function old(string $key, string $default = ''): string
{
    return (string) ($_SESSION['old'][$key] ?? $default);
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(App\Csrf::token()) . '">';
}

function product_image(array $product): string
{
    if (!empty($product['image'])) {
        return '/uploads/' . rawurlencode($product['image']);
    }
    return '/assets/img/' . ($product['category_icon'] ?? 'box') . '.svg';
}

const ORDER_STATUSES = [
    'pending' => 'En attente',
    'paid' => 'Payée',
    'shipped' => 'Expédiée',
    'delivered' => 'Livrée',
    'cancelled' => 'Annulée',
];

function status_label(string $status): string
{
    return ORDER_STATUSES[$status] ?? $status;
}
