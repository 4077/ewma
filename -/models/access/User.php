<?php namespace ewma\models\access;

class User extends \Model
{
    use \SleepingOwl\WithJoin\WithJoinTrait;

    protected $table = 'ewma_access_users';

    public $timestamps = true;

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'ewma_access_users_groups');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'ewma_access_users_permissions')->withPivot('mode');
    }

    public static function getByLogin($login)
    {
        return self::where('login', $login)->first();
    }

    public static function getByEmail($email)
    {
        return self::where('email', $email)->first();
    }

    public static function getByPhone($phone)
    {
        return self::where('phone', $phone)->first();
    }

    public static function getByUniqueField($value)
    {
        $user = static::getByLogin($value) or
        $user = static::getByEmail($value) or
        $user = static::getByPhone($value);

        return $user;
    }
}
