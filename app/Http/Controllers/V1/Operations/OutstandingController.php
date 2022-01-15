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
        FROM `outstanding` WHERE `customer_name` = '".$company_name."'");
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
        $query = $this->sort($query, $tablesColumns, \Request::get('sort'), 'email_id');
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
        
        echo json_encode($output, JSON_NUMERIC_CHECK);

    }

}
