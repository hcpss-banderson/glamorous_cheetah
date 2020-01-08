<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\GraphRepository;

class DepartmentController extends AbstractController
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
     * @Route("/department/{slug}", name="department_show")
     */
    public function show($slug)
    {
        $department = $this->gr->getDepartmentWithParentAndChildren($slug);
        $reports = $this->gr->getDepartmentStaff($slug);

        return $this->render('department/show.html.twig', [
            'department' => $department,
            'reports' => $reports,
        ]);
    }

    /**
     * @Route("/departments", name="department_list")
     */
    public function list()
    {
        $root = $this->gr->tree('IS_DIVISION_OF');

        return $this->render('department/list.html.twig', [
            'root' => $root,
        ]);
    }
}
