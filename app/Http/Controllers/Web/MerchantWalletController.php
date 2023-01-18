<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MerchantWallet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MerchantWalletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($merchant)
    {
        return Inertia::render('MerchantWallet');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($merchant)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $merchant)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($merchant, $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($merchant, $id)
    {
        $details = MerchantWallet::query()
            ->where('merchant_id', $merchant)
            ->with(['merchant:id,name'])
            ->find($id);
        return response()->json(['success' => true, 'data' => $details]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $merchant, $id)
    {
        $request->validate([
            'wallet_address' => 'required|string',
        ]);
        $rec = MerchantWallet::where('merchant_id', $merchant)->find($id);
        $rec->update([
            'wallet_address' => $request->wallet_address,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Update Wallet Success',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($merchant, $id)
    {
        //
    }
}
