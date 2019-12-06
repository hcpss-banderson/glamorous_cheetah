<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use GraphAware\Neo4j\Client\ClientInterface;
use GraphAware\Neo4j\OGM\EntityManagerInterface;
use App\Node\Department;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Node\Position;
use App\Node\Employee;
use GraphAware\Bolt\Result\Type\Path;
use App\Repository\DepartmentRepository;
use GraphAware\Bolt\Result\Type\Node;

class DefaultController extends AbstractController
{
    /**
     * @var ClientInterface
     */
    private $graphClient;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(ClientInterface $graphClient, EntityManagerInterface $em)
    {
        $this->graphClient = $graphClient;
        $this->em = $em;
    }

    private function departmentTree(&$department)
    {
        $response = $this->graphClient->run("
            MATCH (s:Department)-[:IS_DIVISION_OF]->(p:Department)
            WHERE id(p) = {id}
            RETURN s
        ", ['id' => $department['identity']]);

        $department['children'] = [];
        if ($response->size()) {
            foreach ($response->records() as $record) {
                $node = $record->get('s');
                $subdepartment = $node->asArray();
                $subdepartment['identity'] = $node->identity();

                $this->departmentTree($subdepartment);

                $department['children'][] = $subdepartment;
            }
        }
    }

    /**
     * @Route("/departments", name="department_list")
     */
    public function departmentList()
    {
        $department = $this->em
            ->getRepository(Department::class)
            ->findOneBy(['name' => 'Howard County Public School System']);

        return $this->render('department/list.html.twig', [
            'root' => $department,
        ]);
    }

    /**
     * @Route("/department/{slug}", name="department_show")
     */
    public function departmentShow($slug)
    {
        $department = $this->em
            ->getRepository(Department::class)
            ->findOneBy(['slug' => $slug]);

        return $this->render('department/show.html.twig', [
            'department' => $department,
        ]);
    }

    /**
     * @Route("/", name="search")
     */
    public function search()
    {
        $department = $this->em->getRepository(Department::class)->findOneBy([
            'name' => 'Howard County Public School System',
        ]);

        /** @var Department $department */
        echo $department->getName() . "\n";
        return new Response('pow');


//         $result = $this->graphClient->run("
//             MATCH
//                 (michael)-[:HAS_POSITION]->(manager:Position {description: 'Superintendent'})<-[:REPORTS_TO]-(reports)<-[:HAS_POSITION]-(chiefs),
//                 (subdepartment)-[:IS_HEADED_BY]->(reports),Dev
//                 (manager)<-[:IS_HEADED_BY]-(department)
//             RETURN michael, manager, reports, chiefs, department, subdepartment
//         ");


        $result = $this->graphClient->run("
            MATCH
                (department:Department {name: {department_name}}),
                (department)-[:IS_HEADED_BY]->(head),
                (head)<-[:REPORTS_TO]-(reports),
                (head)<-[:HAS_POSITION]-(manager),
                (reports)<-[:HAS_POSITION]-(employees),
                (head)-[:IS_LOCATED_AT]->(locations),
                (reports)-[:IS_LOCATED_AT]->(locations),
                (subdepartments)-[:IS_HEADED_BY]->(reports)
            RETURN
                department,
                head,
                reports,
                manager,
                employees,
                locations,
                subdepartments
        ", ['department_name' => 'Howard County Public School System']);


        $super = $result->firstRecord()->get('manager')->get('first_name');
        echo "$super\n";

        foreach ($result->records() as $record) {
            /** @var \GraphAware\Bolt\Result\Type\Node $head */
            $head = $record->get('manager');
            echo $head->get('last_name');
            echo "\n";

            //print_r($record);
        }

        return new Response('bing');
    }
}
