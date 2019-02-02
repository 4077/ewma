<?php namespace ewma\Data;

/**
 * @method static array delete($node)
 * @method static array getIds($node)
 * @method static array getAllSubnodes($node)
 * @method static array getBranch($node, $reverse = false)
 */
class Tree
{
    private $builder;

    private $idFieldName = 'id';

    private $parentIdFieldName = 'parent_id';

    public function __construct($input)
    {
        if (is_string($input)) {
            $this->builder = (new $input)->query();
        } elseif ($input instanceof \Model) {
            $this->builder = $input->query();
        } else {
            $this->builder = $input;
        }
    }

    public function idField($name)
    {
        $this->idFieldName = $name;

        return $this;
    }

    public function parentIdField($name)
    {
        $this->parentIdFieldName = $name;

        return $this;
    }

    public function __call($name, $arguments)
    {
        if ($name == 'delete') {
            if (isset($arguments[0])) {
                $id = $arguments[0];

                return $this->deleteNode($id);
            }
        }

        if ($name == 'getIds') {
            if (isset($arguments[0])) {
                $modelOrId = $arguments[0];

                if ($modelOrId instanceof \Model) {
                    $id = $modelOrId->{$this->idFieldName};
                } else {
                    $id = $modelOrId;
                }

                return $this->getFlatten()->getIds($id);
            }
        }

        if ($name == 'getAllSubnodes') {
            if (isset($arguments[0])) {
                $modelOrId = $arguments[0];

                if ($modelOrId instanceof \Model) {
                    $id = $modelOrId->{$this->idFieldName};
                } else {
                    $id = $modelOrId;
                }

                return $this->getFlatten()->getAllSubnodes($id);
            }
        }

        if ($name == 'getBranch') {
            if (isset($arguments[0])) {
                $modelOrId = $arguments[0];
                $reverse = isset($arguments[1]) ? $arguments[1] : false;

                if ($modelOrId instanceof \Model) {
                    $id = $modelOrId->{$this->idFieldName};
                } else {
                    $id = $modelOrId;
                }

                return $this->getFlatten()->getBranch($id, $reverse);
            }
        }
    }

    public static function __callStatic($name, $arguments)
    {
        if ($name == 'delete') {
            if (isset($arguments[0])) {
                $argument = $arguments[0];

                if ($argument instanceof \Model) {
                    $tree = new self($argument->query());

                    return $tree->deleteNode($argument->id);
                }
            }
        }

        if ($name == 'getIds') {
            if (isset($arguments[0])) {
                $argument = $arguments[0];

                if ($argument instanceof \Model) {
                    $tree = new self($argument->query());

                    return $tree->getIds($argument->id);
                }
            }
        }

        if ($name == 'getAllSubnodes') {
            if (isset($arguments[0])) {
                $argument = $arguments[0];

                if ($argument instanceof \Model) {
                    $tree = new self($argument->query());

                    return $tree->getAllSubnodes($argument->id);
                }
            }
        }

        if ($name == 'getBranch') {
            if (isset($arguments[0])) {
                $model = $arguments[0];
                $reverse = isset($arguments[1]) ? $arguments[1] : false;

                if ($model instanceof \Model) {
                    $tree = new self($model->query());

                    return $tree->getBranch($model->id, $reverse);
                }
            }
        }
    }

    public static function get($builder)
    {
        return new self($builder);
    }

    public function getNode($id)
    {
        return $this->getFlatten()->getNode($id);
    }

    public function getSubnodes($id)
    {
        return $this->getFlatten()->getSubnodes($id);
    }

    public function getFlattenData($id)
    {
        return $this->getFlatten()->getFlattenData($id);
    }

    public function deleteNode($id)
    {
        $ids = $this->getIds($id);

        if ($ids) {
            $modelClass = get_class($this->builder->getModel());

            (new $modelClass)->whereIn($this->idFieldName, $ids)->delete();
        }

        return $ids;
    }

    public function filterIds($filterIds, $rootNodeId)
    {
        if (is_array($filterIds)) {
            $flatten = $this->getFlatten();

            $nodesById = [];
            $nodesByParent = [];

            $recursion = function ($id, $parentId) use (&$recursion, $filterIds, $flatten, &$nodesById, &$nodesByParent) {
                if (in_array($id, $filterIds)) {
                    $nodesById[$id] = $flatten->getNode($id);

                    $parentId = $id;
                }

                foreach ($flatten->getSubnodes($id) as $subnode) {
                    if (in_array($subnode->id, $filterIds)) {
                        $nodesByParent[$parentId][] = $subnode;
                    }

                    $recursion($subnode->id, $parentId);
                }
            };

            $recursion($rootNodeId, $rootNodeId);

            $this->flatten = new \ewma\Data\Tree\Flatten;

            $this->flatten->idFieldName = $this->idFieldName;
            $this->flatten->parentIdFieldName = $this->parentIdFieldName;
            $this->flatten->nodesById = $nodesById;
            $this->flatten->nodesByParent = $nodesByParent;
        }
    }

    private $flatten;

    private function getFlatten()
    {
        if (null === $this->flatten) {
            $nodes = $this->builder->get();

            $nodesById = [];
            $nodesByParent = [];

            foreach ($nodes as $node) {
                $nodesById[(int)$node->{$this->idFieldName}] = $node;
                $nodesByParent[(int)$node->{$this->parentIdFieldName}][] = $node;
            }

            $this->flatten = new \ewma\Data\Tree\Flatten;

            $this->flatten->idFieldName = $this->idFieldName;
            $this->flatten->parentIdFieldName = $this->parentIdFieldName;
            $this->flatten->nodesById = $nodesById;
            $this->flatten->nodesByParent = $nodesByParent;
        }

        return $this->flatten;
    }
}
