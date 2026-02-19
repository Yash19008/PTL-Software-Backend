<?php

namespace App\Models\V1\Operations\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

use Maatwebsite\Excel\Validators\Failure;
use Carbon\Carbon;

use App\Models\V1\Operations\StockVendor;

class VendorStocksImport implements ToModel, WithValidation, WithStartRow, SkipsEmptyRows
{
    use SkipsErrors, SkipsFailures;
    private $vendorId = null;
    private $onlyCalcRowCount = false;
    private $rows = 0;
    private $failedArray = [];
    public function  __construct($vendorId, $onlyCalcRowCount)
    {
        $this->vendorId = $vendorId;
        $this->onlyCalcRowCount = $onlyCalcRowCount;
    }
    public function startRow(): int
    {
        return 2;
    }
    /**
     * @param array $row
     *
     * @return StockVendor|null
     */
    public function model(array $row)
    {
        if ($this->onlyCalcRowCount) {
            ++$this->rows;
            return;
        }
        
        $clientRowData = [
            'product_group'     => $row[0],
            'gsm'     => $row[1],
            'size_inch_length'     => $row[2],
            'size_inch_width'     => $row[3],
            'size_cms_length'     => $row[4],
            'size_cms_width'     => $row[5],
            'pkt_grs_weight'     => $row[6],
            'sheet'     => $row[7],
            'bdls'     => $row[8],
            'pkg_mode' => $row[9],
            'pkt_grs' => $row[10],
            'weight'     => $row[11],
            'quality'     => $row[12],
            'gwd'     => $row[13],
            'loc'     => $row[14],
            'vendor_id'     => $this->vendorId,
            'temp_flag'     => 1
        ];
        $clientId = StockVendor::create($clientRowData)->id;
    }

    public function rules(): array
    {
        return [
            '0' => 'required',
            '1' => 'nullable|numeric',
            '2' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
            '3' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
            '4' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
            '5' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
            '6' => 'nullable|regex:/^\d+(\.\d{1,2})?$/',
            '7' => 'nullable|numeric',
            '8' => 'nullable|numeric',
            '9' => 'nullable|numeric',
            '10' => 'nullable|numeric',
            '11' => 'nullable|regex:/^\d+(\.\d{1,2})?$/'
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            '0.required' => "Product Group is Mandatory",
            '1.numeric' => "GSM should be numeric",
            '2.regex' => "Length in Inch should be numeric/decimal",
            '3.regex' => "Width in Inch should be numeric/decimal",
            '4.regex' => "Length in CMS should be numeric/decimal",
            '5.regex' => "Width in CMS should be numeric/decimal",
            '6.regex' => "Package Gross Weight should be numeric/decimal",
            '7.numeric' => "Sheet should be numeric",
            '8.numeric' => "Bundles should be numeric",
            '9.numeric' => "Package Mode should be numeric",
            '10.numeric' => "Pkt Gross should be numeric",
            '11.regex' => "Weight should be numeric/decimal",
        ];
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }
    
    // public function onFailure(Failure ...$failures)
    // {
    //     $failures= json_decode( json_encode($failures), true);
    //     $this->failedArray[] = $failures;
    // }

    // public function getFailedArray()
    // {
    //     return $this->failedArray;
    // }
}
	
