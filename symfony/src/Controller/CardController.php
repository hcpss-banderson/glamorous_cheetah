<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\GraphRepository;

class CardController extends AbstractController
{
    /**
     * @var GraphRepository
     */
    private $gr;

    public function __construct(GraphRepository $gr)
    {
        $this->gr = $gr;
    }

    /**
     * Render the employee card.
     *
     * @param array $employee
     * @param mixed $context
     * @param bool $primary
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function employee(array $employee, $context = null, bool $primary = false)
    {
        $employee['positions'] = $this->gr->getPositionsWithDepartment(
            $employee['employee_id']
        );

        $location = $this->gr->getOneByCypher("
            MATCH
                (l:Location)<-[:IS_LOCATED_AT]-(p:Position)<-[:HAS_POSITION {primary: true}]-(e:Employee)
            WHERE e.employee_id = {employee_id}
            RETURN l
        ", ['employee_id' => $employee['employee_id']]);

        return $this->render('card/person.html.twig', [
            'person'   => $employee,
            'location' => $location,
            'grey'     => $primary,
        ]);
    }
}
