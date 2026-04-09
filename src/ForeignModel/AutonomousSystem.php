<?php

namespace IMEdge\Web\Data\ForeignModel;

class AutonomousSystem
{
    public const DB_TABLE = 'data_autonomous_system';

    /** @readonly */
    public int $asn;

    /** @readonly */
    public string $handle;

    /** @readonly */
    public string $description;

    /** @readonly */
    public string $countryCode;

    public function __construct(int $asn, string $handle, string $description, string $countryCode)
    {
        $this->asn = $asn;
        $this->handle = $handle;
        $this->description = $description;
        $this->countryCode = $countryCode;
    }
}
