<?php

namespace App\Node;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * @OGM\Node(label="Location")
 */
class Location
{
    /**
     * @var int
     *
     * @OGM\GraphId()
     */
    protected $id;

    /**
     * @var int
     *
     * @OGM\Property(type="int", nullable=false)
     */
    protected $code;

    /**
     * @var string
     *
     * @OGM\Property(type="string", nullable=false)
     */
    protected $name;

    /**
     * @var Position[]|Collection
     *
     * @OGM\Relationship(
     *   type="IS_LOCATED_AT",
     *   direction="INCOMING",
     *   collection=true,
     *   mappedBy="location",
     *   targetEntity="Position"
     * )
     */
    protected $positions;

    public function __construct()
    {
        $this->positions = new Collection();
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
    public function getPositions(): ?Collection
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
