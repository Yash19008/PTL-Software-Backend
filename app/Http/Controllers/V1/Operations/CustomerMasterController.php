<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Requests\Operations\CustomerMasterRequest;
use App\Models\V1\Operations\CustomerMaster;
use App\Models\V1\Operations\CustomerProductLink;
use App\Models\V1\Operations\CustomerQualityLink;
use App\Models\V1\Operations\StockColumns;

class CustomerMasterController extends Controller
{

    public function store(CustomerMasterRequest $request)
    {
        $customer_id = CustomerMaster::create([
            "company_name" => $request->get('company_name'),
            "client_name" => $request->get('client_name'),
            "email" => $request->get('email'),
            "mobile" => $request->get('mobile'),
            "active" => $request->get('active'),
            "stock_active" => $request->get('stock_active'),
            "password" => md5($request->get('password')),
            "mobile_show_stocks_from" => $request->get('mobile_show_stocks_from'),
            "updated_dt" => Carbon::now()
        ])->id;

        StockColumns::create([
            "customer_id" => $customer_id,
            "quality" => $request->get('quality') == 'true' ? 'Yes' : 'No',
            "gsm" => $request->get('gsm') == 'true' ? 'Yes' : 'No',
            "size_inch" => $request->get('size_inch') == 'true' ? 'Yes' : 'No',
            "total_ups" => $request->get('total_ups') == 'true' ? 'Yes' : 'No',
            "utilization" => $request->get('utilization') == 'true' ? 'Yes' : 'No',
            "utilization" => $request->get('utilization') == 'true' ? 'Yes' : 'No',
            "bundle" => $request->get('bundle') == 'true' ? 'Yes' : 'No',
            "total_sheet" => $request->get('total_sheet') == 'true' ? 'Yes' : 'No',
            "gwd" => $request->get('gwd') == 'true' ? 'Yes' : 'No',
            'is_reel' => 'No'
        ]);

        StockColumns::create([
            'customer_id' => $customer_id,
            "quality" => $request->get('reel_quality') == 'true' ? 'Yes' : 'No',
            "gsm" => $request->get('reel_gsm') == 'true' ? 'Yes' : 'No',
            "size_inch" => $request->get('reel_size_inch') == 'true' ? 'Yes' : 'No',
            "total_ups" => $request->get('reel_total_ups') == 'true' ? 'Yes' : 'No',
            "sheet_weight" => $request->get('reel_sheet_weight') == 'true' ? 'Yes' : 'No',
            "utilization" => $request->get('reel_utilization') == 'true' ? 'Yes' : 'No',
            "bundle" => $request->get('reel_bundle') == 'true' ? 'Yes' : 'No',
            "total_sheet" => $request->get('reel_total_sheet') == 'true' ? 'Yes' : 'No',
            "gwd" => $request->get('reel_gwd') == 'true' ? 'Yes' : 'No',
            'is_reel' => 'Yes'
        ]);

        $data = [
            "stock_active" => $request->get('stock_active'),
            "password" => md5($request->get('password'))
        ];
        CustomerMaster::where('mobile', $request->get('mobile'))->update($data);

        $selectedProducts = $request->get('selectedProducts');
        if (isset($selectedProducts) && count($selectedProducts) > 0) {
            foreach ($selectedProducts  as $product) {
                CustomerProductLink::create([
                    'customer_id' => $customer_id,
                    "product_group_id" => $product,
                ]);
            }
        }

        $selectedQualities = $request->get('selectedQualities');
        if (isset($selectedQualities) && count($selectedQualities) > 0) {
            foreach ($selectedQualities  as $quality) {
                CustomerQualityLink::create([
                    'customer_id' => $customer_id,
                    "quality_id" => $quality,
                ]);
            }
        }

        return $this->success('CustomerMaster Response Submitted Successully !!', null, 200);
    }

