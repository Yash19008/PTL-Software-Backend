<?php

namespace App\Http\Controllers\V1\Operations;

use App\Http\Controllers\Controller;

use App\Models\V1\Operations\UserLevel;

class UserLevelController extends Controller {

    public function show()
    {
        $query = UserLevel::all();
        return $this->success('User levels !!', $query, 200);
    }
}
