<?php

namespace IMEdge\Web\Data\Model;

use gipfl\ZfDb\Expr;
use gipfl\ZfDbStore\DbStorable;
use gipfl\ZfDbStore\DbStorableInterface;
use RuntimeException;

abstract class BaseObject implements DbStorableInterface
{
    use DbStorable;

    public function getDisplayColumn(): Expr
    {
        if (is_array($this->getKeyProperty())) {
            throw new RuntimeException('Display column for array key property is not supported: ' . get_class($this));
        }

        return new Expr($this->getKeyProperty());
    }

    // TODO: getDisplayName.
}
