<?php

namespace IMEdge\Web\Data\Model;

class DataNode extends UuidObject
{
    public const TABLE = 'datanode';

    protected string $tableName = self::TABLE;
    protected array $defaultProperties = [
        'uuid'  => null,
        'label' => null,
        'db_stream_position' => null,
        'db_stream_error'    => null,
    ];
}
