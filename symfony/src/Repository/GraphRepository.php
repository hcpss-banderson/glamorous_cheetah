<?php

namespace App\Repository;

use GraphAware\Neo4j\Client\ClientInterface;
use GraphAware\Bolt\Record\RecordView;
use GraphAware\Bolt\Result\Type\Node;
use GraphAware\Common\Graph\NodeInterface;

class GraphRepository
{
    /**
     * @var ClientInterface
     */
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Get one model using a cypher query.
     *
     * @param string $query
     *   The Cypher query
     * @param array $params
     *   Params to use in the query.
     * @return array
     *   The model.
     */
    public function getOneByCypher(string $query, array $params)
    {
        $response = $this->client->run($query, $params);
        $record = $response->firstRecord();
        $node = $record->get($record->keys()[0]);

        $model = $node->asArray();
        $model['identity'] = $node->identity();

        return $model;
    }

    /**
     * Get many modles for the given Cypher.
     *
     * @param string $query
     *   The Cypher query.
     * @param array $params
     * @return array[]
     */
    public function getManyByCypher(string $query, array $params)
    {
        $models = [];
        $response = $this->client->run($query, $params);

        foreach ($response->records() as $record) {
            $node = $record->get($record->keys()[0]);
            $models[] = $this->nodeToArray($node);
        }

        return $models;
    }

    /**
     * Get the nodes in the given relationship as a tree.
     *
     * @param string $relationship
     * @return array
     */
    public function tree(string $relationship)
    {
        $response = $this->client->run("
            MATCH p=()-[r:{$relationship}]->() RETURN p
        ");

        $records = $response->records();

        $tree = $this->nodeToArray($this->findRoot($records));
        $this->buildTree($tree, $records);

        return $tree;
    }

    /**
     * Get the department and it's parent and children
     *
     * @param string $slug
     * @return array
     */
    public function getDepartmentWithParentAndChildren(string $slug)
    {
        $response = $this->client->run("
            MATCH (d:Department {slug: {slug}})
            OPTIONAL MATCH (d)-[:IS_DIVISION_OF]->(p)
            OPTIONAL MATCH (d)<-[:IS_DIVISION_OF]-(c)
            RETURN d, p, c
            ORDER BY c.name
        ", ['slug' => $slug]);

        $department = $this->nodeToArray($response->firstRecord()->get('d'));

        $pNode = $response->firstRecord()->get('p');
        $department['parent'] = $pNode ? $this->nodeToArray($pNode) : null;

        $department['children'] = [];
        foreach ($response->records() as $record) {
            if ($cNode = $record->get('c')) {
                $department['children'][] = $this->nodeToArray($cNode);
            }
        }

        return $department;
    }

    /**
     * Get the staff for the department with the given slug.
     *
     * @param string $slug
     * @return array[][]|array[]
     */
    public function getDepartmentStaff(string $slug)
    {
        $response = $this->client->run("
            MATCH
                (d:Department {slug: {slug}}),
                (d)-[:IS_HEADED_BY]->(mp:Position)<-[:HAS_POSITION]-(me:Employee),
                (mp)<-[:REPORTS_TO]-(sp:Position)<-[:HAS_POSITION]-(se:Employee)
            RETURN me, se
            ORDER BY se.last_name
        ", ['slug' => $slug]);

        $staff = [$this->nodeToArray($response->firstRecord()->get('me'))];
        foreach ($response->records() as $record) {
            $member = $this->nodeToArray($record->get('se'));
            $staff[$member['employee_id']] = $member;
        }

        return $staff;
    }

    /**
     * Get the department loaded with the location.
     *
     * @param string $slug
     * @return array
     */
    public function getDepartmentWithLocation(string $slug)
    {
        $response = $this->client->run("
            MATCH
                (d:Department {slug: {slug}}),
                (d)-[:IS_HEADED_BY]->(p:Position)-[:IS_LOCATED_AT]->(l:Location)
            RETURN d, l
        ", ['slug' => $slug]);

        $dNode = $response->firstRecord()->get('d');
        $lNode = $response->firstRecord()->get('l');

        $department = $dNode->asArray();
        $department['location'] = $lNode->asArray();

        return $department;
    }

    /**
     * Get the positions eager loaded with department.
     *
     * @param string $employee_id
     * @return array[]
     */
    public function getPositionsWithDepartment(string $employee_id)
    {
        $response = $this->client->run("
            MATCH (p:Position)<-[:HAS_POSITION]-(e:Employee {employee_id: {employee_id}})
            OPTIONAL MATCH (pd:Department)-[:IS_HEADED_BY]->(:Position)<-[:REPORTS_TO]-(p)
            OPTIONAL MATCH (p)<-[:IS_HEADED_BY]-(hd:Department)
            RETURN p, pd, hd
        ", ['employee_id' => $employee_id]);

        $positions = [];
        foreach ($response->records() as $record) {
            $position = $this->nodeToArray($record->get('p'));

            if ($dNode = $record->get('hd')) {
                $position['department'] = $this->nodeToArray($dNode);
            } else {
                $position['department'] = $this->nodeToArray($record->get('pd'));
            }

            $positions[] = $position;
        }

        return $positions;
    }

    /**
     * Conver the node to an array.
     *
     * @param Node $node
     * @return array
     */
    public function nodeToArray(Node $node): array
    {
        $model = $node->asArray();
        $model['identity'] = $node->identity();

        return $model;
    }

    /**
     * Build a tree of departments
     *
     * @param array $tree
     * @param RecordView[] $records
     */
    private function buildTree(&$tree, &$records)
    {
        for ($i = 0; $i < count($records); $i++) {
            $relationship = $records[$i]->get('p');
            if ($tree['identity'] == $relationship->end()->identity()) {
                $child = $relationship->start()->asArray();
                $child['identity'] = $relationship->start()->identity();
                $this->buildTree($child, $records);
                $tree['children'][] = $child;
            }
        }
    }

    /**
     * Find the root node.
     *
     * @param RecordView $records
     */
    private function findRoot(array $records): ?Node
    {
        $startIds = [];
        $endIds = [];

        foreach ($records as $record) {
            $startNode = $record->get('p')->start();
            $endNode = $record->get('p')->end();

            $startIds[$startNode->identity()] = $startNode;
            $endIds[$endNode->identity()] = $endNode;
        }

        $diff = array_diff_key($endIds, $startIds);

        return array_shift($diff);
    }
}
