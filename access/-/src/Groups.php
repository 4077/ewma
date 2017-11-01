<?php namespace ewma\access;

class Groups
{
    public static function createSystemGroups()
    {
        if (!\ewma\models\access\Group::where('system', true)->where('system_type', 'REGISTERED')->first()) {
            \ewma\models\access\Group::create([
                                                  'system'      => true,
                                                  'system_type' => 'REGISTERED',
                                                  'name'        => 'Зарегистрированные пользователи'
                                              ]);
        }
    }

    public static function create()
    {
        return \ewma\models\access\Group::create([]);
    }

    public static function delete(\ewma\models\access\Group $group)
    {
        $group->permissions()->detach();
        $group->delete();
    }

    // todo модуль не нужен
    public static function togglePermissionLink(
        \ewma\Modules\Module $module,
        \ewma\models\access\Group $group,
        \ewma\models\access\Permission $permission
    ) {
        if ($group && $module && $permission) {
            $link = $group->permissions()->where('id', $permission->id)->first();

            if ($link) {
                $group->permissions()->detach([$permission->id]);
            } else {
                $group->permissions()->detach([$permission->id]);
                $group->permissions()->attach([$permission->id]);
            }
        }
    }
}
