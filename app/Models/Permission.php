<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\PowerJoins\PowerJoins;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Permission extends Model
{
    use HasFactory, PowerJoins, LogsActivity;

    protected $fillable = [
        'group_id',
        'name',
        'code'
    ];

    public function permissionGroup()
    {
        return $this->belongsTo(PermissionGroup::class, 'group_id');
    }

    public function rolePermission()
    {
        return $this->hasMany(RolePermission::class, 'permission_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }
}
