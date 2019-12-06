<?php

namespace App\Migration;

class Validator
{
    /**
     * @var array
     */
    private $centralLocations = [
        44,  // Central Office
        45,  // Old Cedar Lane School
        99,  // Mendenhall Building
        100, // Berger Road Building
        96,  // Dorsey Building
        95,  // Ascend One Center
        80,  // Ridge Road Building
        85,  // Warehouse
        13,  // Applications and Research Lab (ARL)
    ];

    /**
     * Validate the position.
     *
     * @param string $key
     * @param array $data
     * @return bool
     */
    public function validatePosition(string $key, array $data): bool
    {
        if (
            strpos($data["{$key}_Job_Description"], 'Temporary ') !== false ||
            strpos($data["{$key}_Job_Description"], 'Temp ') !== false
            ) {
                return false;
            }

            if ($data["{$key}_Location"] == 'Sub/Boe/Loan/Admlv') {
                return false;
            }

            if (!in_array((int)$data["{$key}_Location_Code"], $this->centralLocations)) {
                return false;
            }

            return true;
    }

    /**
     * Validate the user data.
     *
     * @param array $data
     * @return boolean
     */
    public function validateEmployee(array $data): bool
    {
        $subLocation = (int)$data['Primary_Position_Location_Code'];
        if (!in_array($subLocation, $this->centralLocations)) {
            return false;
        }

        if (empty($data['mail'])) {
            return false;
        }

        if (!array_key_exists('enabled', $data)) {
            return false;
        }

        if (!$this->validatePosition('Primary_Position', $data)) {
            return false;
        }

        if (strpos($data['Manager_s_Default_Supervisory_Organization'], '(ARL)') !== false) {
            return false;
        }

        return $data['enabled'];
    }
}
