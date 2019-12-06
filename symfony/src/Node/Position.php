<?php

namespace App\Node;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * @OGM\Node(label="Position")
 */
class Position
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
     * @OGM\Property(type="string", nullable=false)
     */
    protected $description;

    /**
     * We just neet this to be able to easily query positions.
     *
     * @var string
     *
     * @OGM\Property(type="string", key="position_id", nullable=false)
     */
    protected $positionId;

    /**
     * @var int
     *
     * @OGM\Property(type="int", nullable=false)
     */
    protected $weight;

    /**
     * @var Employee
     *
     * @OGM\Relationship(
     *   type="HAS_POSITION",
     *   direction="INCOMING",
     *   collection=false,
     *   mappedBy="positions",
     *   targetEntity="Employee"
     * )
     */
    protected $employee;

    /**
     * @var Location
     *
     * @OGM\Relationship(
     *   type="IS_LOCATED_AT",
     *   direction="OUTGOING",
     *   collection=false,
     *   mappedBy="positions",
     *   targetEntity="Location"
     * )
     */
    protected $location;

    /**
     * @var Position
     *
     * @OGM\Relationship(
     *   type="REPORTS_TO",
     *   direction="OUTGOING",
     *   collection=false,
     *   mappedBy="reports",
     *   targetEntity="Position"
     * )
     */
    protected $manager;

    /**
     * @var Position[]|Collection
     *
     * @OGM\Relationship(
     *   type="REPORTS_TO",
     *   direction="INCOMING",
     *   collection=true,
     *   mappedBy="manager",
     *   targetEntity="Position"
     * )
     */
    protected $reports;

    /**
     * @var Department
     *
     * @OGM\Relationship(
     *   type="IS_HEADED_BY",
     *   direction="INCOMING",
     *   collection=false,
     *   mappedBy="head",
     *   targetEntity="Department"
     * )
     */
    protected $charge;

    public function __construct() {
        $this->reports = new Collection();
    }

    /**
     * @return string
     */
    public function getPositionId(): ?string
    {
        return $this->positionId;
    }

    /**
     * @param string $positionId
     * @return self
     */
    public function setPositionId($positionId): self
    {
        $this->positionId = $positionId;

        return $this;
    }

    /**
     * Get the id.
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get the description.
     *
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get the employee.
     *
     * @return Employee
     */
    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    /**
     * Get the location.
     *
     * @return Location
     */
    public function getLocation(): ?Location
    {
        return $this->location;
    }

    /**
     * Get the weight.
     *
     * @return int
     */
    public function getWeight(): ?int
    {
        return $this->weight;
    }

    /**
     * Get the position
     *
     * @return Position
     */
    public function getManager(): ?Position
    {
        return $this->manager;
    }

    /**
     * Get reports.
     *
     * @return Position[]|Collection
     */
    public function getReports()
    {
        return $this->reports;
    }

    /**
     * Get the charge.
     *
     * @return Department
     */
    public function getCharge(): ?Department
    {
        return $this->charge;
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
     * Set the description.
     *
     * @param string $description
     * @return self
     */
    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Set the employee.
     *
     * @param Employee $employee
     * @return self
     */
    public function setEmployee(Employee $employee): self
    {
        $this->employee = $employee;
        $employee->addPosition($this);

        return $this;
    }

    /**
     * Set the location.
     *
     * @param Location $location
     * @return self
     */
    public function setLocation(Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Set the weight.
     *
     * @param number $weight
     * @return self
     */
    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * Add report.
     *
     * @param Position $report
     * @return self
     */
    public function addReport(Position $report): self
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
        }

        return $this;
    }

    /**
     * Set the manager.
     *
     * @param \App\Node\Position $manager
     * @return self
     */
    public function setManager(?Position $manager): self
    {
        $this->manager = $manager;

        if ($manager) {
            $manager->addReport($this);
        }

        return $this;
    }

    /**
     * Set the reports.
     *
     * @param Position[]|Collection $reports
     * @return self
     */
    public function setReports(Collection $reports): self
    {
        $this->reports = $reports;

        return $this;
    }

    /**
     * Set charge.
     *
     * @param Department $charge
     * @return self
     */
    public function setCharge(Department $charge): self
    {
        $this->charge = $charge;

        if ($this !== $charge->getHead()) {
            $charge->setHead($this);
        }

        return $this;
    }

    /**
     * Get the department this position is in.
     *
     * @return Department|NULL
     */
    public function getDepartment(): ?Department
    {
        // Seems to be a lazy loading bug...
        $this->getCharge();
        $this->getManager();

        if ($this->charge) {
            return $this->charge;
        }

        if ($this->manager) {
            return $this->manager->getDepartment();
        }

        return null;
    }
}
