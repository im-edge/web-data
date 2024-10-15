<?php

namespace IMEdge\Web\Data\Model;

use gipfl\ZfDbStore\Store;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

abstract class UuidObject extends BaseObject
{
    protected string $keyProperty = 'uuid';

    public function set($property, $value): bool
    {
        if ($property === $this->keyProperty || $property === 'uuid' || substr($property, -5) === '_uuid') {
            if (static::isHexString($value)) {
                $value = Uuid::fromString($value)->getBytes();
            }
        }

        return parent::set($property, $value);
    }

    protected static function isHexString($string): bool
    {
        return $string !== null && strlen($string) > 16 && preg_match('/^[A-Fa-f0-9-]+$/', $string);
    }

    public function hasUuid(): bool
    {
        return $this->get($this->keyProperty) !== null;
    }

    public function getUuid(): UuidInterface
    {
        $uuid = $this->get($this->keyProperty);
        if ($uuid === null) {
            $uuid = Uuid::uuid4();
            $this->set($this->keyProperty, $uuid->getBytes());
        } else {
            $uuid = Uuid::fromBytes($uuid);
        }

        return $uuid;
    }

    public function getNiceUuid(): string
    {
        return $this->getUuid()->toString();
    }

    /**
     * Loads an already existing $storable
     *
     * @param Store $store
     * @param string $key
     * @return static
     */
    public static function load(Store $store, $key): self
    {
        if (static::isHexString($key)) {
            $key = Uuid::fromString($key)->getBytes();
        }

        $object = $store->load($key, get_called_class());
        assert($object instanceof UuidObject);

        return $object;
    }
}
