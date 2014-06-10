<?php

namespace Icap\PadBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="icap_pad_options")
 */
class PadOptions
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", name="endpoint_root", nullable=false)
     */
    protected $endpointRoot;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set endpointRoot
     *
     * @param string $endpointRoot
     * @return PadOptions
     */
    public function setEndpointRoot($endpointRoot)
    {
        $this->endpointRoot = $endpointRoot;

        return $this;
    }

    /**
     * Get endpointRoot
     *
     * @return string 
     */
    public function getEndpointRoot()
    {
        return $this->endpointRoot;
    }
}
