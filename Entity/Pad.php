<?php

namespace Icap\PadBundle\Entity;

use Claroline\CoreBundle\Entity\Resource\AbstractResource;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="Icap_pad")
 */
class Pad extends AbstractResource
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var url
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $url;
    
    /**
     * @var array<string> $padUsers
     * @ORM\Column(type="array", nullable=true)
     */
    protected $padUsers;

    /**
     * @var string $program
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $program;

    /**
     * @var string $title
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var string $nit
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $unit;

    /**
     * @var string $padOwner
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private $padOwner;

    /**
     * @ORM\ManyToOne(
     *     targetEntity="Icap\PadBundle\Entity\PadAggregate",
     *     inversedBy="pads"
     * )
     * @ORM\JoinColumn(name="aggregate_id", onDelete="CASCADE", nullable=false)
     */
    protected $aggregate;

    /**
     * Create a pad
     * 
     * @param string $url
     * @param string $title
     * @param string $program
     * @param string $unit
     * @param string $padOwner
     * @param \Icap\PadBundle\Entity\PadAggregate $aggregate
     * @param string $padUsers
     */
    public function hydrate($url, $title, $program, $unit, $padOwner, $aggregate, $padUsers = array())
    {
        $this->url = $url;
        $this->title = $title;
        $this->program = $program;
        $this->unit = $unit;
        $this->padOwner = $padOwner;
        $this->aggregate = $aggregate;
        $this->padUsers = $padUsers;

        return $this;
    }

    /**
     * Get the pad object as an array
     * 
     * @return array
     */
    public function toArray()
    {
        $array = array(
            "id" => $this->getId(),
            "title" => $this->getTitle(),
            "padOwner" => $this->getPadOwner(),
            "program" => $this->getProgram(),
            "unit" => $this->getUnit(),
            "padUsers" => $this->getPadUsers()
        );

        return $array;
    }

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
     * Set url
     *
     * @param string $url
     * @return Pad
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set program
     *
     * @param string $program
     * @return Pad
     */
    public function setProgram($program)
    {
        $this->program = $program;

        return $this;
    }

    /**
     * Get program
     *
     * @return string 
     */
    public function getProgram()
    {
        return $this->program;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Pad
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set unit
     *
     * @param string $unit
     * @return Pad
     */
    public function setUnit($unit)
    {
        $this->unit = $unit;

        return $this;
    }

    /**
     * Get unit
     *
     * @return string 
     */
    public function getUnit()
    {
        return $this->unit;
    }

    /**
     * Set padOwner
     *
     * @param string $padOwner
     * @return Pad
     */
    public function setPadOwner($padOwner)
    {
        $this->padOwner = $padOwner;

        return $this;
    }

    /**
     * Get padOwner
     *
     * @return string 
     */
    public function getPadOwner()
    {
        return $this->padOwner;
    }

    /**
     * Set aggregate
     *
     * @param \Icap\PadBundle\Entity\PadAggregate $aggregate
     * @return Pad
     */
    public function setAggregate(\Icap\PadBundle\Entity\PadAggregate $aggregate)
    {
        $this->aggregate = $aggregate;

        return $this;
    }

    /**
     * Get aggregate
     *
     * @return \Icap\PadBundle\Entity\PadAggregate 
     */
    public function getAggregate()
    {
        return $this->aggregate;
    }

    /**
     * Set padUsers
     *
     * @param array $padUsers
     * @return Pad
     */
    public function setPadUsers($padUsers)
    {
        $this->padUsers = $padUsers;

        return $this;
    }

    /**
     * Get padUsers
     *
     * @return array 
     */
    public function getPadUsers()
    {
        return $this->padUsers;
    }
}
