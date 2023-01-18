<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\PowerJoins\PowerJoins;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PermissionGroup extends Model
{
    use HasFactory, PowerJoins, LogsActivity;

    protected $fillable = [
        'name',
        'code'
    ];

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'group_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }
}
