<?php

namespace IMEdge\Web\Data\RemoteLookup;

use IMEdge\Web\Data\Model\SnmpCredential;
use IMEdge\Web\Select2\BaseSelect2Lookup;

class SnmpCredentialLookup extends BaseSelect2Lookup
{
    protected bool $usesUuid = true;
    protected string $idColumn = 'credential_uuid';
    protected string $table = SnmpCredential::TABLE;
    protected array $textColumns = ['credential_name'];
    protected array $searchColumns = ['credential_name'];
}
