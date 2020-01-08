<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Elastica\Client as EsClient;
use GraphAware\Neo4j\Client\ClientInterface as NeoClient;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Elastica\Type\Mapping;
use Elastica\Document;
use Elastica\Index;
use GraphAware\Bolt\Record\RecordView;

class DataIndexCommand extends Command
{
    protected static $defaultName = 'app:data:index';

    /**
     * @var EsClient
     */
    private $es;

    /**
     * @var NeoClient
     */
    private $neo;

    public function __construct(EsClient $es, NeoClient $neo)
    {
        $this->es = $es;
        $this->neo = $neo;

        parent::__construct();
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::configure()
     */
    protected function configure()
    {
        $this->setDescription('Add data to search index.');
    }

    /**
     * Refresh index.
     *
     * @param string $name
     * @return \Elastica\Index
     */
    private function refreshIndex(string $name): Index
    {
        $index = $this->es->getIndex($name);

        $index->create([
            'number_of_shards' => 4,
            'number_of_replicas' => 1,
        ], true);

        return $index;
    }

    /**
     * Get all the employees as a data array ready for elasticsearch.
     *
     * @return array
     */
    private function getAllEmployees(): array
    {
        $response = $this->neo->run("
            MATCH (e:Employee)-[:HAS_POSITION]->(p:Position)
            RETURN e, p.description
        ");

        $employees = [];
        foreach ($response->records() as $record) {
            $eNode = $record->get('e');
            if (!in_array($eNode->identity(), array_keys($employees))) {
                $id = $eNode->identity();

                $employees[$id] = $eNode->asArray();
                $employees[$id]['id'] = $eNode->identity();
            }

            $employees[$id]['positions'][] = $record->get('p.description');
        }

        return array_map(function ($employee) {
            $employee['fulltext'] = $this->render($employee);

            return $employee;
        }, array_values($employees));
    }

    /**
     * get all departments as a data array ready for elasticsearch.
     *
     * @return array
     */
    private function getAllDepartments(): array
    {
        $response = $this->neo->run("MATCH (d:Department) RETURN d");

        return array_map(function (RecordView $record) {
            $node = $record->get('d');
            $department = $node->asArray();
            $department['id'] = $node->identity();
            $department['fulltext'] = $department['name'];

            return $department;
        }, $response->records());
    }

    /**
     * Render the employee as a string.
     *
     * @param array $employee
     * @return string
     */
    public function render(array $employee): string
    {
        $e = $employee;

        $out = $e['display_name'] ?: $e['first_name'] . ' ' . $e['last_name'];
        $out .= "\n";
        $out .= $e['phone'] . "\n";
        $out .= $e['email'] . "\n";
        $out .= implode("\n", $e['positions']);

        return $out;
    }

    /**
     * {@inheritDoc}
     * @see \Symfony\Component\Console\Command\Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $index          = $this->refreshIndex('directory');
        $employeeType   = $index->getType('employee');

        $empDocs = array_map(function ($employee) {
            return new Document($employee['id'], $employee);
        }, $this->getAllEmployees());
        $employeeType->addDocuments($empDocs);

        $employeeType->getIndex()->refresh();

        $io->writeln('Done');
    }
}
