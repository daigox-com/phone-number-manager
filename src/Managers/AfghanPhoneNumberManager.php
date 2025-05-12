<?php
/**
 * Afghan Mobile Phone Number Utility
 * -----------------------------------
 * A complete helper for validating, parsing and formatting Afghan mobile
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

namespace Daigox\PhoneNumberManager\Managers;

use Daigox\PhoneNumberManager\Operators\AfghanOperators;
use Daigox\PhoneNumberManager\Operators\CountryCallingCodes;
use InvalidArgumentException;

/**
 * Static utility class for working with Afghan mobile numbers.
 */
final class AfghanPhoneNumberManager
{
    /** Country calling code without + */
    private static string $countryCode = '93';
    public const LOCAL_TRUNK  = '0';

    /**
     * Get the country calling code
     * @return string
     */
    public static function getCountryCode(): string
    {
        return self::$countryCode;
    }

    /**
     * Operator constants for easy access
     */
    public const OPERATOR_ROSHAN = AfghanOperators::ROSHAN;
    public const OPERATOR_MTN = AfghanOperators::MTN;
    public const OPERATOR_ETISALAT = AfghanOperators::ETISALAT;
    public const OPERATOR_SALAM = AfghanOperators::SALAM;
    public const OPERATOR_AFGHANTEL = AfghanOperators::AFGHANTEL;
    public const OPERATOR_WASEL = AfghanOperators::WASEL;

    /**
     * Operator prefixes (leading 0 included).
     * Updated: May 2025.
     */
    private const OPERATOR_PREFIXES = [
        self::OPERATOR_MTN => ['070', '071', '077'],
        self::OPERATOR_ROSHAN => ['072', '073', '074', '079'],
        self::OPERATOR_ETISALAT => ['075', '076'],
        self::OPERATOR_SALAM => ['078'],
        self::OPERATOR_AFGHANTEL => ['0740', '0741', '0742', '0743', '0744'],
        self::OPERATOR_WASEL => ['0747', '0748', '0749'],
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

        if (str_starts_with($digits, '0093')) {
            $digits = substr($digits, 4);
        }
        if (str_starts_with($digits, '93')) {
            $digits = substr($digits, 2);
        }
        if (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if (!preg_match('/^7\d{8}$/', $digits)) {
            throw new InvalidArgumentException('Invalid Afghan mobile number.');
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
        try {
            $digits = self::normalize($input);
            foreach (self::getPrefixesOrderedDesc() as $operator => $prefixes) {
                foreach ($prefixes as $prefix) {
                    if (str_starts_with('0' . $digits, $prefix)) {
                        return $operator;
                    }
                }
            }
            return null;
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    public static function hasValidPrefix(string $input): bool
    {
        return self::getOperator($input) !== null;
    }

    public static function getPrefix(string $input, bool $withoutZero = true): ?string
    {
        try {
            $digits = self::normalize($input);
            foreach (self::getPrefixesOrderedDesc() as $prefixes) {
                foreach ($prefixes as $prefix) {
                    if (str_starts_with('0' . $digits, $prefix)) {
                        return $withoutZero ? ltrim($prefix, '0') : $prefix;
                    }
                }
            }
            return null;
        } catch (InvalidArgumentException) {
            return null;
        }
    }

    public static function split(string $input): array
    {
        $digits = self::normalize($input);
        $prefix = self::getPrefix($digits) ?? substr('0' . $digits, 0, 3);
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
        return '+' . self::getCountryCode() . self::normalize($input);
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
        return 'tel:+' . self::getCountryCode() . '-' . ltrim($parts['prefix'], '0') . '-' . $parts['middle'] . '-' . $parts['last'];
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
        $remaining  = 9 - strlen($barePrefix);
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