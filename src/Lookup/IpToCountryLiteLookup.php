<?php

namespace IMEdge\Web\Data\Lookup;

use gipfl\ZfDb\Adapter\Adapter;
use gipfl\ZfDb\Select;
use IMEdge\Web\Data\Importer\IpToCountryLiteImporter;

class IpToCountryLiteLookup
{
    protected Adapter $db;

    public function __construct(Adapter $db)
    {
        $this->db = $db;
    }

    public function getCountryCode(string $binaryIp): ?string
    {
        return $this->db->fetchOne($this->prepareSearchQuery($binaryIp)->columns('country_code'));
    }

    protected function prepareSearchQuery(string $binaryIp): Select
    {
        // Das ist zu langsam. Ggf anders speichern und/oder so was:
        // SELECT country_code FROM data_ip_country_lite WHERE ip_range_from < '�' ORDER BY ip_range_from desc limit 1;

        return $this->db->select()
            ->from(IpToCountryLiteImporter::DB_TABLE, [])
            ->where('ip_family = ?', strlen($binaryIp) === 4 ? 'IPv4' : 'IPv6')
            ->where('? BETWEEN ip_range_from AND ip_range_to', $binaryIp);
    }
}
