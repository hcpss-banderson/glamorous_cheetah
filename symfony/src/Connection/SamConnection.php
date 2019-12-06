<?php

namespace App\Connection;

use GuzzleHttp\ClientInterface;

class SamConnection {

    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Perform a search on SAM.
     *
     * @param array $conditions
     * @throws \Exception
     * @return mixed
     */
    public function find(array $conditions) {
        $uri = '/api/public/search/user';

        $response = $this->client->get($uri, ['query' => ['q' => $conditions]]);

        $data = json_decode($response->getBody(), true);

        if (!$data || !$data['success']) {
            // Something went wrong.
            throw new \Exception("Could not get data for {$uri}.");
        }

        return $data['message'];
    }

    /**
     * Get the direct reports for the employee with the given e number.
     *
     * @param string $eNumber
     * @throws \Exception
     * @return array
     */
    public function getDirectReports(string $eNumber): array {
        return $this->find(['Primary_Position_Manager' => $eNumber]);
    }

    /**
     * FInd one employee.
     *
     * @param array $query
     * @return array|NULL
     */
    public function findOne(array $query): ?array {
        $data = $this->find($query);

        return !empty($data) ? $data[0] : null;
    }

    /**
     * Get departments for the given location.
     *
     * @param int $location
     * @throws \Exception
     * @return array
     */
    public function getDepartmentsForLocation(int $location): array {
        $uri = '/api/public/field/Manager_s_Default_Supervisory_Organization/values';

        $response = $this->client->get($uri, ['query' => [
            'q[Primary_Position_Location_Code]' => $location,
        ]]);

        $data = json_decode($response->getBody(), true);

        if (!$data || !$data['success']) {
            // Something went wrong.
            throw new \Exception("Could not get data for {$uri}.");
        }

        $departments = [];
        foreach ($data['message'] as $department) {
            if ($department && !in_array($department, $departments)) {
                $departments[] = $department;
            }
        }

        return $departments;
    }

    /**
     * Get the department head for the give department.
     *
     * @param string $department
     * @throws \Exception
     * @return array
     */
    public function getDepartmentHead(string $department): array {
        $data = $this->find(['Manager_s_Default_Supervisory_Organization' => $department]);

        return $data[0];
    }

    /**
     * Get all central office department names.
     *
     * @return array
     */
    public function getDepartments($locations): array {
        $departments = [];
        foreach ($locations as $location) {
            $departments = array_merge(
                $departments,
                $this->getDepartmentsForLocation($location)
            );
        }

        return $departments;
    }
}
