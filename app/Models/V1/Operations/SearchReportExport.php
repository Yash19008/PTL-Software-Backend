<?php

namespace App\Models\V1\Operations;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

use App\Models\V1\Operations\SearchReportSheet;

class SearchReportExport implements WithMultipleSheets
{
    use Exportable;
    private $query = null;
    private $wise = null;

    public function  __construct($query, $wise)
    {
        $this->query = $query;
        $this->wise = $wise;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new SearchReportSheet($this->query, $this->wise);

        return $sheets;
    }
}