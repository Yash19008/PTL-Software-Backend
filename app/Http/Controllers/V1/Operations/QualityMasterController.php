<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use App\Models\V1\Operations\CustomerQualityLink;
use App\Models\V1\Operations\QualityMaster;
use App\Models\V1\Operations\CustomerMaster;
use Illuminate\Http\Request;

class QualityMasterController extends Controller
{

    public function store(Request $request)
    {
        $qualityId = QualityMaster::create([
            "name" => $request->get('name')
        ])->id;
        
        $customerIds = CustomerMaster::pluck('id')->toArray();
        foreach ($customerIds as $customerId) {
            CustomerQualityLink::create([
                "customer_id" => $customerId,
                "quality_id" => $qualityId
            ]);
        }
        return $this->success('QualityMaster Response Submitted Successully !!', null, 200);
    }

    public function update(Request $request, $id)
    {
        $user = QualityMaster::findOrFail($id);
        $data = [
            "name" => $request->get('name')
        ];
        $user->update($data);
        return $this->success('QualityMaster updated successfully', $user, 200);
    }

    public function show($id)
    {
        $user = QualityMaster::where('id', $id)->first();
        return $this->success('QualityMaster Responses !!', $user, 200);
    }

    public function query()
    {
        $query = QualityMaster::select("*");
        return $query;
    }

    public function index()
    {
        $limit = \Config::get('global.PAGINATE.limit');
        $query = $this->query();
        $tablesColumns = $this->getTableColumn();
        $query = $this->search($query, $tablesColumns, \Request::get('search'));
        $query = $this->sort($query, $tablesColumns, \Request::get('sort'), 'id');
        $query = $query->paginate($limit);
        return $this->success('QualityMaster Responses List', $query, 200);
    }

    public function destroy(Request $request, $id)
    {
        CustomerQualityLink::where('quality_id', $id)->delete();
        QualityMaster::destroy($id);
        return $this->success('QualityMaster deleted successfully', null, 200);
    }

    public function getTableColumn()
    {
        return array("id" => "id", "name" => "name", "updated_at" => "updated_at");
    }
}
