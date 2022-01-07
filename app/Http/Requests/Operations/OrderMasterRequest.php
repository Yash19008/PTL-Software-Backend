<?php

namespace App\Http\Requests\Operations;

use Illuminate\Foundation\Http\FormRequest;

class CustomerMasterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //If you're using some another access control, you can return  true here. 
        //Returning false would block this request. sending 403 back as response
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $arr = explode('@', $this->route()->getActionName());
        $method = $arr[1];
        switch ($method) {
            case 'update':
            case 'store':
                return [
                ];
         }
        return [];
    }
}
