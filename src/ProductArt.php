<?php

declare(strict_types=1);

namespace App;

/**
 * Generates a product illustration (SVG) from the product type, brand and id,
 * so every product without a photo still gets a distinct, polished visual.
 */
final class ProductArt
{
    /** Background palettes per category icon: [light, mid, accent]. */
    private const PALETTES = [
        'router' => [['#EEF4FF', '#D6E4FF', '#2563EB'], ['#F0F7FF', '#DCEBFF', '#1D4ED8'], ['#F3F0FF', '#E2DBFF', '#4F46E5']],
        'switch' => [['#ECFDF5', '#CFF5E4', '#059669'], ['#F0FDFA', '#CCF3EC', '#0D9488'], ['#F1F8F2', '#D7EEDB', '#15803D']],
        'wifi' => [['#F5F3FF', '#E6DFFF', '#7C3AED'], ['#FDF4FF', '#F5DDFB', '#A21CAF'], ['#EEF2FF', '#DDE3FF', '#4338CA']],
        'cable' => [['#FFF7ED', '#FFE6CC', '#EA580C'], ['#FFFBEB', '#FDEDC2', '#D97706'], ['#FEF2F2', '#FCDADA', '#DC2626']],
        'shield' => [['#FEF2F2', '#FCDEDE', '#DC2626'], ['#F8FAFC', '#E2E8F0', '#0F172A'], ['#FFF1F2', '#FBD8DD', '#BE123C']],
        'box' => [['#F8FAFC', '#E2E8F0', '#475569'], ['#F1F5F9', '#DCE3EC', '#334155'], ['#F8FAFC', '#E5E7EB', '#1F2937']],
    ];

    public static function svg(array $product): string
    {
        $icon = $product['category_icon'] ?? 'box';
        $palettes = self::PALETTES[$icon] ?? self::PALETTES['box'];
        [$light, $mid, $accent] = $palettes[((int) ($product['id'] ?? 0)) % count($palettes)];
        $brand = mb_strtoupper(mb_substr((string) ($product['brand'] ?? ''), 0, 12));
        $name = mb_strtolower((string) ($product['name'] ?? ''));

        $device = match (true) {
            str_contains($name, 'caméra') || str_contains($name, 'camera') => self::camera($accent, $brand),
            str_contains($name, 'mesh') => self::mesh($accent, $brand),
            str_contains($name, 'sfp') => self::sfp($accent, $brand),
            str_contains($name, 'bobine') => self::spool($accent, $brand),
            str_contains($name, 'répéteur') || str_contains($name, 'repeteur') => self::extender($accent, $brand),
            $icon === 'router' => self::router($accent, $brand, str_contains($name, '4g')),
            $icon === 'switch' => self::switch($accent, $brand, str_contains($name, '24') ? 12 : (str_contains($name, '16') ? 8 : 6)),
            $icon === 'wifi' => self::accessPoint($accent, $brand),
            $icon === 'cable' => self::cable($accent),
            $icon === 'shield' => self::firewall($accent, $brand),
            default => self::box($accent),
        };

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 300" role="img">
<defs>
  <radialGradient id="bg" cx="50%" cy="38%" r="75%"><stop offset="0" stop-color="#ffffff"/><stop offset=".55" stop-color="$light"/><stop offset="1" stop-color="$mid"/></radialGradient>
  <linearGradient id="body" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#3a4357"/><stop offset=".5" stop-color="#1f2533"/><stop offset="1" stop-color="#11151e"/></linearGradient>
  <linearGradient id="bodyLight" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffffff"/><stop offset="1" stop-color="#dfe4ec"/></linearGradient>
  <linearGradient id="accent" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="$accent" stop-opacity=".85"/><stop offset="1" stop-color="$accent"/></linearGradient>
  <filter id="shadow" x="-30%" y="-30%" width="160%" height="170%"><feGaussianBlur stdDeviation="9"/></filter>
  <filter id="glow" x="-200%" y="-200%" width="500%" height="500%"><feGaussianBlur stdDeviation="2.2"/></filter>
</defs>
<rect width="400" height="300" fill="url(#bg)"/>
<circle cx="330" cy="52" r="90" fill="$accent" opacity=".06"/>
<circle cx="52" cy="262" r="70" fill="$accent" opacity=".05"/>
$device
</svg>
SVG;
    }

