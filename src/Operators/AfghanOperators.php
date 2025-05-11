<?php

namespace Daigox\PhoneNumberManager\Operators;

/**
 * Class AfghanOperators
 * Contains constants for all Afghan mobile operators
 */
class AfghanOperators
{
    // Mobile Network Operators (MNOs)
    public const ROSHAN = 'RSH';        // Roshan
    public const MTN = 'MTN';           // MTN Afghanistan
    public const ETISALAT = 'ETL';      // Etisalat Afghanistan
    public const SALAM = 'SLM';         // Salam Telecom
    public const AFGHANTEL = 'AFT';     // Afghan Telecom
    public const WASEL = 'WSL';         // Wasel Telecom

    /**
     * Get all operators as an array
     * @return array
     */
    public static function getAllOperators(): array
    {
        return [
            self::ROSHAN,
            self::MTN,
            self::ETISALAT,
            self::SALAM,
            self::AFGHANTEL,
            self::WASEL
        ];
    }

    /**
     * Get all MNOs (Mobile Network Operators)
     * @return array
     */
    public static function getMNOs(): array
    {
        return self::getAllOperators();
    }
} 