<?php

namespace IMEdge\Web\Data\Model;

class NetworkInterfaceStatus extends BaseObject
{
    public const TABLE = 'snmp_interface_status';

    protected string $tableName = self::TABLE;
    protected array $keyProperty = [
        'system_uuid',
        'if_index',
    ];

    protected array $defaultProperties = [
        'system_uuid'             => null,
        'if_index'                => null,
        // 'up','down','testing','unknown','dormant','notPresent','lowerLayerDown'
        'status_operational'      => null,
        // 'disabled','blocking','listening','learning','forwarding','broken'
        'status_stp'              => null,
        // 'unknown','halfDuplex','fullDuplex'
        'status_duplex'           => null,
        'connector_present'       => null,
        'promiscuous_mode'        => null, // also in config?!
        'current_kbit_in'         => null,
        'current_kbit_out'        => null,
        'last_update'             => null,
        'stp_designated_root'     => null,
        'stp_designated_bridge'   => null,
        'stp_designated_port'     => null,
        'stp_forward_transitions' => null,
        'stp_port_path_cost'      => null,
    ];
}
