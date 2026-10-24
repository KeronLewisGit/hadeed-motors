<?php

if (! function_exists('hadeed')) {
    function hadeed(string $key, mixed $default = null): mixed
    {
        return config('hadeed.'.$key, $default);
    }
}

if (! function_exists('us_digits')) {
    /** E.164 digits for a North American (+1, incl. Antigua 268) number written any common way, or null. */
    function us_digits(?string $number): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number) ?? '';
        if (strlen($digits) === 10) {
            $digits = '1'.$digits;
        }

        return strlen($digits) === 11 && $digits[0] === '1' ? $digits : null;
    }
}

if (! function_exists('tel_href')) {
    function tel_href(?string $number): string
    {
        $digits = us_digits($number);

        return $digits ? 'tel:+'.$digits : 'tel:'.preg_replace('/[^\d+]/', '', (string) $number);
    }
}

if (! function_exists('sms_href')) {
    function sms_href(?string $number): string
    {
        return 'sms:+'.(us_digits($number) ?? preg_replace('/\D+/', '', (string) $number));
    }
}

if (! function_exists('format_phone')) {
    /** 268-481-2500 for +1 numbers (the way Antiguans write them), the input unchanged otherwise. */
    function format_phone(?string $number): string
    {
        $digits = us_digits($number);
        if (! $digits) {
            return (string) $number;
        }

        return sprintf('%s-%s-%s', substr($digits, 1, 3), substr($digits, 4, 3), substr($digits, 7));
    }
}

if (! function_exists('input_string')) {
    /** A request value as a string. Arrays (?type[]=x) and other junk become ''. */
    function input_string(mixed $value): string
    {
        return is_scalar($value) ? trim((string) $value) : '';
    }
}

if (! function_exists('lines_to_array')) {
    /**
     * Textarea with one item per line, bullets optional, to a clean list.
     *
     * @return list<string>
     */
    function lines_to_array(?string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', (string) $text) ?: [];
        $clean = array_map(fn (string $l) => trim((string) preg_replace('/^(?:[-*]|•)\s*/u', '', $l)), $lines);

        return array_values(array_filter($clean, fn (string $l) => $l !== ''));
    }
}

if (! function_exists('compact_number')) {
    /** 1,284 → 1.3K, used on stat tiles. */
    function compact_number(int|float $n): string
    {
        return match (true) {
            abs($n) >= 1_000_000 => round($n / 1_000_000, 1).'M',
            abs($n) >= 10_000 => round($n / 1_000).'K',
            default => number_format($n),
        };
    }
}

if (! function_exists('photo')) {
    /**
     * Responsive <img> attributes for a photo in public/img (WebP at 480/960/1920 wide).
     *
     * @return array{src: string, srcset: string, width: int, height: int}
     */
    function photo(string $key): array
    {
        $meta = config('hadeed.photos.'.$key) ?? ['w' => 1920, 'h' => 1280];
        $url = fn (int $w) => asset("img/{$key}-{$w}.webp");

        return [
            'src' => $url(960),
            'srcset' => $url(480).' 480w, '.$url(960).' 960w, '.$url(1920).' 1920w',
            'width' => (int) $meta['w'],
            'height' => (int) $meta['h'],
        ];
    }
}

if (! function_exists('wa_href')) {
    /** WhatsApp click-to-chat link, optionally with a prefilled message. */
    function wa_href(?string $number = null, ?string $text = null): string
    {
        $digits = us_digits($number ?? (string) hadeed('whatsapp')) ?? preg_replace('/\D+/', '', (string) $number);

        return 'https://wa.me/'.$digits.($text ? '?text='.rawurlencode($text) : '');
    }
}

if (! function_exists('money')) {
    /** EC$45,900 – whole dollars, the way car prices are quoted. */
    function money(int|float|null $amount, bool $cents = false): string
    {
        if ($amount === null) {
            return '';
        }

        return hadeed('currency', 'EC$').number_format((float) $amount, $cents ? 2 : 0);
    }
}

if (! function_exists('usd_hint')) {
    /** "about US$17,000" for an EC$ amount at the fixed 2.70 peg. */
    function usd_hint(int|float $xcd): string
    {
        return 'about US$'.number_format(round($xcd / (float) hadeed('usd_rate', 2.70), -2));
    }
}
