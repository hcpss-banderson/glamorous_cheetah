<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @MongoDB\Document
 */
class Employee
{
    /**
     * @MongoDB\Id(strategy="NONE", type="string")
     */
    protected $id;

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $email;

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $displayName;

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $lastName;

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $firstName;

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $phone;

    /**
     * @var Position[]
     * @MongoDB\ReferenceMany(
     *   targetDocument=Position::class,
     *   mappedBy="employee",
     *   cascade={"persist", "remove"},
     *   orphanRemoval=false
     * )
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
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get the employee id.
     *
     * @return string
     */
    public function getEmployeeId(): ?string
    {
        return $this->employeeId;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Get display name.
     *
     * @return string
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName ?: $this->firstName . ' ' . $this->lastName;
    }

    /**
     * Get last name.
     *
     * @return string
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * Get first name.
     *
     * @return string
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * Get phone number.
     *
     * @return string
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * Get positions.
     *
     * @return Position[]
     */
    public function getPositions(): Collection
    {
        return $this->positions;
    }

    /**
     * Set the id.
     *
     * @param number $id
     * @return self
     */
    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set the employee id.
     *
     * @param string $employeeId
     * @return self
     */
    public function setEmployeeId(string $employeeId): self
    {
        $this->employeeId = $employeeId;

        return $this;
    }

    /**
     * Set the email.
     *
     * @param string $mail
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Set the display name.
     *
     * @param string $displayName
     * @return self
     */
    public function setDisplayName(string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Set last name.
     *
     * @param string $lastName
     * @return self
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Set the first name.
     *
     * @param string $firstName
     * @return self
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Set the phone number.
     *
     * @param string $phone
     * @return self
     */
    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Set positions.
     *
     * @param Position[] $positions
     * @return self
     */
    public function setPositions(Collection $positions): self
    {
        $positions = $this->dedupePositions($positions);

        foreach ($positions->getValues() as $position) {
            $position->setEmployee($this);
        }

        return $this;
    }

    /**
     * Remove a position.
     *
     * @param Position $position
     * @return self
     */
    public function removePosition(Position $position): self
    {
        if ($this->positions->contains($position)) {
            $this->positions->removeElement($position);
        }

        return $this;
    }

    /**
     * Add a position.
     *
     * @param Position $position
     * @return self
     */
    public function addPosition(Position $position): self
    {
        if (!$this->positions->contains($position)) {
            $this->positions->add($position);
        }

        return $this;
    }
}