    public function update(CustomerMasterRequest $request, $id)
    {
        $user = CustomerMaster::findOrFail($id);
        $data = [
            "company_name" => $request->get('company_name'),
            "client_name" => $request->get('client_name'),
            "email" => $request->get('email'),
            "mobile" => $request->get('mobile'),
            "active" => $request->get('active'),
            "stock_active" => $request->get('stock_active'),
            "mobile_show_stocks_from" => $request->get('mobile_show_stocks_from'),
            "updated_dt" => Carbon::now()
        ];

        if ($request->get('password')) {
            $data['password'] = md5($request->get('password'));
        }

        $user->update($data);

        $stockcolumns = StockColumns::where('customer_id', $id);
        $data = [
            "quality" => $request->get('quality') == 'true' ? 'Yes' : 'No',
            "gsm" => $request->get('gsm') == 'true' ? 'Yes' : 'No',
            "size_inch" => $request->get('size_inch') == 'true' ? 'Yes' : 'No',
            "total_ups" => $request->get('total_ups') == 'true' ? 'Yes' : 'No',
            "sheet_weight" => $request->get('sheet_weight') == 'true' ? 'Yes' : 'No',
            "utilization" => $request->get('utilization') == 'true' ? 'Yes' : 'No',
            "bundle" => $request->get('bundle') == 'true' ? 'Yes' : 'No',
            "total_sheet" => $request->get('total_sheet') == 'true' ? 'Yes' : 'No',
            "gwd" => $request->get('gwd') == 'true' ? 'Yes' : 'No',
            'is_reel' => 'No',
        ];
        $stockcolumns->update($data);

        StockColumns::updateOrInsert(
            ['customer_id' => $id, 'is_reel' => 'Yes'],
            [
                'customer_id' => $id,
                "quality" => $request->get('reel_quality') == 'true' ? 'Yes' : 'No',
                "gsm" => $request->get('reel_gsm') == 'true' ? 'Yes' : 'No',
                "size_inch" => $request->get('reel_size_inch') == 'true' ? 'Yes' : 'No',
                "total_ups" => $request->get('reel_total_ups') == 'true' ? 'Yes' : 'No',
                "sheet_weight" => $request->get('reel_sheet_weight') == 'true' ? 'Yes' : 'No',
                "utilization" => $request->get('reel_utilization') == 'true' ? 'Yes' : 'No',
                "bundle" => $request->get('reel_bundle') == 'true' ? 'Yes' : 'No',
                "total_sheet" => $request->get('reel_total_sheet') == 'true' ? 'Yes' : 'No',
                "gwd" => $request->get('reel_gwd') == 'true' ? 'Yes' : 'No',
                'is_reel' => 'Yes'
            ]
        );

        $data = [
            "stock_active" => $request->get('stock_active')
        ];
        if ($request->get('password')) {
            $data['password'] = md5($request->get('password'));
        }
        CustomerMaster::where('mobile', $request->get('mobile'))->update($data);

        
        CustomerProductLink::where('customer_id',$id)->delete();
        $selectedProducts = $request->get('selectedProducts');
        if (isset($selectedProducts) && count($selectedProducts) > 0) {
            foreach ($selectedProducts  as $product) {
                CustomerProductLink::create([
                    'customer_id' => $id,
                    "product_group_id" => $product,
                ]);
            }
        }

        CustomerQualityLink::where('customer_id',$id)->delete();
        $selectedQualities = $request->get('selectedQualities');
        if (isset($selectedQualities) && count($selectedQualities) > 0) {
            foreach ($selectedQualities  as $quality) {
                CustomerQualityLink::create([
                    'customer_id' => $id,
                    "quality_id" => $quality,
                ]);
            }
        }

        return $this->success('CustomerMaster updated successfully', $user, 200);
    }

    public function show($id)
    {
        $user = CustomerMaster::where('id', $id)->first();
        $user->stock_columns = StockColumns::where('customer_id', $id)->where('is_reel', 'No')->first();
        $user->stock_columns_reel = StockColumns::where('customer_id', $id)->where('is_reel', 'Yes')->first();
        $user->selectedProducts = CustomerProductLink::where('customer_id', $user->id)->pluck('product_group_id')->toArray();
        $user->selectedQualities = CustomerQualityLink::where('customer_id', $user->id)->pluck('quality_id')->toArray();
        return $this->success('CustomerMaster Responses !!', $user, 200);
    }

    public function query()
    {
        $query = CustomerMaster::select("*");
        return $query;
    }

    public function index()
    {
        $limit = \Config::get('global.PAGINATE.limit');
        $query = $this->query();
        $tablesColumns = $this->getTableColumn();
        $query = $this->search($query, $tablesColumns, \Request::get('search'));
        $query = $this->sort($query, $tablesColumns, \Request::get('sort'), 'company_name');
        $query = $query->paginate($limit);
        return $this->success('CustomerMaster Responses List', $query, 200);
    }

    public function getTableColumn()
    {
        return array("company_name" => "company_name", "client_name" => "client_name", "email" => "email", "mobile" => "mobile", "active" => "active", "stock_active" => "stock_active", "otp" => "otp");
    }
}
