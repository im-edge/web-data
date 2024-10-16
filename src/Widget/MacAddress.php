<?php

namespace IMEdge\Web\Data\Widget;

use gipfl\Translation\TranslationHelper;
use IMEdge\Web\Data\Helper\MacAddressHelper;
use IMEdge\Web\Data\Lookup\MacAddressBlockLookup;
use ipl\Html\BaseHtmlElement;

class MacAddress extends BaseHtmlElement
{
    use TranslationHelper;

    protected const CSS_CLASS = 'imedge-mac-address';

    protected $tag = 'span';
    protected $defaultAttributes = [
        'class' => self::CSS_CLASS
    ];

    protected string $binaryMacAddress;
    protected MacAddressBlockLookup $lookup;
    protected string $extraClass;
    protected bool $isMulticast = false;
    protected bool $isPrivate = false;

    /** @readonly  */
    public string $description;
    /** @readonly  */
    public ?string $additionalInfo = null;
    /** @readonly  */
    public ?string $prefix = null;
    /** @readonly  */
    public ?int $prefixLength = null;

    protected function __construct(string $binaryMacAddress, MacAddressBlockLookup $lookup)
    {
        $this->binaryMacAddress = $binaryMacAddress;
        $this->lookup = $lookup;
        $this->lookup();
    }

    protected function lookup(): void
    {
        $binaryMac = $this->binaryMacAddress;
        $prefix = self::CSS_CLASS;
        // TODO: UAA, LAA
        if (MacAddressHelper::isVRRPv4($binaryMac)) {
            $this->description = $this->translate('IPv4 Virtual Router Redundancy Protocol (VRRP)');
            $this->additionalInfo = sprintf('Virtual Router identifier: %s', ord(substr($binaryMac, -1)));
            $this->extraClass = "$prefix-special";
        } elseif (MacAddressHelper::isVRRPv6($binaryMac)) {
            $this->description = $this->translate('IPv6 Virtual Router Redundancy Protocol (VRRP)');
            $this->additionalInfo = sprintf('Virtual Router identifier: %s', ord(substr($binaryMac, -1)));
            $this->extraClass = "$prefix-special";
        } elseif (MacAddressHelper::isMulticast($binaryMac)) {
            $this->description = $this->translate('Multicast MAC Address');
            $this->extraClass = "$prefix-multicast";
            $this->isMulticast = true;
        } elseif (MacAddressHelper::isLocallyAdministered($binaryMac)) {
            $this->description = $this->translate('Locally administered, private/random MAC');
            $this->extraClass = "$prefix-private";
            $this->isPrivate = true;
        } elseif ($reg = $this->lookup->getRegistration(MacAddressHelper::toText($this->binaryMacAddress))) {
            $this->description = $reg->company;
            $this->prefix = MacAddressHelper::toPrefixText($reg->prefix, $reg->prefixLength);
            $this->prefixLength = $reg->prefixLength;
            $this->additionalInfo = sprintf('%dbit prefix: %s', $this->prefixLength, $this->prefix);
            $this->extraClass = "$prefix-oui";
        } else {
            $this->description = $this->translate('Unknown MAC Address');
            $this->extraClass = "$prefix-unknown";
        }
    }

    public static function parse(string $macAddress, MacAddressBlockLookup $lookup): MacAddress
    {
        return new self(MacAddressHelper::toBinary($macAddress), $lookup);
    }

    public static function fromBinary(string $binaryMacAddress, MacAddressBlockLookup $lookup): MacAddress
    {
        return new self($binaryMacAddress, $lookup);
    }

    protected function assemble()
    {
        $this->add(MacAddressHelper::toText($this->binaryMacAddress));
        $this->addAttributes([
            'class' => $this->extraClass,
            'title' => $this->description . ($this->additionalInfo ? ', ' . $this->additionalInfo : ''),
        ]);
    }
}
