<?php namespace ewma\Data\Tree;

class Flatten
{
    public $idFieldName = 'id';

    public $parentIdFieldName = 'parent_id';

    public $nodesById = [];

    public $nodesByParent = [];

    public function getNode($id)
    {
        if (!isset($this->nodesById[$id])) {
            $this->nodesById[$id] = [];
        }

        return $this->nodesById[$id];
    }

    public function getSubnodes($id)
    {
        if (!isset($this->nodesByParent[$id])) {
            $this->nodesByParent[$id] = [];
        }

        return $this->nodesByParent[$id];
    }

    private $level = 0;

    private $branch = [];

    public function getBranch($idOrModel, $reverse = false)
    {
        if ($idOrModel instanceof \Model) {
            $id = $idOrModel->{$this->idFieldName};
        } else {
            $id = $idOrModel;
        }

        if (isset($this->nodesById[$id])) {
            $this->branch = [];
            $this->branchRecursion($id);

            if ($reverse) {
                return $this->branch;
            } else {
                return array_reverse($this->branch);
            }
        }
    }

    private function branchRecursion($id)
    {
        $this->branch[$this->level] = $this->nodesById[$id];

        $this->level++;

        if ($this->nodesById[$id][$this->parentIdFieldName] > 0) {
            $this->branchRecursion($this->nodesById[$id][$this->parentIdFieldName]);
        }

        $this->level--;
    }

    private $ids;

    public function getIds($id)
    {
        $this->getIdsRecursion($id);

        return $this->ids;
    }

    private function getIdsRecursion($id)
    {
        merge($this->ids, $id);

        $subnodes = $this->getSubnodes($id);
        foreach ($subnodes as $subnode) {
            $this->getIdsRecursion($subnode->{$this->idFieldName});
        }
    }

    private $allSubnodes;

    public function getAllSubnodes($id)
    {
        $this->getAllSubnodesRecursion($id);

        unset($this->allSubnodes[$id]); // todo сделать симметрично с getIds

        return $this->allSubnodes;
    }

    private function getAllSubnodesRecursion($id)
    {
        $this->allSubnodes[$id] = $this->getNode($id);

        $subnodes = $this->getSubnodes($id);
        foreach ($subnodes as $subnode) {
            $this->getAllSubnodesRecursion($subnode->{$this->idFieldName});
        }
    }

    private $flattenData = [
        'nodes_by_id'   => [],
        'ids_by_parent' => [],
        'parents_by_id' => [],
        'ids_by_order'  => []
    ];

    public function getFlattenData($id)
    {
        $this->getFlattenDataRecursion($id);

        return $this->flattenData;
    }

    private function getFlattenDataRecursion($id)
    {
        $this->flattenData['nodes_by_id'][$id] = $this->getNode($id);

        foreach ($this->getSubnodes($id) as $subnode) {
            $this->flattenData['ids_by_order'][] = $subnode->{$this->idFieldName};
            $this->flattenData['ids_by_parent'][$id][] = $subnode->{$this->idFieldName};
            $this->flattenData['parents_by_id'][$subnode->{$this->idFieldName}] = $id;

            $this->getFlattenDataRecursion($subnode->{$this->idFieldName});
        }
    }
}
