<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\V1\Operations\OutstandingView;
use App\Models\V1\Operations\Outstanding;
use DB;

class OutstandingController extends Controller {

    public function show($id) {
        $outstanding = Outstanding::where('id', $id)->first();
        $data=array(); 
        $array=array("company_name" => $outstanding->customer_name);
        $array1=array("oustanding" => $this->outstanding_details($outstanding->customer_name));
        $array2=array("summary" => $this->summary($outstanding->customer_name));
        $array3=array("total" => $this->total($outstanding->customer_name));
        $data[]=array_merge($array,$array1, $array2, $array3);

        return $this->success('Outstanding Responses !!', $data, 200);
    }

    public function outstanding_details($company_name) {
        $result=DB::select("
            SELECT date_format(date(date),'%d-%m-%Y') AS Date_chall,voucher_type,voucher_no,credit_days,
            date_format(date(DATE_ADD(date, INTERVAL credit_days DAY)),'%d-%m-%Y') AS due_date,
            DATEDIFF(date_format(date(DATE_ADD(date, INTERVAL credit_days DAY)),'%Y-%m-%d'),CURDATE()) AS days,
            over_dues, balance,
            IF(CURDATE() > date_format(date(DATE_ADD(date, INTERVAL credit_days DAY)),'%Y-%m-%d'),'YES','NO') as red 
            FROM `outstanding` WHERE `customer_name` = '".$company_name."'  ORDER BY date asc");
        if(count($result) > 0) {
            return $result;
        } else {
            return "no_record";
        }      
    }

    public function summary($company_name) {   
        
        
        $data_record=DB::select("SELECT DATE_FORMAT(date, '%b-%Y') AS Month, SUM(balance) AS Amount,
        IF(date_format(date(DATE_ADD(date, INTERVAL credit_days DAY)),'%Y-%m-%d'),'YES','NO') as red FROM outstanding WHERE `customer_name`= '$company_name' GROUP BY DATE_FORMAT(date, '%m-%Y') order by cast(YEAR(date) as unsigned) ASC,cast(MONTH(date) as unsigned) ASC");
        $allrecords=$this->outstanding_details($company_name); 
        $total_due=0;
        $total_not_due=0; 
        $i=1;  
    
        foreach($data_record as $values) {
              $due=0;
               $notdue=0;
               $total_summ=0;
              
            foreach($allrecords as $val) {  
              if($values->Month == date("M-Y",strtotime($val->Date_chall))) {
                    if($val->days < 0)
                    {
                        $due=$due+$val->balance;
                       
                    }
                    
                    if($val->days > 0)
                    {
                         $notdue=$notdue+$val->balance;
                          
                    }
                    
                    $total_summ=$due+$notdue;
              }      
            }
             $total_due=$total_due+$due;
              $total_not_due=$total_not_due+$notdue;
              
            $data[]=array(
                            "Month"=>$values->Month,
                            "Due"=>$due,
                            "Notdue"=>$notdue,
                            "Total_summ"=>$total_summ,
                            

                         );

            if(count($data_record) == $i)
            {
                 
                $data[]=array(
                            "Month"=>"Total",
                            "Due"=>$total_due,
                            "Notdue"=>$total_not_due,
                            "Total_summ"=>$this->total($company_name)
                         );
                
            }
            $i=$i+1;            
        }
      
        if(count($data_record) > 0) {      
            return $data;
        } else {
            return "no_record"; 
        }
    }

    public function total($company_name) {
        $data_record=DB::select("SELECT sum(balance) as balance_Amt FROM outstanding WHERE `customer_name` =  '$company_name'");
        if(count($data_record) > 0) {
            return $data_record[0]->balance_Amt;
        } else {
           return "no_record";
        }
    }

    public function query()
    {
        $query = OutstandingView::select("*");
        return $query;
    }

    public function index()
    {
        $limit = \Config::get('global.PAGINATE.limit');
        $query = $this->query();
        $tablesColumns = $this->getTableColumn();
        $query = $this->search($query, $tablesColumns, \Request::get('search'));
        $query = $this->sort($query, $tablesColumns, \Request::get('sort'), 'date');
        $query = $query->paginate($limit);
        return $this->success('OutstandingView Responses List', $query, 200);
    }

    public function getTableColumn()
    {         
        return array( "date" => "date", "customer_name" => "customer_name" , "mobile" => "mobile", "voucher_type" => "voucher_type", "total_amount" => "total_amount", "part_paid" => "part_paid", "balance" => "balance");
    }
    
    public function company(Request $request)
    {
        $mobile_no = $request->get('mobile');
        if($mobile_no)
        {
            // find how many company is register on this number 
            $data=array();
            
            $data_record=DB::select("SELECT customer_name FROM outstanding WHERE `mobile` = '".$mobile_no."' GROUP BY customer_name");
          
            foreach($data_record as $value)
            {
            
                $array=array("company_name" => $value->customer_name);
                $array1=array("oustanding" => $this->outstanding_details($value->customer_name));
                $array2=array("summary" => $this->summary($value->customer_name));
                $array3=array("total" => $this->total($value->customer_name));
                
                $data[]=array_merge($array,$array1,$array2,$array3);
            }
            
            $output['data'] = $data;
            $output['message'] = 'Outstanding List !!';
            $output['status'] = 'success';
        }
        else
        {
            $output['message'] = 'No result found !!';
            $output['status'] = 'error';
        }
        
        return response()->json($output, 200);

    }
    public function import_outstanding_outside(Request $request){
       
        try {
            //$outstandingArr = json_decode($request->outstandingObj, true);
            if(!empty($request->all())){
                foreach ($request->all() as $key => $value) {
                    $outstanding = Outstanding::where('voucher_no',$value['voucher_no'])->get();
                        if(count($outstanding) == 0){
                            $insert_outstanding_array=array(
                                "date" => date('Y-m-d', strtotime($value['date'])),
                                "customer_name"=> $value['customer_name'],
                                "person_name"=>NULL,
                                "mobile"=>$value['mobile'],
                                "email"=>$value['email'],
                                "voucher_type"=>$value['voucher_type'],
                                "voucher_no"=>$value['voucher_no'],
                                "credit_days"=> $value['credit_days'],
                                "due_date"=>NULL,
                                "over_dues"=>NULL,
                                "total_amount"=>$value['total_amount'],
                                "part_paid"=>$value['part_paid'],
                                "balance"=>$value['balance'],
                                "update_on"=>Carbon::now(),
                            );
                            Outstanding::create($insert_outstanding_array);
                        }else{
                           
                            foreach ($outstanding as $key => $val) {
                                $update_outstanding_array=array(
                                    "date" =>date('Y-m-d', strtotime($value['date'])),
                                    "customer_name"=> $value['customer_name'],
                                    "mobile"=>$value['mobile'],
                                    "email"=>$value['email'],
                                    "voucher_type"=>$value['voucher_type'],
                                    "voucher_no"=>$value['voucher_no'],
                                    "credit_days"=> $value['credit_days'],
                                    "over_dues"=>NULL,
                                    "total_amount"=>$value['total_amount'],
                                    "part_paid"=>$value['part_paid'],
                                    "balance"=>$value['balance'],
                                    "update_on"=>Carbon::now(),
                                );
                                Outstanding::where('voucher_no',$value['voucher_no'])->update($update_outstanding_array);
                            }
                    }
                   
                }
                return $this->success('Import outside outstanding successfully !!', $request->all(), 200);
            }else{
                return $this->failure('Empty data found or Data not proper !!', 500);
            }
        } catch (\Exception $e) {
            return $this->failure('Something Went Wrong !!', $e->getMessage(), 500);
        }
    }
    public function import_outstanding_outside_nested(Request $request){
        try {
            //$outstandingArr = json_decode($request->outstandingObj, true);
            if(!empty($request->all())){
                foreach ($request->all() as $key => $value) {
                    $outstanding = Outstanding::where('voucher_no',$value['voucher_no'])
                    ->where('date', date('Y-m-d', strtotime($value['date'])))->get();
                        if(count($outstanding) == 0){
                           
                            foreach ($value['items'] as $key => $val) {
                                $input = [
                                    "date" =>date('Y-m-d', strtotime($value['date'])),
                                    "customer_name"=> $value['customer_name'],
                                    "mobile"=>$value['mobile'],
                                    "email"=>$value['email'],
                                    "voucher_no"=>$value['voucher_no'],
                                    "voucher_type"=>$val['voucher_type'],
                                    "credit_days"=> $val['credit_days'],
                                    "due_date"=>NULL,
                                    "over_dues"=>NULL,
                                    "total_amount"=>$val['total_amount'],
                                    "part_paid"=>$val['part_paid'],
                                    "balance"=>$val['balance'],
                                    'updated_on'=>Carbon::now(),
                                ];
                                Outstanding::create($input);
                            }
                          
                        }else{
                           
                            Outstanding::where('voucher_no',$value['voucher_no'])->delete();
                            foreach ($value['items'] as $key => $val) {
                                $input = [
                                    "date" =>date('Y-m-d', strtotime($value['date'])),
                                    "customer_name"=> $value['customer_name'],
                                    "mobile"=>$value['mobile'],
                                    "email"=>$value['email'],
                                    "voucher_no"=>$value['voucher_no'],
                                    "voucher_type"=>$val['voucher_type'],
                                    "credit_days"=> $val['credit_days'],
                                    "over_dues"=>NULL,
                                    "due_date"=>NULL,
                                    "total_amount"=>$val['total_amount'],
                                    "part_paid"=>$val['part_paid'],
                                    "balance"=>$val['balance'],
                                    'updated_on'=>Carbon::now(),
                                ];
                                Outstanding::create($input);
                            }
                    }
                   
                }
                return $this->success('Import outside outstanding successfully !!', $request->all(), 200);
            }else{
                return $this->failure('Empty data found or Data not proper !!', 500);
            }
        } catch (\Exception $e) {
            return $this->failure('Something Went Wrong !!', $e->getMessage(), 500);
        }
    }
    public function deleteAllOutstanding(){
        Outstanding::truncate();
        return $this->success('Outstanding table truncated successfully !!', 200);
    }

}
