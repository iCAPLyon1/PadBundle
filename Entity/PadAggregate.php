<?php

namespace Icap\PadBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="Icap_pad_aggregate")
 */
class PadAggregate extends AbstractResource
{
    /**
     * @ORM\OneToMany(
     *     targetEntity="Icap\PadBundle\Entity\Pad",
     *     mappedBy="aggregate"
     * )
     */
    protected $pads;

    public function getPads()
    {
        return $this->pads;
    }
}