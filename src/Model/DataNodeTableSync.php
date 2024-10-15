<?php

namespace IMEdge\Web\Data\Model;

class DataNodeTableSync extends BaseObject
{
    public const TABLE = 'datanode_table_sync';

    protected array $keyProperty = ['datanode_uuid', 'table_name'];
    protected string $tableName = self::TABLE;
    protected array $defaultProperties = [
        'datanode_uuid'    => null,
        'table_name'       => null,
        'current_position' => null,
        'current_error'    => null,
    ];
}
