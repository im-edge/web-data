<?php

namespace IMEdge\Web\Data\Model;

class SnmpAgent extends UuidObject
{
    public const TABLE = 'snmp_agent';

    protected string $tableName = self::TABLE;
    protected string $keyProperty = 'agent_uuid';

    protected array $defaultProperties = [
        'agent_uuid'       => null,
        'credential_uuid'  => null,
        'datanode_uuid'    => null,
        'lifecycle_uuid'   => null,
        'environment_uuid' => null,
        'ip_address'       => null,
        'ip_protocol'      => null,
        'snmp_port'        => null,
        'label'            => null,
    ];
}
