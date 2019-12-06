<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @MongoDB\Document
 */
class Location
{
    /**
     * @MongoDB\Id(strategy="NONE")
     */
    protected $id;

    /**
     * @var int
     * @MongoDB\Field(type="string")
     */
    protected $code;

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $name;

    /**
     * @var Position[]
     * @MongoDB\ReferenceMany(targetDocument=Position::class, mappedBy="location")
     */
    protected $positions;

    public function __construct()
    {
        $this->positions = new ArrayCollection();
    }

    /**
     * Get the id.
     *
     * @return number
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the SAM id.
     *
     * @return number
     */
    public function getCode(): ?int
    {
        return $this->code;
    }

    /**
     * Get the name.
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get positions.
     *
     * @return Position[]|Collection
     */
    public function getPositions(): Collection
    {
        return $this->positions;
    }

    /**
     * Set the ID.
     *
     * @param number $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set the sam ID.
     *
     * @param number $samId
     * @return self
     */
    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Set the name.
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param Position[]|Collection $positions
     * @return self
     */
    public function setPositions(Collection $positions): self
    {
        $this->positions = $positions;

        return $this;
    }
}
