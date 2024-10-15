<?php

namespace IMEdge\Web\Data\Lookup;

use gipfl\ZfDb\Adapter\Adapter;
use gipfl\ZfDb\Select;
use IMEdge\Web\Data\ForeignModel\MacAddressBlockRegistration;
use IMEdge\Web\Data\Helper\MacAddressHelper;

// TODO: https://www.iana.org/assignments/ethernet-numbers/ethernet-numbers.xhtml !!
class MacAddressBlockLookup
{
    protected const PREFIXES = [24, 28, 36];

    protected Adapter $db;

    public function __construct(Adapter $db)
    {
        $this->db = $db;
    }

    public function getCompany(string $macAddress): ?string
    {
        return $this->db->fetchOne($this->prepareSearchQuery($macAddress)->columns('company'));
    }

    public function getRegistration(string $macAddress): ?MacAddressBlockRegistration
    {
        if ($row = $this->db->fetchRow($this->prepareSearchQuery($macAddress))) {
            return new MacAddressBlockRegistration(
                $row->prefix,
                $row->prefix_length,
                $row->company,
                $row->address,
            );
        }

        return null;
    }

    protected function prepareSearchQuery(string $macAddress): Select
    {
        $db = $this->db;
        $query = $db->select()->from(MacAddressBlockRegistration::DB_TABLE)->order('prefix_length DESC')->limit(1);

        foreach (self::PREFIXES as $prefixLength) {
            $query->orWhere($db->quoteInto(
                '(prefix = ? AND prefix_length = ' . $prefixLength . ')',
                MacAddressHelper::getPrefix(MacAddressHelper::toBinary($macAddress), $prefixLength)
            ));
        }

        return $query;
    }
}
