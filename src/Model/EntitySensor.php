<?php

namespace IMEdge\Web\Data\Model;

class EntitySensor extends BaseObject
{
    public const TABLE = 'inventory_physical_entity_sensor';
    protected string $tableName = self::TABLE;

    protected array $keyProperty = [
        'device_uuid',
        'entity_index',
    ];

    protected array $defaultProperties = [
        'device_uuid'          => null,
        'entity_index'         => null,
        'sensor_type'          => null,
        'sensor_scale'         => null,
        'sensor_precision'     => null,
        'sensor_status'        => null,
        'sensor_value'         => null,
        'sensor_units_display' => null,
    ];
}
