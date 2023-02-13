<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Exports\Export;
use App\Models\Merchant;
use App\Models\MerchantWallet;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\RunningNumber;
use App\Models\RunningNumbers;
use App\Models\Wallet;
use App\Services\RunningNumberService;
use App\Traits\ExportableTrait;
use App\Traits\SearchableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Spatie\Activitylog\Facades\LogBatch;

class MerchantController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function index(Request $request)
    {
        return Inertia::render('Merchant');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required'],
            'notify_url' => ['required', 'url'],
        ]);
        LogBatch::startBatch();
        $merchant = Merchant::create([
            'name' => $request->name,
            'notify_url' => $request->notify_url,
            'api_key' => Str::uuid(),
            'ranking_id' => 1
        ]);


        $walletTypes = ['TRC20', 'ERC20', 'BTC'];

        foreach ($walletTypes as $type) {
            $walletNumber =  RunningNumberService::getId('wallet_number');

            MerchantWallet::create([
                'wallet_number' => $walletNumber,
                'type' => $type,
                'merchant_id' => $merchant->id,
            ]);
        }
        $role = Role::create(['name' => 'Admin', 'merchant_id' => $merchant->id]);
        $permissions = RolePermission::whereRelation('role', 'name', 'Merchant Admin Template')->pluck('permission_id');
        foreach ($permissions as $permission) {
            RolePermission::create(['role_id' => $role->id, 'permission_id' => $permission]);
        }
        activity('activity-log')->causedBy(Auth::user())->log('create-merchant');
        LogBatch::endBatch();

        return response()->json([
            'success' => true,
            'message' => 'Create Merchant Success',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = Merchant::find($id);
        return response()->json(['success' => true, 'data' => $data]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => ['required'],
            'notify_url' => ['required', 'url'],
        ]);

        LogBatch::startBatch();
        $merchant = Merchant::find($id);
        $merchant->update([
            'name' => $request->name,
            'notify_url' => $request->notify_url,
        ]);
        activity('activity-log')->causedBy(Auth::user())->log('edit-merchant');
        LogBatch::endBatch();
        return response()->json([
            'success' => true,
            'message' => 'Update Merchant Success',
        ]);

        //        try{
        //            $merchant->timestamps=false;
        //            $merchant->name=$validator->validated()['name'];
        //            $merchant->notify_url=$validator->validated()['notify_url'];
        //            $merchant->api_key=$validator->validated()['api_key'];
        //            $merchant->ranking_id=$validator->validated()['ranking_id'];
        //
        //            $old=$merchant->getOriginal();
        //
        //            $merchant->save();
        //            $new=$merchant->getChanges();
        //            $keys=array_keys($new);
        //            $old=array_filter($old,function($key) use ($keys) {
        //                return in_array($key,$keys);
        //            },ARRAY_FILTER_USE_KEY);
        //
        //
        //            activity('activity-log')->causedBy(Auth::user())->performedOn($merchant)->withProperties(['old'=>$old,'new'=>$new])->log('updated');
        //        }catch(\Exception $exception){
        //            return response()->json([
        //                'error'=>$exception->errorInfo,
        //            ],422);
        //        }

        //        $merchants=Merchant::where('ranking_id','1')->get();
        //        foreach($merchants as $merchant){
        //
        //            $old=$merchant->getOriginal();
        //            $merchant->update($validator->validated());
        //
        //            $new=$merchant->getChanges();
        //            $keys=array_keys($new);
        //            $old=array_filter($old,function($key) use ($keys) {
        //                return in_array($key,$keys);
        //            },ARRAY_FILTER_USE_KEY);
        //
        //            activity('activity-log')->causedBy(Auth::user())->performedOn($merchant)->withProperties(['old'=>$old,'new'=>$new])->log('updated');
        //
        //        }




    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        LogBatch::startBatch();
        $count = Merchant::destroy($id);
        activity('activity-log')->causedBy(Auth::user())->log('delete-merchant');
        LogBatch::endBatch();
        if ($count > 0) {
            return response()->json([
                'success' => true,
                "message" => "Delete Merchant Success",
            ]);
        } else {
            return response()->json([
                'success' => false,
                "message" => "Delete Merchant Error",
            ]);
        }
    }
}
