<?php

namespace IMEdge\Web\Data\RemoteLookup;

use IMEdge\Web\Data\Model\DataNode;
use IMEdge\Web\Select2\BaseSelect2Lookup;

class DatanodeLookup extends BaseSelect2Lookup
{
    protected bool $usesUuid = true;
    protected string $idColumn = 'uuid';
    protected string $table = DataNode::TABLE;
    protected array $textColumns = ['label'];
    protected array $searchColumns = ['label'];
}
