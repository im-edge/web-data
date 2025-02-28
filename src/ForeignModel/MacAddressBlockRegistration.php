<?php

namespace IMEdge\Web\Data\ForeignModel;

class MacAddressBlockRegistration
{
    public const DB_TABLE = 'data_mac_address_block';

    /** @readonly */
    public string $prefix;

    /** @readonly */
    public int $prefixLength;

    /** @readonly */
    public string $company;

    /** @readonly */
    public string $address;

    public function __construct(string $prefix, int $prefixLength, string $company, string $address)
    {
        $this->prefix = $prefix;
        $this->prefixLength = $prefixLength;
        $this->company = $company;
        $this->address = $address;
    }
}
