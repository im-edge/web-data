<?php

namespace IMEdge\Web\Data\Widget;

use gipfl\Translation\TranslationHelper;
use IMEdge\Web\Data\Lookup\AutonomousSystemLookup;
use ipl\Html\BaseHtmlElement;

class AutonomousSystem extends BaseHtmlElement
{
    use TranslationHelper;

    protected $tag = 'span';
    protected $defaultAttributes = [
        'class' => 'autonomous-system'
    ];

    /** @readonly  */
    public int $asn;
    /** @readonly  */
    public string $handle;
    /** @readonly  */
    public string $description;
    protected AutonomousSystemLookup $lookup;

    public function __construct(int $asn, AutonomousSystemLookup $lookup)
    {
        $this->asn = $asn;
        $this->lookup = $lookup;
        if ($info = $this->lookup->lookup($this->asn)) {
            $this->addAttributes(['title' => sprintf('%s: %s', $info->handle, $info->description)]);
            $this->handle = $info->handle;
            $this->description = $info->description;
        }
    }

    protected function assemble()
    {
        $this->add($this->asn);
    }
}
