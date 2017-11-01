<?php namespace ewma\callCenter\controllers\app;

class Exchange extends \Controller
{
    private $exportOutput = [];

    public function export()
    {
        $cat = $this->unpackModel('cat') or
        $cat = \ewma\callCenter\models\Cat::find($this->data('cat_id'));

        if ($cat) {
            $tree = \ewma\Data\Tree::get(\ewma\callCenter\models\Cat::orderBy('position'));

            $this->exportOutput['cat_id'] = $cat->id;
            $this->exportOutput['cats'] = $tree->getFlattenData($cat->id);

            $this->exportRecursion($tree, $cat);

            return $this->exportOutput;
        }
    }

    private function exportRecursion(\ewma\Data\Tree $tree, $cat)
    {
        $calls = $cat->calls()->orderBy('position')->get();
        foreach ($calls as $call) {
            $this->exportOutput['calls'][$cat->id][] = $call->toArray();
        }

        $subcats = $tree->getSubnodes($cat->id);
        foreach ($subcats as $subcat) {
            $this->exportRecursion($tree, $subcat);
        }
    }

    public function import()
    {
        $targetCat = $this->unpackModel('cat') or
        $targetCat = \ewma\callCenter\models\Cat::find($this->data('cat_id'));

        $importData = $this->data('data');
        $sourceCatId = $importData['cat_id'];

        $this->importRecursion($targetCat, $importData, $sourceCatId, $this->data('skip_first_level'));

        $this->e('ewma/callCenter/cats/import')->trigger();
    }

    private function importRecursion($targetCat, $importData, $catId, $skipFirstLevel = false)
    {
        $newCatData = $importData['cats']['nodes_by_id'][$catId];

        if ($skipFirstLevel) {
            $newCat = $targetCat;
        } else {
            $newCat = $targetCat->nested()->create($newCatData);
        }

        if (!empty($importData['calls'][$catId])) {
            foreach ($importData['calls'][$catId] as $newCallData) {
                $newCat->calls()->create($newCallData);
            }
        }

        if (!empty($importData['cats']['ids_by_parent'][$catId])) {
            foreach ($importData['cats']['ids_by_parent'][$catId] as $sourceCatId) {
                $this->importRecursion($newCat, $importData, $sourceCatId);
            }
        }
    }
}