    private static function shadow(int $cx, int $cy, int $rx, int $ry = 12): string
    {
        return "<ellipse cx=\"$cx\" cy=\"$cy\" rx=\"$rx\" ry=\"$ry\" fill=\"#0f172a\" opacity=\".22\" filter=\"url(#shadow)\"/>";
    }

    private static function led(int $x, int $y, string $color): string
    {
        return "<circle cx=\"$x\" cy=\"$y\" r=\"5\" fill=\"$color\" filter=\"url(#glow)\" opacity=\".9\"/><circle cx=\"$x\" cy=\"$y\" r=\"2.6\" fill=\"#fff\"/>";
    }

    private static function label(string $brand, int $x, int $y, string $color = '#cbd5e1', int $size = 11): string
    {
        $brand = htmlspecialchars($brand, ENT_XML1);
        return "<text x=\"$x\" y=\"$y\" font-family=\"Arial, Helvetica, sans-serif\" font-size=\"$size\" font-weight=\"700\" letter-spacing=\"2\" fill=\"$color\">$brand</text>";
    }

    private static function router(string $accent, string $brand, bool $lte): string
    {
        $ports = '';
        for ($i = 0; $i < 5; $i++) {
            $x = 196 + $i * 22;
            $ports .= "<rect x=\"$x\" y=\"184\" width=\"16\" height=\"12\" rx=\"2\" fill=\"#0b0e14\" stroke=\"#4b5568\"/><rect x=\"" . ($x + 4) . "\" y=\"186\" width=\"8\" height=\"3\" fill=\"#64748b\"/>";
        }
        $sim = $lte ? '<rect x="118" y="186" width="34" height="10" rx="2" fill="#0b0e14" stroke="#4b5568"/><text x="121" y="194" font-family="Arial" font-size="7" fill="#94a3b8">SIM</text>' : '';
        return self::shadow(200, 236, 135)
            . "<rect x=\"112\" y=\"58\" width=\"9\" height=\"98\" rx=\"4.5\" fill=\"url(#body)\" transform=\"rotate(-12 116 156)\"/>"
            . "<rect x=\"279\" y=\"58\" width=\"9\" height=\"98\" rx=\"4.5\" fill=\"url(#body)\" transform=\"rotate(12 284 156)\"/>"
            . "<rect x=\"196\" y=\"72\" width=\"8\" height=\"84\" rx=\"4\" fill=\"url(#body)\"/>"
            . '<path d="M78 150 Q78 138 92 138 H308 Q322 138 322 150 L332 214 Q334 226 320 226 H80 Q66 226 68 214 Z" fill="url(#body)"/>'
            . '<path d="M84 148 Q86 142 94 142 H306 Q314 142 316 148 L318 158 H82 Z" fill="#ffffff" opacity=".08"/>'
            . "<rect x=\"92\" y=\"166\" width=\"216\" height=\"3\" rx=\"1.5\" fill=\"$accent\" opacity=\".7\"/>"
            . self::led(104, 190, '#4ade80') . self::led(124, 190, '#4ade80') . self::led(144, 190, '#facc15')
            . ($lte ? '' : self::led(164, 190, $accent))
            . $sim . $ports . self::label($brand, 92, 214, '#94a3b8', 10);
    }

