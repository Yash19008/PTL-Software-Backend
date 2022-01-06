<?php

namespace App\Http\Requests\Authentication;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
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
            case 'login':
                return [
                    'email_id' => 'required|string',
                    "password" => "required|string"
                ];
         }
        return [];
    }
}
