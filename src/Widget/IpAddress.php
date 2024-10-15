<?php

namespace IMEdge\Web\Data\Widget;

use gipfl\Translation\TranslationHelper;
use IMEdge\Web\Data\Lookup\IpToCountryLiteLookup;
use ipl\Html\BaseHtmlElement;

class IpAddress extends BaseHtmlElement
{
    use TranslationHelper;

    protected $tag = 'span';
    protected $defaultAttributes = [
        'class' => 'ip-address',
    ];

    /** @readonly  */
    public string $binaryIp;
    /** @readonly  */
    public string $country;
    protected IpToCountryLiteLookup $lookup;

    public function __construct(string $binaryIp, IpToCountryLiteLookup $lookup)
    {
        $this->binaryIp = $binaryIp;
        $this->lookup = $lookup;
        if (self::isPrivateIp($binaryIp)) {
            $this->addAttributes(['title' => $this->translate('This is a privat IP address')]);
            return;
        }
        if ($country = $this->lookup->getCountryCode($binaryIp)) {
            $this->addAttributes(['title' => sprintf($this->translate('Country: %s'), $country)]);
            $this->addAttributes(['class' => ['fi', "fi-" . strtolower($country)]]);
            $this->country = $country;
        }
    }

    protected static function isPrivateIp(string $binaryIp): bool
    {
        if (strlen($binaryIp) === 4) {
            // TODO: Opt for a real ip/net implementation
            if (substr(inet_ntop($binaryIp), 0, 3) === '10.') {
                return true;
            }
            if (substr(inet_ntop($binaryIp), 0, 8) === '192.168.') {
                return true;
            }
        }

        return false;
    }

    protected function assemble()
    {
        $this->add(inet_ntop($this->binaryIp));
    }
}