    private static function switch(string $accent, string $brand, int $columns): string
    {
        $ports = '';
        $width = 26 * $columns;
        $start = 200 - (int) ($width / 2) + 14;
        for ($row = 0; $row < 2; $row++) {
            for ($i = 0; $i < $columns; $i++) {
                $x = $start + $i * 26;
                $y = 150 + $row * 26;
                $ports .= "<rect x=\"$x\" y=\"$y\" width=\"20\" height=\"18\" rx=\"2\" fill=\"#0b0e14\" stroke=\"#3f4a5e\"/>"
                    . "<rect x=\"" . ($x + 6) . "\" y=\"" . ($y + 2) . "\" width=\"8\" height=\"4\" fill=\"#475569\"/>";
                if (($i + $row) % 3 !== 2) {
                    $ports .= "<circle cx=\"" . ($x + 17) . "\" cy=\"" . ($y + 15) . "\" r=\"1.8\" fill=\"#4ade80\"/>";
                }
            }
        }
        return self::shadow(200, 222, 170)
            . '<rect x="34" y="122" width="332" height="92" rx="10" fill="url(#body)"/>'
            . '<rect x="34" y="122" width="332" height="16" rx="8" fill="#ffffff" opacity=".07"/>'
            . "<rect x=\"34\" y=\"204\" width=\"332\" height=\"4\" fill=\"$accent\" opacity=\".8\"/>"
            . '<rect x="18" y="128" width="16" height="78" rx="3" fill="#2a3242"/><rect x="366" y="128" width="16" height="78" rx="3" fill="#2a3242"/>'
            . '<circle cx="26" cy="140" r="3" fill="#0b0e14"/><circle cx="26" cy="194" r="3" fill="#0b0e14"/><circle cx="374" cy="140" r="3" fill="#0b0e14"/><circle cx="374" cy="194" r="3" fill="#0b0e14"/>'
            . $ports . self::label($brand, 46, 140, '#94a3b8', 9);
    }

    private static function accessPoint(string $accent, string $brand): string
    {
        return self::shadow(200, 232, 120, 14)
            . '<ellipse cx="200" cy="196" rx="116" ry="34" fill="#cbd2dd"/>'
            . '<ellipse cx="200" cy="186" rx="116" ry="34" fill="url(#bodyLight)"/>'
            . '<ellipse cx="200" cy="182" rx="98" ry="26" fill="#ffffff"/>'
            . "<ellipse cx=\"200\" cy=\"182\" rx=\"34\" ry=\"9\" fill=\"none\" stroke=\"$accent\" stroke-width=\"5\" filter=\"url(#glow)\"/>"
            . "<ellipse cx=\"200\" cy=\"182\" rx=\"34\" ry=\"9\" fill=\"none\" stroke=\"$accent\" stroke-width=\"2\"/>"
            . "<path d=\"M150 104 a70 70 0 0 1 100 0\" fill=\"none\" stroke=\"$accent\" stroke-width=\"9\" stroke-linecap=\"round\" opacity=\".85\"/>"
            . "<path d=\"M126 78 a104 104 0 0 1 148 0\" fill=\"none\" stroke=\"$accent\" stroke-width=\"9\" stroke-linecap=\"round\" opacity=\".45\"/>"
            . "<path d=\"M172 128 a36 36 0 0 1 56 0\" fill=\"none\" stroke=\"$accent\" stroke-width=\"9\" stroke-linecap=\"round\"/>"
            . self::label($brand, 170, 214, '#94a3b8', 9);
    }

    private static function mesh(string $accent, string $brand): string
    {
        $unit = static function (int $x, int $h) use ($accent): string {
            $top = 220 - $h;
            return "<rect x=\"" . ($x - 32) . "\" y=\"$top\" width=\"64\" height=\"$h\" rx=\"22\" fill=\"url(#bodyLight)\" stroke=\"#d6dbe4\"/>"
                . "<rect x=\"" . ($x - 32) . "\" y=\"$top\" width=\"64\" height=\"16\" rx=\"8\" fill=\"#fff\"/>"
                . "<circle cx=\"$x\" cy=\"" . ($top + 30) . "\" r=\"5\" fill=\"$accent\" filter=\"url(#glow)\"/><circle cx=\"$x\" cy=\"" . ($top + 30) . "\" r=\"2.5\" fill=\"#fff\"/>";
        };
        return self::shadow(200, 230, 150)
            . $unit(118, 96) . $unit(282, 96) . $unit(200, 130)
            . "<path d=\"M150 120 Q200 70 250 120\" fill=\"none\" stroke=\"$accent\" stroke-width=\"4\" stroke-dasharray=\"2 9\" stroke-linecap=\"round\"/>"
            . self::label($brand, 172, 206, '#94a3b8', 9);
    }

