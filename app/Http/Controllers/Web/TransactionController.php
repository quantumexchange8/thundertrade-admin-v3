<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MerchantTransaction;
use App\Models\MerchantWallet;
use App\Models\Ranking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Ladumor\OneSignal\OneSignal;
use Spatie\Activitylog\Facades\LogBatch;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('Transaction');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
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
        $rec = MerchantTransaction::find($id);
        if ($rec->status == 0) {
            $wallet = MerchantWallet::find($rec->wallet_id);
            $merchant = $rec->merchant;
            LogBatch::startBatch();

            $devices = OneSignal::getDevices();

            $fields = [];
            foreach ($devices['players'] as $player) {
                $fields['include_player_ids'][] = $player['id'];
            }

            if ($request->status == 2) {
                if ($rec->transaction_type == "deposit") {

                    Log::debug($wallet);

                    $wallet->deposit_balance += $rec->amount;
                    $wallet->gross_deposit += $rec->amount;
                    $wallet->net_deposit += $rec->total;
                    $wallet->save();

                    $message = 'Approved $' . $rec->amount . ', ID - ' . $rec->transaction_no;

                    $total_deposit = MerchantWallet::where('merchant_id', $merchant->id)->sum('gross_deposit');
                    $ranking = Ranking::where('amount', '<=', $total_deposit)->orderBy('amount', 'desc')->first();
                    if ($ranking) {
                        if ($ranking->id != $merchant->ranking_id) {
                            $merchant->update(['ranking_id' => $ranking->id]);
                        }
                    }
                } else if ($rec->transaction_type == "withdrawal") {

                    if ($wallet->deposit_balance < $rec->amount) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Insufficient balance',
                        ]);
                    };

                    $wallet->deposit_balance -= $rec->amount;
                    $wallet->gross_withdrawal += $rec->amount;
                    $wallet->net_withdrawal += $rec->total;
                    $wallet->save();
                }
            } else if ($request->status == 1) {
                $message = 'Rejected $' . $rec->amount . ', ID - ' . $rec->transaction_no;
            }

            OneSignal::sendPush($fields, $message);

            $rec->update([
                'status' => $request->status,
                'approval_reason' => $request->approval_reason,
                'approval_by' => Auth::id(),
                'approval_date' => today(),
            ]);

            $hashedToken = md5($rec->transaction_no . $rec->address);
            $params = [
                "token" => $hashedToken,
                "transactionID" => $rec->transaction_no,
                "address" => $rec->address,
                "amount" => $rec->amount,
                "status" => $rec->status == 2 ? 'Success' : 'Rejected',
                "remarks" => $rec->approval_reason
            ];

            $url = 'https://metafinx-member.currenttech.pro/updateDeposit';
            $response = Http::post($url, $params);
            Log::debug($response);

            activity('activity-log')->causedBy(Auth::user())->log('approve-transaction');
            LogBatch::endBatch();
            if ($rec->channel == 'website') {
                Http::post($merchant->notify_url, ['transaction_no' => $rec->merchant_transaction_no, 'status' => $request->status]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Success',
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Already approved',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
