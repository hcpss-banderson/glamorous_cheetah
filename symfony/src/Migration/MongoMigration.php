<?php

namespace App\Migration;

use App\Connection\SamConnection;
use App\Service\SlugifyService;
use Doctrine\ODM\MongoDB\DocumentManager;
use App\Document\Employee;
use App\Document\Location;
use App\Document\Position;
use App\Document\Department;
use Doctrine\ODM\MongoDB\Query\Builder;
use MongoDB\Collection;

class MongoMigration
{
    /**
     * @var DocumentManager
     */
    private $dm;

    /**
     * @var SamConnection
     */
    private $samClient;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var SlugifyService
     */
    private $slugify;

    public function __construct(DocumentManager $dm, SamConnection $samClient, Validator $validator, SlugifyService $slugify)
    {
        $this->dm = $dm;
        $this->samClient = $samClient;
        $this->validator = $validator;
        $this->slugify = $slugify;
    }

    /**
     * Delete all database tables.
     */
    private function deleteAll()
    {
        foreach ([
            Location::class,
            Department::class,
            Employee::class,
            Position::class
        ] as $class) {
            $this->dm
                ->createQueryBuilder($class)
                ->remove()
                ->getQuery()
                ->execute();
        }
    }

    /**
     * Perform the migration.
     */
    public function migrate(bool $deleteOld = true)
    {
        if ($deleteOld) {
            $this->deleteAll();
        }

        $superData = $this->samClient->findOne(['Employee_ID' => 'E07715']);
        $this->createEmployee($superData);
        $this->createDepartmentTree();
    }

    /**
     * Create all relationships between departments.
     */
    private function createDepartmentTree()
    {
        $departments = $this->dm
            ->getRepository(Department::class)
            ->findAll();

        foreach ($departments as $department) {
            $manager = $department->getHead()->getManager();
            if (!$manager) {
                continue;
            }

            $parent = $manager->getDepartment();
            if (!$parent) {
                continue;
            }

            $department->setParent($parent);
            $this->dm->persist($department);
        }

        $this->dm->flush();
    }

    /**
     * Create the employee and all subordinates from the data array.
     *
     * @param array $data
     */
    private function createEmployee(array $data)
    {
        $positionKeys = [
            'Primary_Position', 'Position_2', 'Position_3', 'Position_4',
            'Position_5',
        ];

        $employee = new Employee();
        $employee
            ->setId($data['Employee_ID'])
            ->setDisplayName($data['Display_Name'])
            ->setEmail($data['mail'])
            ->setFirstName($data['First_Name'])
            ->setLastName($data['Last_Name'])
            ->setPhone($data['Work_Phone']);

        foreach ($positionKeys as $weight => $positionKey) {
            if ($data[$positionKey . '_Job_Description']) {
                if (!$this->validator->validatePosition($positionKey, $data)) {
                    continue;
                }

                $location = new Location();
                $location
                    ->setId((int)$data[$positionKey . '_Location_Code'])
                    ->setName($data[$positionKey . '_Location']);

                $this->dm->persist($location);

                $position = new Position();
                $position
                    ->setDescription($data[$positionKey . '_Job_Description'])
                    ->setWeight($weight)
                    ->setLocation($location);

                if ($data[$positionKey . '_Manager']) {
                    $manager = $this->dm
                        ->getRepository(Employee::class)
                        ->find($data[$positionKey . '_Manager']);

                    if ($manager) {
                        $position->setManager($manager->getPositions()->first());
                    }
                }

                $employee->addPosition($position);
            }
        }

        if ($departmentName = $data['Manager_s_Default_Supervisory_Organization']) {
            $department = new Department();
            $department
                ->setId($this->slugify->slugify($departmentName))
                ->setName($departmentName);

            $position = $employee->getPositions()->first();
            $position->setCharge($department);
            $department->setHead($position);

            $this->dm->persist($position);
            $this->dm->persist($department);
        }

        $this->dm->persist($employee);
        $this->dm->flush();

        $subordinateData = $this->samClient->find([
            'Primary_Position_Manager' => $data['Employee_ID'],
        ]);

        if (!empty($subordinateData)) {
            foreach ($subordinateData as $report) {
                if ($this->validator->validateEmployee($report)) {
                    $this->createEmployee($report);
                }
            }
        }
    }
}
