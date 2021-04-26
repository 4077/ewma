<?php namespace ewma\Access;

class User
{
    public $model;

    public function __construct(\ewma\access\models\User $user)
    {
        $this->model = $user;
    }

    public function updatePass($pass)
    {
        $this->model->pass = password_hash($pass, PASSWORD_DEFAULT);
        $this->model->save();

        app()->access->auth->logoutOnOtherDevices();
    }

    public function setSessionKey($sessionKey)
    {
        $this->model->session_key = $sessionKey;
        $this->model->save();
    }

    public function setToken($token)
    {
        $this->model->token = $token;
        $this->model->save();
    }

    public function isSuperuser()
    {
        return in_array($this->model->id, app()->getConfig('access/superusers'));
    }

    private $runtimeAddedGroups = [];

    public function runtimeAddGroups($ids)
    {
        merge($this->runtimeAddedGroups, $ids);
    }

    private $allowedPermissionsByModulesNamespaces = [];

    public function getAllowedPermissions(\ewma\Modules\Module $module)
    {
        if (!isset($this->allowedPermissionsByModulesNamespaces[$module->namespace])) {
            $allowed = &$this->allowedPermissionsByModulesNamespaces[$module->namespace];

            $user = $this->model;

            $userGroupsIds = $user->groups()->get()->pluck('id')->all();

            $registeredUserGroup = \ewma\access\models\Group::where('system_type', 'REGISTERED')->first();
            if ($registeredUserGroup) {
                merge($userGroupsIds, $registeredUserGroup->id);
            }

            merge($userGroupsIds, $this->runtimeAddedGroups);

            $userPermissionsByGroups = \ewma\access\models\Permission::where('module_namespace', $module->namespace)
                ->whereHas('groups', function ($query) use ($userGroupsIds) {
                    $query->whereIn('id', $userGroupsIds);
                })->get()->pluck('path')->all();

            merge($allowed, $userPermissionsByGroups);

            $userPermissions = [];
            $user->permissions()
                ->where('module_namespace', $module->namespace)
                ->get()
                ->each(function ($permission) use (&$userPermissions) {
                    $userPermissions[$permission->path] = $permission->pivot->mode;
                });

            foreach ($userPermissions as $permissionPath => $mode) {
                if ($mode == 'MERGE') {
                    merge($allowed, $permissionPath);
                }

                if ($mode == 'DIFF') {
                    diff($allowed, $permissionPath);
                }
            }
        }

        return $this->allowedPermissionsByModulesNamespaces[$module->namespace];
    }

    private $checkedPermissions = [];

    public function hasPermission(\ewma\Controllers\Controller $controller, $path = '')
    {
        if ($this->isSuperuser()) {
            return true;
        }

        $cacheIndex = $controller->_module()->namespace . ' ' . $path;

        if (!isset($this->checkedPermissions[$cacheIndex])) {
            if ($path == '') {
                $path = '^:';
            }

            if (false !== strpos($path, ':')) {
                list($moduleNamespace, $permissionPattern) = explode(':', $path);

                if ($moduleNamespace == '^') {
                    $moduleNamespace = $controller->_masterModule()->namespace;
                }

                $module = app()->modules->getByNamespace($moduleNamespace);
            } else {
                $permissionPattern = $path;
                $module = $controller->_module();
            }

            if ($module) {
                $allowedPermissions = $this->getAllowedPermissions($module);

                if (in_array('', $allowedPermissions)) {
                    $allow = true;
                } else {
                    $checkPermissionsList = [];

                    if (false !== strpos($permissionPattern, '*')) {
                        $regexp = '/(' . str_replace(['/', '*'], ['\/', '[\w|\/]*'], $permissionPattern) . ')\|/U';
                        preg_match_all($regexp, implode('|', $allowedPermissions) . '|', $matches);
                        merge($checkPermissionsList, $matches[1]);
                    } else {
                        $permissionArray = explode('/', $permissionPattern);
                        for ($i = 0; $i < count($permissionArray); $i++) {
                            $checkPermissionsList[] = a2p(array_slice($permissionArray, 0, $i + 1));
                        }
                    }

                    $allow = array_intersect($checkPermissionsList, $allowedPermissions) ? true : false;

                    if (!$allow) {
//                        $controller->console('access denied: ' . $path);
                    }
                }

                $this->checkedPermissions[$cacheIndex] = $allow;
            } else {
                $this->checkedPermissions[$cacheIndex] = false;
            }
        }

        return $this->checkedPermissions[$cacheIndex];
    }
}
