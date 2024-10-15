<?php

namespace IMEdge\Web\Data\Model;

use JsonSerializable;
use Ramsey\Uuid\Uuid;

class SnmpCredential extends UuidObject implements JsonSerializable
{
    public const TABLE = 'snmp_credential';

    protected string $tableName = self::TABLE;
    protected string $keyProperty = 'credential_uuid';

    protected array $defaultProperties = [
        'credential_uuid' => null,
        'credential_name' => null,
        'snmp_version'    => null,
        'security_name'   => null,
        'security_level'  => null,
        'auth_protocol'   => null,
        'auth_key'        => null,
        'priv_protocol'   => null,
        'priv_key'        => null,
    ];

    public function jsonSerialize(): object
    {
        $map = [
            'credential_uuid' => 'uuid',
            'credential_name' => 'name',
            'snmp_version'    => 'version',
            'security_name'   => 'securityName',
            'security_level'  => 'securityLevel',
            'auth_protocol'   => 'authProtocol',
            'auth_key'        => 'authKey',
            'priv_protocol'   => 'privProtocol',
            'priv_key'        => 'privKey',
        ];
        $properties = [];
        foreach ($this->properties as $k => $v) {
            if ($k === 'credential_uuid') {
                $v = Uuid::fromBytes($v);
            }
            $properties[$map[$k]] = $v;
        }
        return (object) array_filter($properties, function ($v) {
            return $v !== null;
        });
    }
}
