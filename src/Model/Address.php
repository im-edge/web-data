<?php

namespace IMEdge\Web\Data\Model;

use gipfl\ZfDb\Expr;

class Address extends UuidObject
{
    protected string $tableName = 'inventory_address';

    /**
     * @var array<string, null>
     */
    protected array $defaultProperties = [
        'uuid'         => null,
        'street'       => null,
        'zip'          => null,
        'city_name'    => null,
        'country_code' => null,
        'location'     => null,
        'nominatim_lookup_key' => null,
        'bounding_box' => null,
        /*
        'created_by'          => null,
        'modified_by'         => null,
        'ts_created'          => null,
        'ts_modified'         => null,
        */
        // 'city_id'            => null,
        // 'location'           => null,
        // 'bounding_box'       => null,
        // 'nominatim_key'      => null
    ];

    public function getZipAndCity(): string
    {
        return $this->get('zip') . ' ' . $this->get('city_name');
    }

    public function setZipAndCity($value)
    {
        if ($value === null) {
            $this->set('zip', null);
            $this->set('city_name', null);
        }
    }

    public function getProperties(?array $properties = null)
    {
        $properties = parent::getProperties($properties);
        if (isset($properties['location'])) {
            $properties['location'] = new Expr(sprintf("(PointFromText('POINT(%s)'))", $properties['location']));
        }
        if (isset($properties['bounding_box'])) {
            $properties['bounding_box'] = new Expr(sprintf(
                "(PointFromText('POLYGON(%s)'))",
                $properties['bounding_box']
            ));
        }

        return $properties;
    }
}
