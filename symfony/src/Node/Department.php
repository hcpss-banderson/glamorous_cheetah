<?php

namespace App\Node;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;
use GraphAware\Common\Collection\CollectionInterface;
use GraphAware\Neo4j\OGM\Proxy\LazyCollection;

/**
 * @OGM\Node(label="Department")
 */
class Department
{
    /**
     * @OGM\GraphId()
     */
    protected $id;

    /**
     * @OGM\Property(type="string", nullable=false)
     */
    protected $name;

    /**
     * @OGM\Property(type="string", nullable=false)
     */
    protected $slug;

    /**
     * @var Position
     * @OGM\Relationship(
     *   type="IS_HEADED_BY",
     *   direction="OUTGOING",
     *   collection=false,
     *   mappedBy="charge",
     *   targetEntity="Position"
     * )
     */
    protected $head;

    /**
     * @var Department
     *
     * @OGM\Relationship(
     *   type="IS_DIVISION_OF",
     *   direction="OUTGOING",
     *   collection=false,
     *   mappedBy="children",
     *   targetEntity="Department"
     * )
     */
    protected $parent;

    /**
     * @var Department[]|Collection
     *
     * @OGM\Relationship(
     *   type="IS_DIVISION_OF",
     *   direction="INCOMING",
     *   collection=true,
     *   mappedBy="parent",
     *   targetEntity="Department"
     * )
     */
    protected $children;

    public function __construct()
    {
        $this->children = new Collection();
    }

    /**
     * @return mixed
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     * @return self
     */
    public function setSlug($slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get the parent department.
     *
     * @return Department
     */
    public function getParent(): ?Department
    {
        return $this->parent;
    }

    /**
     * Get children departments.
     *
     * @return Department[]|Collection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param \App\Node\Department $parent
     * @return self
     */
    public function setParent(Department $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @param Department[]|Collection $children
     * @return self
     */
    public function setChildren(Collection $children): self
    {
        $this->children = $children;

        return $this;
    }

    /**
     * Get the id.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
//         $l = new LazyCollection($initializer, $node, $object, $relationshipMetadata);
//         $l->c

        return $this->id;
    }

    /**
     * Get the department name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the department head.
     *
     * @return Position
     */
    public function getHead(): ?Position
    {
        return $this->head;
    }

    /**
     * Set the ID.
     *
     * @param int $id
     * @return self
     */
    public function setId(int $id): self
    {
        $this->id = $id;

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
     * Set the position.
     *
     * @param Position $head
     * @return self
     */
    public function setHead(Position $head): self
    {
        $this->head = $head;

        return $this;
    }
}
