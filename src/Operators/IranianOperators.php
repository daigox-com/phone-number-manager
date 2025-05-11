<?php

namespace Daigox\PhoneNumberManager\Operators;

/**
 * Class IranianOperators
 * Contains constants for all Iranian mobile operators
 */
class IranianOperators
{
    // Mobile Network Operators (MNOs)
    public const HAMRAHE_AVAL = 'MCI';  // Mobile Communications Company of Iran
    public const IRANCELL = 'IRN';      // Irancell
    public const RIGHTEL = 'RTL';       // Rightel
    public const SHATEL = 'SHT';        // Shatel Mobile
    public const AZARTAKHT = 'AZR';     // Azartakht
    public const SAMANTEL = 'SMT';      // Samantel
    public const APTELL = 'APT';        // Aptel

    // Mobile Virtual Network Operators (MVNOs)
    public const TALIYA = 'TLY';        // Taliya
    public const LOTUSTEL = 'LTS';      // LotusTel
    public const ANARESTAN = 'ANR';     // Anarestan
    public const AZARTAKHT_MVNO = 'AZM'; // Azartakht MVNO
    public const SAMANTEL_MVNO = 'SMM'; // Samantel MVNO

    /**
     * Get all operators as an array
     * @return array
     */
    public static function getAllOperators(): array
    {
        return [
            self::HAMRAHE_AVAL,
            self::IRANCELL,
            self::RIGHTEL,
            self::SHATEL,
            self::AZARTAKHT,
            self::SAMANTEL,
            self::APTELL,
            self::TALIYA,
            self::LOTUSTEL,
            self::ANARESTAN,
            self::AZARTAKHT_MVNO,
            self::SAMANTEL_MVNO
        ];
    }

    /**
     * Get all MNOs (Mobile Network Operators)
     * @return array
     */
    public static function getMNOs(): array
    {
        return [
            self::HAMRAHE_AVAL,
            self::IRANCELL,
            self::RIGHTEL,
            self::SHATEL,
            self::AZARTAKHT,
            self::SAMANTEL,
            self::APTELL
        ];
    }

    /**
     * Get all MVNOs (Mobile Virtual Network Operators)
     * @return array
     */
    public static function getMVNOs(): array
    {
        return [
            self::TALIYA,
            self::LOTUSTEL,
            self::ANARESTAN,
            self::AZARTAKHT_MVNO,
            self::SAMANTEL_MVNO
        ];
    }
} 