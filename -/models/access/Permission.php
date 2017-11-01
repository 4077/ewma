<?php namespace ewma\models\access;

class Permission extends \Model
{
    use \SleepingOwl\WithJoin\WithJoinTrait;

    protected $table = 'ewma_access_permissions';

    public function nested()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'ewma_access_groups_permissions');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'ewma_access_users_permissions');
    }
}
