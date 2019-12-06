<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Node\Employee;
use App\Node\Department;

class CardController extends AbstractController
{
    /**
     * Render the employee card.
     *
     * @param Employee $employee
     * @param mixed $context
     * @param bool $primary
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function employee(Employee $employee, $context = null, bool $primary = false)
    {
        return $this->render('card/person.html.twig', [
            'person'    => $employee,
            'grey'      => $primary,
        ]);
    }
}
