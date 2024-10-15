<?php

namespace IMEdge\Web\Data\ForeignModel;

use gipfl\ZfDb\Adapter\Adapter;
use gipfl\ZfDb\Expr;
use Ramsey\Uuid\Uuid;

class ZipCode
{
    public const TABLE_NAME = 'data_known_zipcode';
    protected const NS_UUID = 'f4b3b876-c4de-468b-bc4f-fb5c27db6cee';

    protected Adapter $db;

    public function __construct(Adapter $db)
    {
        $this->db = $db;
    }

    public function import(string $countryCode, $rows)
    {
        $db = $this->db;
        $db->beginTransaction();
        // We want to add/delete/modify those in the future
        $cntDeleted = $db->delete(self::TABLE_NAME, $db->quoteInto('country_code = ?', $countryCode));
        $ns = Uuid::fromString(self::NS_UUID);
        foreach ($rows as $line) {
            if ($line['country_code'] !== $countryCode) {
                throw new \RuntimeException(sprintf(
                    "Country Code '%s' does not match '%s'",
                    $line['country_code'],
                    $countryCode
                ));
            }
            $data = [
                // I absolutely do not like using "place" here, but country/zip is not unique.
                // References should therefore be ON UPDATE CASCADE, we might want to change these
                'uuid' => Uuid::uuid5($ns, implode('|', [
                    $line['country_code'],
                    $line['zipcode'],
                    $line['place'],
                    $line['community_code'], // see 9615 Schinzengraben, place is not enough
                ]))->getBytes(),
                'country_code' => $line['country_code'],
                'zip'          => $line['zipcode'],
                'place'        => $line['place'],
                'state'        => $line['state'],
                'state_code'   => $line['state_code'],
                'location'     => new Expr(
                    // Should we use quoteInto?
                    sprintf(
                        'POINT(%.6F, %.6F)',
                        (float) $line['longitude'],
                        (float) $line['latitude']
                    )
                ),
            ];
            // Y as latitude and X as longitude
            // I am using Mapinfo, and it has Y as latitude and X as longitude.
            // Breitengrad = Latitude = Y −90, 90
            // Längengrad = Longitude = X −180, 180
            // (easting, northing) or (longitude, latitude) or (x, y)
            try {
                $db->insert(self::TABLE_NAME, $data);
            } catch (\Exception $e) {
                echo $e->getMessage() . "\n";
                $db->rollBack();
                print_r($line);
                print_r($data);
                exit;
            }
        }
        $db->commit();
        printf("%d zip codes for %s replaced with %d new ones\n", $cntDeleted, $countryCode, count($rows));
    }

    public static function parseGeoNamesFile(string $filename): array
    {
        $headers = [
            'country_code',
            'zipcode',
            'place',
            'state',
            'state_code',
            'province',
            'province_code',
            'community',
            'community_code',
            'latitude',
            'longitude',
            'precision_code',
        ];

        if (! file_exists($filename) || ! is_readable($filename)) {
            throw new \RuntimeException("Cannot read $filename");
        }

        $result = [];
        foreach (file($filename) as $line) {
            $result[] = array_combine($headers, explode("\t", $line));
        }

        return $result;
    }
}
