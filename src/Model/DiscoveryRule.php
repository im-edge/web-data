<?php

namespace IMEdge\Web\Data\Model;

class DiscoveryRule extends UuidObject
{
    public const TABLE = 'snmp_discovery_rule';

    protected string $tableName = self::TABLE;

    protected array $defaultProperties = [
        'uuid'           => null,
        'label'          => null,
        'implementation' => null,
        'settings'       => null,
    ];
}
