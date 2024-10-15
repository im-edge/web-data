<?php

namespace IMEdge\Web\Data\Model;

class Site extends UuidObject
{
    protected string $tableName = 'inventory_site';

    protected array $defaultProperties = [
        'uuid'    => null,
        'site_name'    => null,
        'site_type'    => null,
        'address_uuid' => null,
        /*
        'created_by'          => null,
        'modified_by'         => null,
        'ts_created'          => null,
        'ts_modified'         => null,
        */
    ];
}
