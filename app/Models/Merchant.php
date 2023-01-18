<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\PowerJoins\PowerJoins;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Merchant extends Model
{
    use HasFactory, PowerJoins, LogsActivity;

    protected $fillable = [
        'name',
        'notify_url',
        'api_key',
        'ranking_id'
    ];

    public function ranking()
    {
        return $this->belongsTo(Ranking::class, 'ranking_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }
}
