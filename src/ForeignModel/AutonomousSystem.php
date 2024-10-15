<?php

namespace IMEdge\Web\Data\ForeignModel;

class AutonomousSystem
{
    /** @readonly */
    public int $asn;

    /** @readonly */
    public string $handle;

    /** @readonly */
    public string $description;

    public function __construct(int $asn, string $handle, string $description)
    {
        $this->asn = $asn;
        $this->handle = $handle;
        $this->description = $description;
    }
}
