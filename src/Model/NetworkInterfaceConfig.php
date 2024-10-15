<?php

namespace IMEdge\Web\Data\Model;

class NetworkInterfaceConfig extends BaseObject
{
    public const TABLE = 'snmp_interface_config';

    protected string $tableName = self::TABLE;
    protected array $keyProperty = [
        'system_uuid',
        'if_index',
    ];

    protected array $defaultProperties = [
        'system_uuid'            => null,
        'datanode_uuid'          => null,
        'if_index'               => null,
        'if_type'                => null,
        'if_name'                => null,
        'if_alias'               => null,
        'if_description'         => null,
        'physical_address'       => null,
        'mtu'                    => null,
        'speed_kbit'             => null,
        'status_admin'           => null,
        'monitor'                => null,
        'notify'                 => null,
        'promiscuous_mode'       => null,
        /*
        // TODO:
        'ipv4_enabled'           => null,
        'ipv6_enabled'           => null,
        'ipv4_forwarding'        => null,
        'ipv6_forwarding'        => null,
        'ipv4_mtu'               => null,
        'ipv6_mtu'               => null,
        'forward_transitions'    => null,
        */
    ];
}
