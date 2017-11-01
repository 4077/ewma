<?php namespace ewma\handlers\models;

use SleepingOwl\WithJoin\WithJoinTrait;

class Assignment extends \Model
{
    use WithJoinTrait;

    protected $table = 'ewma_handlers_assignments';

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function nested()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function used()
    {
        if ($this->source_used) {
            return $this->source;
        } else {
            return $this;
        }
    }

    public function source()
    {
        return $this->belongsTo(self::class, 'source_id');
    }
}

class AssignmentObserver
{
    public function creating($model)
    {
        $position = Assignment::max('position') + 10;

        $model->position = $position;
    }
}

Assignment::observe(new AssignmentObserver);
