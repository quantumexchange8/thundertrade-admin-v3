<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kirschbaum\PowerJoins\PowerJoins;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class MerchantWallet extends Model
{
    use HasFactory, PowerJoins, LogsActivity;

    protected $fillable = [
        'wallet_number',
        'deposit_balance',
        'gross_deposit',
        'gross_withdrawal',
        'wallet_address',
        'type',
        'merchant_id'
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll();
    }
}
