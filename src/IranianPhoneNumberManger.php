<?php

namespace Daigox\PhoneNumberManager;

class IranianPhoneNumberManger
{
    private const HAMRAH_E_AVAL = ['0910', '0911', '0912', '0913', '0914', '0915', '0916', '0917', '0918', '0919', '0990', '0991', '0992', '0993', '0994'];
    private const IRANCELL = ['0930', '0933', '0935', '0936', '0937', '0938', '0939', '0900', '0901', '0902', '0903', '0904', '0905', '0941'];
    private const RIGHTEL = ['0920', '0921', '0922', '0923'];
    private const SHATEL_MOBILE = ['099810', '099811', '099812', '099813', '099814', '099815', '099816', '099817'];
    private const SAMANTEL = ['099999', '09999'];
    private const TELE_KISH = ['0934'];
    private const APTEL = ['099910', '099911', '099913', '099914'];
    private const LOTUSTEL = ['09990'];
    private const ARIANTEL = ['09998'];
    private const ANARSTAN = ['0994'];

    public static function isValidIranianNumber($number): bool
    {
        $number = self::sanitizePhone($number);
        return preg_match('/^(\+98|0)?9\d{9}$/', $number);
    }

    public static function getPrefix($number): string|false
    {
        $number = self::sanitizePhone($number);
        if (self::isValidIranianNumber($number)) {
            return substr(self::normalizeNumber($number), 0, 3);
        }
        return false;
    }

    public static function getMiddleThreeDigits($number): string|false
    {
        $number = self::sanitizePhone($number);
        if (self::isValidIranianNumber($number)) {
            return substr(self::normalizeNumber($number), 3, 3);
        }
        return false;
    }

    public static function getLastFourDigits($number): string|false
    {
        $number = self::sanitizePhone($number);
        if (self::isValidIranianNumber($number)) {
            return substr(self::normalizeNumber($number), 6, 4);
        }
        return false;
    }

    public static function formatNumberInternational($number): string|false
    {
        $number = self::sanitizePhone($number);
        if (self::isValidIranianNumber($number)) {
            return '+98' . substr(self::normalizeNumber($number), 0);
        }
        return false;
    }

    public static function formatNumberLocal($number): string|false
    {
        $number = self::sanitizePhone($number);
        if (self::isValidIranianNumber($number)) {
            return '0' . substr(self::normalizeNumber($number), 0);
        }
        return false;
    }

    public static function formatNumberBare($number): string|false
    {
        $number = self::sanitizePhone($number);
        if (self::isValidIranianNumber($number)) {
            return substr(self::normalizeNumber($number), 0);
        }
        return false;
    }

    private static function normalizeNumber($number): string
    {
        $number = self::sanitizePhone($number);
        $number = preg_replace('/^\+98/', '', $number);
        $number = preg_replace('/^0/', '', $number);
        return $number;
    }

    public static function isValidPrefix($number): bool
    {
        $number = self::sanitizePhone($number);
        $validPrefixes = [
            ...self::HAMRAH_E_AVAL,
            ...self::IRANCELL,
            ...self::RIGHTEL,
            ...self::SHATEL_MOBILE,
            ...self::SAMANTEL,
            ...self::TELE_KISH,
            ...self::APTEL,
            ...self::LOTUSTEL,
            ...self::ARIANTEL,
            ...self::ANARSTAN,
        ];
        $phone = self::normalizeNumber($number);
        $sixDigits ='0' . substr($phone, 0, 6);
        foreach ($validPrefixes as $prefix) {
            if (strpos($sixDigits, $prefix) === 0) {
                return true;
            }
        }
        return false;
    }

    public static function getOperatorName($number): string
    {
        $number = self::sanitizePhone($number);
        $phone = self::normalizeNumber($number);
        $sixDigits = '0' . substr($phone, 0, 6);
        $operatorPrefixes = [
            'hamrah-e-aval' => self::HAMRAH_E_AVAL,
            'irancell' => self::IRANCELL,
            'rightel' => self::RIGHTEL,
            'shatel-mobile' => self::SHATEL_MOBILE,
            'samantel' => self::SAMANTEL,
            'tele-kish' => self::TELE_KISH,
            'aptel' => self::APTEL,
            'lotustel' => self::LOTUSTEL,
            'ariantel' => self::ARIANTEL,
            'anarstan' => self::ANARSTAN,
        ];
        foreach ($operatorPrefixes as $operator => $prefixes) {
            foreach ($prefixes as $prefixe) {
                if (strpos($sixDigits, $prefixe) === 0) {
                    return $operator;
                }
            }
        }
        return 'other';
    }

    public static function sanitizePhone($number): string
    {
        return StringManager::convertPersianNumbersToEnglish($number);
    }
}