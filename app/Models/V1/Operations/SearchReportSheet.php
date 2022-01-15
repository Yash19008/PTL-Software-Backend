<?php

namespace App\Models\V1\Operations;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SearchReportSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize
{
    private $query = null;
    private $wise = null;

    public function  __construct($query, $wise)
    {
        $this->query = $query;
        $this->wise = $wise;
    }

    public function collection()
    {
        return collect($this->query);
    }

    public function map($row): array {
        if ($this->wise == 'Customer') {
            $fields = [
                $row->company_name,
                $row->size_in_inch,
                $row->gsm,
                $row->product_group,
                $row->count
            ];
        } else {
            $fields = [
                $row->size_in_inch,
                $row->gsm,
                $row->product_group,
                $row->count
            ];
        }
        return $fields;
    }

    public function headings(): array
    {
        if ($this->wise == 'Customer') {
            return [
                'Company Name',
                'Size In Inch',
                'GSM',
                'Product Group',
                'Count'
            ];
        } else {
            return [
                'Size In Inch',
                'GSM',
                'Product Group',
                'Count'
            ];
        }
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Report';
    }
}