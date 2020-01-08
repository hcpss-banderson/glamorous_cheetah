<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Elastica\Client;
use Elastica\Search;
use Elastica\Query;
use Elastica\Query\Term;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Elastica\Result;

class SearchController extends AbstractController
{
    /**
     * @var Client
     */
    private $es;

    public function __construct(Client $es)
    {
        $this->es = $es;
    }

    /**
     * @Route("/", name="home")
     */
    public function search(Request $request)
    {
        $results = null;
        $input = new \stdClass();
        $input->query = '';

        $form = $this->createFormBuilder($input, [
            'attr' => ['class' => 'contents dir-search smtb-mg']
        ])
        ->setMethod('GET')
        ->add('query', TextType::class, [
            'attr' => ['placeholder', 'Search our directory'],
            'label' => 'Search:',
        ])
        ->add('search', SubmitType::class, [
            'label' => 'Search',
        ])
        ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $input = $form->getData();

            $search = new Search($this->es);
            $search
                ->addIndex('directory')
                ->addTypes(['employee', 'department']);

            $query = new Query([
                'query' => [
                    'wildcard' => [
                        '_all' => [
                            'value' => '*' . strtolower($input->query) . '*',
                            'boost' => '1.0',
                            'rewrite' => 'constant_score',
                        ],
                    ],
                ],
            ]);

            $search->setQuery($query);

            $resultSet = $search->search();
            $results = array_map(function (Result $result) {
                return $result->getSource();
            }, $resultSet->getResults());
        }

        return $this->render('home.html.twig', [
            'form' => $form->createView(),
            'results' => $results,
        ]);
    }
}
