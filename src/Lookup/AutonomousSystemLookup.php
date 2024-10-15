<?php

namespace IMEdge\Web\Data\Lookup;

use gipfl\ZfDb\Adapter\Adapter;
use gipfl\ZfDb\Select;
use IMEdge\Web\Data\ForeignModel\AutonomousSystem;

class AutonomousSystemLookup
{
    protected Adapter $db;

    public function __construct(Adapter $db)
    {
        $this->db = $db;
    }

    public function getHandle(int $asn): ?string
    {
        return $this->db->fetchOne($this->prepareSearchQuery($asn)->columns('handle'));
    }

    public function lookup(string $asn): ?AutonomousSystem
    {
        if ($row = $this->db->fetchRow($this->prepareSearchQuery($asn)->columns('*'))) {
            return new AutonomousSystem(
                $row->asn,
                $row->handle,
                $row->description,
            );
        }

        return null;
    }

    protected function prepareSearchQuery(int $asn): Select
    {
        return $this->db->select()->from(AutonomousSystem::DB_TABLE, [])->where('asn = ?', $asn);
    }
}
