<?php

namespace IMEdge\Web\Data\Model;

class DiscoveryCandidate extends UuidObject
{
    public const TABLE = 'snmp_discovery_candidate';

    protected string $tableName = self::TABLE;
    protected array $defaultProperties = [
        'uuid'                => null,
        'discovery_rule_uuid' => null,
        'datanode_uuid'       => null,
        'credential_uuid'     => null,
        'ip_address'          => null,
        'snmp_port'           => null,
        'state'               => null,
        'ts_last_reachable'   => null,
        'ts_last_check'       => null,
    ];
}
