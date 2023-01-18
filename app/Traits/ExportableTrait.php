<?php

namespace App\Traits;

use App\Exports\Export;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait ExportableTrait
{

    public function export($query, $type)
    {
        if ($type === 'csv') {
            return (new Export($query))->download('table.csv', \Maatwebsite\Excel\Excel::CSV);
        } else if ($type === 'xlsx') {
            return (new Export($query))->download('table.xlsx', \Maatwebsite\Excel\Excel::XLSX);
        } else if (str_contains($type, 'pdf')) {
            return (new Export($query))->download('table.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
        }
    }
}