    private static function extender(string $accent, string $brand): string
    {
        return self::shadow(200, 238, 70)
            . '<rect x="140" y="70" width="120" height="156" rx="26" fill="url(#bodyLight)" stroke="#d6dbe4"/>'
            . '<rect x="186" y="226" width="10" height="16" fill="#9aa3b2"/><rect x="204" y="226" width="10" height="16" fill="#9aa3b2"/>'
            . "<path d=\"M176 130 a34 34 0 0 1 48 0\" fill=\"none\" stroke=\"$accent\" stroke-width=\"7\" stroke-linecap=\"round\"/>"
            . "<path d=\"M164 112 a52 52 0 0 1 72 0\" fill=\"none\" stroke=\"$accent\" stroke-width=\"7\" stroke-linecap=\"round\" opacity=\".5\"/>"
            . "<circle cx=\"200\" cy=\"148\" r=\"6\" fill=\"$accent\"/>"
            . self::led(200, 196, '#4ade80') . self::label($brand, 172, 92, '#94a3b8', 9);
    }

    private static function cable(string $accent): string
    {
        return self::shadow(200, 240, 150)
            . "<path d=\"M88 196 C 88 92, 312 92, 312 168 S 150 236, 150 160 S 260 120, 266 196\" fill=\"none\" stroke=\"#1e293b\" stroke-width=\"15\" stroke-linecap=\"round\"/>"
            . "<path d=\"M88 196 C 88 92, 312 92, 312 168 S 150 236, 150 160 S 260 120, 266 196\" fill=\"none\" stroke=\"$accent\" stroke-width=\"11\" stroke-linecap=\"round\"/>"
            . "<path d=\"M88 196 C 88 92, 312 92, 312 168\" fill=\"none\" stroke=\"#ffffff\" stroke-width=\"3\" stroke-linecap=\"round\" opacity=\".35\"/>"
            . self::plug(70, 196) . self::plug(248, 196);
    }

    private static function plug(int $x, int $y): string
    {
        $pins = '';
        for ($i = 0; $i < 8; $i++) {
            $pins .= '<rect x="' . ($x + 6 + $i * 3.6) . '" y="' . ($y + 30) . '" width="2" height="7" fill="#d4a017"/>';
        }
        return "<rect x=\"$x\" y=\"$y\" width=\"38\" height=\"30\" rx=\"5\" fill=\"#334155\"/>"
            . '<rect x="' . ($x + 3) . "\" y=\"" . ($y + 26) . "\" width=\"32\" height=\"16\" rx=\"2\" fill=\"#e2e8f0\" opacity=\".9\"/>" . $pins;
    }

    private static function spool(string $accent, string $brand): string
    {
        $turns = '';
        for ($i = 0; $i < 7; $i++) {
            $y = 112 + $i * 14;
            $turns .= "<path d=\"M128 $y Q200 " . ($y + 9) . " 272 $y\" fill=\"none\" stroke=\"$accent\" stroke-width=\"11\" stroke-linecap=\"round\"/>"
                . "<path d=\"M128 " . ($y - 3) . " Q200 " . ($y + 5) . " 272 " . ($y - 3) . "\" fill=\"none\" stroke=\"#fff\" stroke-width=\"2\" opacity=\".3\"/>";
        }
        return self::shadow(200, 248, 120)
            . '<ellipse cx="200" cy="96" rx="108" ry="26" fill="#334155"/><ellipse cx="200" cy="96" rx="34" ry="9" fill="#0f172a"/>'
            . $turns
            . '<ellipse cx="200" cy="216" rx="108" ry="26" fill="url(#body)"/>'
            . self::label($brand, 168, 222, '#94a3b8', 10);
    }

