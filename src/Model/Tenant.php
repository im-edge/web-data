<?php

namespace IMEdge\Web\Data\Model;

class Tenant extends UuidObject
{
    protected string $tableName = 'tenant';

    /** @var array<string, null> */
    protected array $defaultProperties = [
        'uuid'  => null,
        'name'  => null,
        'label' => null,
    ];
}
