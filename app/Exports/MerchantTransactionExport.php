<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MerchantTransactionExport implements FromQuery, WithHeadings
{
    use Exportable;
    protected $query;
    public function __construct($query)
    {
        $this->query = $query;
    }

    public function query()
    {
        return $this->query;
    }
    public function headings(): array
    {
        return [
            'id',
            'user_id',
            'status',
            'transaction_type',
            'address',
            'currency',
            'amount',
            'charges',
            'total',
            'wallet_id', //
            'transaction_no',
            'TxID',
            'merchant_id', //
            'approval_reason', //
            'approval_date', //
            'approval_by', //
            'receipt', //
            'remarks',
            'created_at',
            'updated_at',
        ];
    }
}
