<?php

namespace IMEdge\Web\Data\Reader;

use RuntimeException;

class IpListReader
{
    public static function readString($string): array
    {
        $lines = preg_split('/\r?\n/', $string, -1, PREG_SPLIT_NO_EMPTY);
        $ips = [];
        $i = 0;
        while (array_key_exists($i, $lines)) {
            $line = $lines[$i];
            $i++;
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if ($line[0] === ';') {
                continue;
            }
            if (($pos = strpos($line, '-')) === false) {
                $ips[] = $line;
            } else {
                array_push($lines, ...self::explodeIps($line));
            }
        }

        return $ips;
    }

    protected static function explodeIps($ip): array
    {
        $ips = [];
        if (!preg_match('/^(.*?)(\d+)-(\d+)(.*?)$/', $ip, $match)) {
            throw new RuntimeException("Invalid IP range: $ip");
        }
        // printf("Range from %s to %s\n", $match[2], $match[3]);

        foreach (range((int) $match[2], (int) $match[3]) as $part) {
            $ips[] = $match[1] . $part . $match[4];
        }

        return $ips;
    }
}
