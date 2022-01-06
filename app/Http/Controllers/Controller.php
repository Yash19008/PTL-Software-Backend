<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function success($message, $data, $code = 200) {
        return response()->json(['status' => 'success', 'message'=> __($message), 'data'=> $data], $code);
    }

    public function failure($message, $data = null, $code = 200) {
        return response()->json(['status' => 'error', 'message'=> __($message), 'data'=> $data], $code);
    }

    public function search($query, $tableColumns = [], $search = [], $operation = 'LIKE') {
		if(!empty($search) && !empty($tableColumns))
		{
			foreach($search as $key=>$searchvalue)
			{
                if (strtoupper($operation) == 'LIKE') {
                    $query =  $query->where($tableColumns[$key], $operation, '%'.$searchvalue.'%');
                } else {
                    $query =  $query->where($tableColumns[$key], $operation, $searchvalue);
                }
			}
		}
        return $query;
    }

    public function sort($query, $tableColumns = [], $sort = [], $defaultSortCol = 'name')
    {
		if(!empty($sort) && !empty($tableColumns))
		{
			$query = $query->orderBy($tableColumns[key($sort)], $sort[key($sort)]);
		} else {
			$query = $query->orderBy($defaultSortCol, 'DESC');
        }
		return $query;       
    }
}
