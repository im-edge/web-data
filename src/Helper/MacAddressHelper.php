<?php

namespace IMEdge\Web\Data\Helper;

use RuntimeException;

use function hex2bin;
use function in_array;
use function str_replace;
use function substr;

class MacAddressHelper
{
    public static function isLocallyAdministered(string $binaryMacAddress): bool
    {
        return (substr($binaryMacAddress, 0, 1) & "\x02") === "\x02";
    }

    public static function isMulticast(string $binaryMacAddress): bool
    {
        return (substr($binaryMacAddress, 0, 1) & "\x01") === "\x01";
    }

    public static function isVRRPv4(string $binaryMacAddress): bool
    {
        // 00:00:5E:00:01:00 - 00:00:5E:00:01:FF
        return substr($binaryMacAddress, 0, 5) === "\x00\x00\x5E\x00\x01";
    }

    public static function isVRRPv6(string $binaryMacAddress): bool
    {
        // 00:00:5E:00:02:00 - 00:00:5E:00:02:FF
        return substr($binaryMacAddress, 0, 5) === "\x00\x00\x5E\x00\x02";
    }

    public static function isUnicast(string $binaryMacAddress): bool
    {
        return ! self::isMulticast($binaryMacAddress);
    }

    public static function toText(string $binaryMacAddress): string
    {
        $hex = bin2hex($binaryMacAddress);

        return implode(':', array_map('strtolower', str_split($hex, 2)));
    }

    public static function toPrefixText(string $binaryMacAddress, int $prefixLength): string
    {
        $hex = bin2hex($binaryMacAddress);

        $result = implode(':', array_map('strtolower', str_split($hex, 2)));
        if ($prefixLength % 8 === 4) {
            return substr($result, 0, -1); // Strip final 0
        }

        return $result;
    }

    public static function toBinary(string $formattedMacAddress): string
    {
        $macAddress = str_replace(['-', ':'], '', $formattedMacAddress);
        // if (! in_array(strlen($macAddress), [4, 5, 6])) {
        if (! in_array(strlen($macAddress), [8, 10, 12])) {
            throw new RuntimeException("48 bit MAC address (or prefix) expected, got '$formattedMacAddress'");
        }

        return hex2bin($macAddress);
    }

    public static function getPrefix(string $binaryMacAddress, int $prefixLength): string
    {
        $prefixBytes = ceil($prefixLength / 8);
        $odd = ($prefixLength % 8) !== 0;
        $prefix = substr($binaryMacAddress, 0, $prefixBytes);
        if ($odd) {
            $prefix = substr($prefix, 0, -1) . chr(ord(substr($prefix, -1, 1)) & 0xf0);
        }

        return $prefix;
    }
}
