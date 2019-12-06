<?php

namespace App\Node;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * @OGM\Node(label="Employee")
 */
class Employee
{
    /**
     * @var int
     *
     * @OGM\GraphId()
     */
    protected $id;

    /**
     * @var string
     *
     * @OGM\Property(type="string", key="employee_id", nullable=false)
     */
    protected $employeeId;

    /**
     * @var string
     *
     * @OGM\Property(type="string", nullable=true)
     */
    protected $email;

    /**
     * @var string
     *
     * @OGM\Property(type="string", key="display_name", nullable=false)
     */
    protected $displayName;

    /**
     * @var string
     *
     * @OGM\Property(type="string", key="last_name", nullable=false)
     */
    protected $lastName;

    /**
     * @var string
     *
     * @OGM\Property(type="string", key="first_name", nullable=false)
     */
    protected $firstName;

    /**
     * @var string
     *
     * @OGM\Property(type="string", nullable=true)
     */
    protected $phone;

    /**
     * @var Position[]|Collection
     *
     * @OGM\Relationship(
     *   type="HAS_POSITION",
     *   direction="OUTGOING",
     *   collection=true,
     *   mappedBy="employee",
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
     * Due to an bug in the OGM, there are sometimes dupllicates in the
     * collections.
     *
     * @see https://github.com/graphaware/neo4j-php-ogm/issues/169
     * @param Position[]|Collection $positions
     * @return Position[]|Collection
     */
    private function dedupePositions($positions)
    {
        $ids = [];
        for ($i = 0; $i < $positions->count(); $i++) {
            $id = $positions[$i]->getPositionId();
            if (in_array($id, $ids)) {
                $positions->remove($i);
            } else {
                $ids[] = $id;
            }
        }

        return $positions;
    }

    /**
     * Get positions.
     *
     * @return Position[]|Collection
     */
    public function getPositions()
    {
        return $this->dedupePositions($this->positions);
    }

    /**
     * Set the id.
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
     * @param Position[]|Collection $positions
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
