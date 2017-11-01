<?php namespace ewma\access\ui\permissions\controllers\app;

class Exchange extends \Controller
{
    private $exportOutput = [];

    public function export()
    {
        $permission = $this->unpackModel('permission') or
        $permission = \ewma\models\access\Permission::find($this->data('permission_id'));

        if ($permission) {
            $tree = \ewma\Data\Tree::get(\ewma\models\access\Permission::orderBy('position'));

            $this->exportOutput['permission_id'] = $permission->id;
            $this->exportOutput['permissions'] = $tree->getFlattenData($permission->id);

//            $this->exportRecursion($tree, $permission);

            return $this->exportOutput;
        }
    }

    private function exportRecursion(\ewma\Data\Tree $tree, $permission)
    {
        // ...

        $subPermissions = $tree->getSubnodes($permission->id);
        foreach ($subPermissions as $subPermission) {
            $this->exportRecursion($tree, $subPermission);
        }
    }

    public function import()
    {
        $targetPermission = $this->unpackModel('permission') or
        $targetPermission = \ewma\models\access\Permission::find($this->data('permission_id'));

        $importData = $this->data('data');
        $sourcePermissionId = $importData['permission_id'];

        $this->importRecursion($targetPermission, $importData, $sourcePermissionId, $this->data('skip_first_level'));

        $this->e('ewma/access/permissions/import')->trigger();
    }

    private function importRecursion($targetPermission, $importData, $permissionId, $skipFirstLevel = false)
    {
        $newPermissionData = $importData['permissions']['nodes_by_id'][$permissionId];

        $newPermissionData['module_namespace'] = $targetPermission->module_namespace;

        if ($skipFirstLevel) {
            $newPermission = $targetPermission;
        } else {
            $newPermission = $targetPermission->nested()->create($newPermissionData);
        }

        if (!empty($importData['permissions']['ids_by_parent'][$permissionId])) {
            foreach ($importData['permissions']['ids_by_parent'][$permissionId] as $sourcePermissionId) {
                $this->importRecursion($newPermission, $importData, $sourcePermissionId);
            }
        }
    }
}
