<?php
/**
 * Iranian Mobile Phone Number Utility
 * -----------------------------------
 * A complete helper for validating, parsing and formatting Iranian mobile
 * phone numbers, including operator detection and random number generation.
 *
 * @package   daigox/phone-number-manager
 * @author    Daigox
 * @copyright 2025
 * @license   MIT
 *
 * PHP version 8.1+
 */

declare(strict_types=1);

namespace Daigox\PhoneNumberManager;

use InvalidArgumentException;

/**
 * Static utility class for working with Iranian mobile numbers.
 */
final class IranianPhoneNumberManger
{
    /** Country calling code without + */
    public const COUNTRY_CODE = '98';
    public const LOCAL_TRUNK  = '0';

    /**
     * Operator prefixes (leading 0 included).
     * Updated: May 2025.
     */
    private const OPERATOR_PREFIXES = [
        // همراه اول – Hamrah‑e Aval / MCI
        'hamrah-e-aval' => [
            '0910','0911','0912','0913','0914','0915','0916','0917','0918','0919',
            '0990','0991','0992','0993','0994','0995','0996',
        ],

        // ایرانسل – Irancell
        'irancell' => [
            '0930','0933','0935','0936','0937','0938','0939',
            '0900','0901','0902','0903','0904','0905','0941',
        ],

        // رایتل – Rightel
        'rightel' => ['0920','0921','0922','0923'],

        // شاتل‌موبایل – Shatel Mobile (Full MVNO)
        'shatel-mobile' => [
            '099810','099811','099812','099813','099814','099815','099816','099817',
            '099818','099819','099820','099821',
        ],

        // سامانتل – Samantel
        'samantel' => ['09999','099999','099996','099997','099998'],

        // شبکه مستقل تلفن همراه کیش – Tele Kish (TKC)
        'tele-kish' => ['0934'],

        // تالیا – Taliya (اعتباری)
        'taliya' => ['0932'],

        // اپراتور محلی اسپادان – Spadan
        'spadan' => ['0931'],

        // آپتل –&nbsp;Aptel (MVNO)
        'aptel' => ['099910','099911','099913'],

        // آذرتل – Azartel (هواداری تراکتور)
        'azartel' => ['099914'],

        // لوتوس‌تل – LotusTel
        'lotustel' => ['09990'],

        // آریان‌تل – ArianTel
        'ariantel' => ['09998'],

        // انارستان – Anarestan (سیم‌کارت دانش‌آموزی)
        'anarstan' => ['0994','09944','09945','09908','09932','09933'],
    ];

    /* -------------------------------------------------------------------- */
    /*  Sanitisation                                                        */
    /* -------------------------------------------------------------------- */

    private const DIGIT_MAP = [
        '۰' => '0','۱' => '1','۲' => '2','۳' => '3','۴' => '4','۵' => '5','۶' => '6','۷' => '7','۸' => '8','۹' => '9',
        '٠' => '0','١' => '1','٢' => '2','٣' => '3','٤' => '4','٥' => '5','٦' => '6','٧' => '7','٨' => '8','٩' => '9',
    ];

    private function __construct() {}

    public static function sanitize(string $input): string
    {
        return strtr(trim($input), self::DIGIT_MAP);
    }

    /* -------------------------------------------------------------------- */
    /*  Normalisation & validation                                          */
    /* -------------------------------------------------------------------- */

    public static function normalize(string $input): string
    {
        $digits = preg_replace('/\D+/', '', self::sanitize($input));

        if (str_starts_with($digits, '0098')) {
            $digits = substr($digits, 4);
        }
        if (str_starts_with($digits, '98')) {
            $digits = substr($digits, 2);
        }
        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if (!preg_match('/^9\d{9}$/', $digits)) {
            throw new InvalidArgumentException('Invalid Iranian mobile number.');
        }
        return $digits;
    }

    public static function isValid(string $input): bool
    {
        try {
            self::normalize($input);
            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    /* -------------------------------------------------------------------- */
    /*  Operator & prefix utilities                                         */
    /* -------------------------------------------------------------------- */

    public static function getOperator(string $input): ?string
    {
        $digits = self::normalize($input);
        foreach (self::getPrefixesOrderedDesc() as $operator => $prefixes) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with('0' . $digits, $prefix)) {
                    return $operator;
                }
            }
        }
        return null;
    }

    public static function hasValidPrefix(string $input): bool
    {
        return self::getOperator($input) !== null;
    }

    public static function getPrefix(string $input, bool $withoutZero = true): ?string
    {
        $digits = self::normalize($input);
        foreach (self::getPrefixesOrderedDesc() as $prefixes) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with('0' . $digits, $prefix)) {
                    return $withoutZero ? ltrim($prefix, '0') : $prefix;
                }
            }
        }
        return null;
    }

    public static function split(string $input): array
    {
        $digits = self::normalize($input);
        $prefix = self::getPrefix($digits) ?? substr('0' . $digits, 0, 4);
        $len   = strlen(ltrim($prefix, '0'));
        return [
            'prefix' => $prefix,
            'middle' => substr($digits, $len, 3),
            'last'   => substr($digits, $len + 3),
        ];
    }

    /* -------------------------------------------------------------------- */
    /*  Formatting                                                          */
    /* -------------------------------------------------------------------- */

    public static function formatInternational(string $input): string
    {
        return '+' . self::COUNTRY_CODE . self::normalize($input);
    }

    public static function formatLocal(string $input): string
    {
        return self::LOCAL_TRUNK . self::normalize($input);
    }

    public static function formatBare(string $input): string
    {
        return self::normalize($input);
    }

    public static function formatRFC3966(string $input): string
    {
        $parts = self::split($input);
        return 'tel:+' . self::COUNTRY_CODE . '-' . ltrim($parts['prefix'], '0') . '-' . $parts['middle'] . '-' . $parts['last'];
    }

    /* -------------------------------------------------------------------- */
    /*  Random number generator                                             */
    /* -------------------------------------------------------------------- */

    public static function random(?string $operator = null): string
    {
        if ($operator !== null && !isset(self::OPERATOR_PREFIXES[$operator])) {
            throw new InvalidArgumentException("Unknown operator '{$operator}'.");
        }
        $operator   = $operator ?? array_rand(self::OPERATOR_PREFIXES);
        $prefix     = self::OPERATOR_PREFIXES[$operator][array_rand(self::OPERATOR_PREFIXES[$operator])];
        $barePrefix = ltrim($prefix, '0');
        $remaining  = 10 - strlen($barePrefix);
        $randomPart = str_pad((string)random_int(0, (10 ** $remaining) - 1), $remaining, '0', STR_PAD_LEFT);
        return self::LOCAL_TRUNK . $barePrefix . $randomPart;
    }

    /* -------------------------------------------------------------------- */

    private static function getPrefixesOrderedDesc(): array
    {
        static $cache;
        if ($cache !== null) {
            return $cache;
        }
        foreach (self::OPERATOR_PREFIXES as $operator => $prefixes) {
            usort($prefixes, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));
            $cache[$operator] = $prefixes;
        }
        return $cache;
    }
}
