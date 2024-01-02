<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;

use App\Models\V1\Operations\ProductGroup;
use Illuminate\Http\Request;

class ProductGroupController extends Controller
{

    public function store(Request $request)
    {
        $user_id = ProductGroup::create([
            "group_name" => $request->get('group_name'),
            "is_reel" => $request->get('is_reel')
        ])->id;
        return $this->success('ProductGroup Response Submitted Successully !!', null, 200);
    }

    public function update(Request $request, $id)
    {
        $user = ProductGroup::findOrFail($id);
        $data = [
            "group_name" => $request->get('group_name'),
            "is_reel" => $request->get('is_reel')
        ];
        $user->update($data);
        return $this->success('ProductGroup updated successfully', $user, 200);
    }

    public function show($id)
    {
        $user = ProductGroup::where('id', $id)->first();
        return $this->success('ProductGroup Responses !!', $user, 200);
    }

    public function all_list()
    {
        $query = ProductGroup::select("*")->get();
        return $this->success('ProductGroup Responses List', $query, 200);
    }

    public function query()
    {
        $query = ProductGroup::select("*");
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
        return $this->success('ProductGroup Responses List', $query, 200);
    }

    public function destroy(Request $request, $id)
    {
        ProductGroup::destroy($id);
        return $this->success('ProductGroup deleted successfully', null, 200);
    }

    public function getTableColumn()
    {
        return array("id" => "id", "group_name" => "group_name", "is_reel" => "is_reel", "updated_on" => "updated_on");
    }
}
