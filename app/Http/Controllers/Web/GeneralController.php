<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function getRolesByMerchant(Request $request, $merchant = null)
    {
        $roles = Role::where('merchant_id', $merchant)->get();
        return response()->json(['success' => true, 'data' => $roles]);
    }
}