    private static function sfp(string $accent, string $brand): string
    {
        return self::shadow(200, 226, 140)
            . '<path d="M70 132 L300 132 L330 150 L330 196 L70 196 Z" fill="#cbd5e1"/>'
            . '<path d="M70 132 L300 132 L330 150 L100 150 Z" fill="#e2e8f0"/>'
            . '<rect x="70" y="150" width="260" height="46" fill="url(#bodyLight)" stroke="#94a3b8"/>'
            . "<rect x=\"64\" y=\"146\" width=\"20\" height=\"54\" rx=\"3\" fill=\"$accent\"/>"
            . '<rect x="300" y="160" width="26" height="12" rx="2" fill="#0f172a"/><rect x="300" y="176" width="26" height="12" rx="2" fill="#0f172a"/>'
            . self::label($brand . ' 10G', 112, 178, '#475569', 12);
    }

    private static function firewall(string $accent, string $brand): string
    {
        $ports = '';
        for ($i = 0; $i < 6; $i++) {
            $x = 196 + $i * 22;
            $ports .= "<rect x=\"$x\" y=\"176\" width=\"16\" height=\"12\" rx=\"2\" fill=\"#0b0e14\" stroke=\"#4b5568\"/>";
        }
        return self::shadow(200, 232, 160)
            . '<rect x="44" y="134" width="312" height="80" rx="12" fill="url(#body)"/>'
            . '<rect x="44" y="134" width="312" height="14" rx="7" fill="#ffffff" opacity=".07"/>'
            . "<path d=\"M110 152 L132 160 V176 C132 190 122 198 110 202 C98 198 88 190 88 176 V160 Z\" fill=\"$accent\"/>"
            . '<rect x="102" y="172" width="16" height="13" rx="2" fill="#fff"/><path d="M105 172 v-4 a5 5 0 0 1 10 0 v4" fill="none" stroke="#fff" stroke-width="2.5"/>'
            . self::led(150, 182, '#4ade80') . self::led(170, 182, $accent) . $ports
            . self::label($brand, 150, 160, '#94a3b8', 10);
    }

    private static function camera(string $accent, string $brand): string
    {
        return self::shadow(200, 240, 110)
            . '<rect x="250" y="70" width="70" height="26" rx="6" fill="#cbd5e1"/><rect x="276" y="90" width="18" height="40" fill="#cbd5e1"/>'
            . '<path d="M86 118 H262 Q290 118 290 146 V170 Q290 198 262 198 H86 Z" fill="url(#bodyLight)" stroke="#d1d7e0"/>'
            . '<rect x="70" y="110" width="40" height="96" rx="10" fill="#1f2533"/>'
            . '<circle cx="90" cy="158" r="26" fill="#0b0e14"/>'
            . "<circle cx=\"90\" cy=\"158\" r=\"15\" fill=\"$accent\" opacity=\".75\"/><circle cx=\"84\" cy=\"152\" r=\"5\" fill=\"#fff\" opacity=\".7\"/>"
            . self::led(270, 180, '#ef4444') . self::label($brand, 140, 164, '#64748b', 11);
    }

    private static function box(string $accent): string
    {
        return self::shadow(200, 240, 110)
            . '<path d="M200 70 L300 118 V206 L200 254 L100 206 V118 Z" fill="#475569"/>'
            . "<path d=\"M200 70 L300 118 L200 166 L100 118 Z\" fill=\"$accent\"/>"
            . '<path d="M200 166 V254" stroke="#334155" stroke-width="4"/>';
    }
}
