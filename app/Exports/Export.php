<?php

namespace App\Exports;

use App\Traits\SearchableTrait;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithDefaultStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Str;

class Export implements FromCollection, ShouldAutoSize, WithStyles, WithHeadings, WithDefaultStyles, WithMapping
{
    use Exportable, SearchableTrait;

    protected $headingsName, $headingsLabel, $request, $query, $relations;
    /**
     * @return \Illuminate\Support\Collection
     */
    public function __construct($query)
    {
        $this->request = request();

        $headings = collect($this->request->headings);

        $this->headingsName = $headings->pluck('name')->toArray();
        $this->headingsLabel = $headings->pluck('label')->toArray();

        $this->query = $query;
    }

    public function collection()
    {

        return $this->query->get();
    }

    public function map($row): array
    {
        $arr = array();
        foreach ($this->headingsName as $heading) {
            $head = strtolower($heading);
            $parts = explode('.', $head);
            $column = array_pop($parts);

            $val = array_reduce($parts, function ($val, $part) {
                return $val[Str::camel($part)];
            }, $row);
            $val = $val[$column];
            $arr[] = $val;
        }


        return $arr;
    }

    public function headings(): array
    {
        return $this->headingsLabel;
    }

    public function styles(Worksheet $sheet)
    {
        //bold column header
        return [
            '1' => ['font' => ['bold' => true]],
        ];
    }

    public function defaultStyles(Style $defaultStyle)
    {
        return [
            'alignment' => [
                'wrapText' => true,
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT
            ]
        ];
    }
}
