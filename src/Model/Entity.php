<?php

namespace IMEdge\Web\Data\Model;

class Entity extends BaseObject
{
    public const TABLE = 'inventory_physical_entity';

    protected string $tableName = self::TABLE;
    protected array $keyProperty = [
        'device_uuid',
        'entity_index',
    ];
    protected array $defaultProperties = [
        'device_uuid'            => null,
        'entity_index'           => null,
        'name'                   => null,
        'alias'                  => null,
        'description'            => null,
        'model_name'             => null,
        'asset_id'               => null,
        'parent_index'           => null,
        'class'                  => null,
        'relative_position'      => null,
        'revision_hardware'      => null,
        'revision_firmware'      => null,
        'revision_software'      => null,
        'manufacturer_name'      => null,
        'serial_number'          => null,
        'field_replaceable_unit' => null,
    ];
}
