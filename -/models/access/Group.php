<?php namespace ewma\models\access;

class Group extends \Model
{
    use \SleepingOwl\WithJoin\WithJoinTrait;

    protected $table = 'ewma_access_groups';

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'ewma_access_groups_permissions');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'ewma_access_users_groups');
    }
}

class GroupObserver
{
    public function creating($model)
    {
        $position = Group::max('position') + 10;

        $model->position = $position;
    }
}

Group::observe(new GroupObserver);
