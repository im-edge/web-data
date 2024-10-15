<?php

namespace IMEdge\Web\Data\Model;

class SnmpSystemInfo extends UuidObject
{
    public const TABLE = 'snmp_system_info';

    protected string $tableName = self::TABLE;
    protected array $defaultProperties = [
        'uuid'        => null,
        'datanode_uuid'   => null,
        // TODO: 'credential_uuid'     => null,
        'system_name'        => null,
        'system_description'       => null,
        'system_location'         => null,
        'system_contact'         => null,
        'system_services' => null,
        'system_oid'  => null,
        'system_engine_id'  => null,
        'system_engine_boot_count'  => null,
        'system_engine_boot_time'  => null,
        'system_engine_max_message_size'  => null,
        'dot1d_base_bridge_address'  => null,
    ];
}
