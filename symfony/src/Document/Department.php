<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @MongoDB\Document
 */
class Department
{
    /**
     * @MongoDB\Id(strategy="NONE", type="string")
     */
    protected $id;

    /**
     * @MongoDB\Field(type="string")
     */
    protected $name;

    /**
     * @var Position
     * @MongoDB\ReferenceOne(targetDocument=Position::class, mappedBy="charge")
     */
    protected $head;

    /**
     * @var Department
     * @MongoDB\ReferenceOne(targetDocument=Department::class, inversedBy="children")
     */
    protected $parent;

    /**
     * @var Department[]
     * @MongoDB\ReferenceMany(targetDocument=Department::class, mappedBy="parent")
     */
    protected $children;

    public function __construct()
    {
        $this->children = new ArrayCollection();
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
    public function getChildren(): Collection
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
    public function getId(): ?string
    {
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
    public function setId(string $id): self
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
