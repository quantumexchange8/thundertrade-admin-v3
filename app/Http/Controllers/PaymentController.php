<?php

namespace App\Http\Controllers;

use App\Models\MerchantTransaction;
use App\Models\MerchantWallet;
use App\Models\Ranking;
use App\Models\User;
use App\Services\RunningNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Ladumor\OneSignal\OneSignal;
use Spatie\Activitylog\Facades\LogBatch;

class PaymentController extends Controller
{
    public function deposit(Request $request)
    {
        $data = $request->all();

        $result = [
            "token" => $data['token'],
            "transactionID" => $data['transactionID'],
            "address" => $data["address"],
            "currency" => $data["currency"],
            "amount" => $data["amount"],
            "TxID" => $data['TxID'],
        ];

        Log::debug($result);

        $users = User::with('merchant')->get(); // Retrieve all users

        foreach ($users as $user) {
            $merchant = $user->merchant;
            Log::debug($merchant);

            if ($merchant && $result['token'] === md5($user->email . $merchant->api_key)) {
                Log::debug($result['token']);
                //proceed deposit
                $wallet = MerchantWallet::where('merchant_id', $merchant->id)->where('type', $result['currency'])->first();
                $charges = $result['amount'] * ($merchant->ranking->deposit / 100);
                $total = $result['amount'] - $charges;

                LogBatch::startBatch();
                MerchantTransaction::create([
                    'transaction_no' => $result['transactionID'],
                    'user_id' => $user->id,
                    'merchant_id' => $merchant->id,
                    'address' => $result['address'],
                    'currency' => $result['currency'],
                    'amount' => $result['amount'],
                    'charges' => $charges,
                    'total' => $total,
                    'TxID' => $result['TxID'],
                    'transaction_type' => 'deposit',
                    'wallet_id' => $wallet->id,
                    'channel' => 'merchant'
                ]);
                activity('activity-log')->causedBy($user)->log('top-up');
                LogBatch::endBatch();
            }
        }

        return response()->json(['success' => true, 'message' => 'Deposit Success']);
    }
    public function withdrawal(Request $request)
    {
        $data = $request->all();

        $result = [
            "token" => $data['token'],
            "transactionID" => $data['transactionID'],
            "address" => $data["address"],
            "currency" => $data["currency"],
            "amount" => $data["amount"],
            "payment_charges" => $data["payment_charges"],
        ];

        $users = User::with('merchant')->get(); // Retrieve all users

        foreach ($users as $user) {
            $merchant = $user->merchant;

            if ($merchant && $result['token'] === md5($user->email . $merchant->api_key)) {
                //proceed withdrawal
                $wallet = MerchantWallet::where('merchant_id', $merchant->id)->where('type', $result['currency'])->first();

                LogBatch::startBatch();
                $totalAmount = 0;
                $totalAmount += $result['amount'];
                $charges = $result['payment_charges'];
                $total = $result['amount'] - $charges;
                MerchantTransaction::create([
                    'transaction_no' => $result['transactionID'],
                    'user_id' => $user->id,
                    'merchant_id' => $merchant->id,
                    'address' => $result['address'],
                    'currency' => $result['currency'],
                    'amount' => $result['amount'],
                    'charges' => $charges,
                    'total' => $total,
                    'transaction_type' => 'withdrawal',
                    'wallet_id' => $wallet->id,
                    'channel' => 'merchant'
                ]);
                activity('activity-log')->causedBy(Auth::user())->withProperties(['total_amount' => $totalAmount . " " . $data['currency']])->log('withdrawal');
                LogBatch::endBatch();
            }
        }
        return response()->json(['success' => true, 'message' => 'Withdrawal Success']);
    }

    public function updateTransaction(Request $request)
    {
        $data = $request->all();

        Log::debug($data);
        $result = [
            "token" => $data['token'],
            "transactionID" => $data['transactionID'],
            "address" => $data["address"],
            "amount" => $data["amount"],
            "status" => $data["status"],
            "remarks" => $data["remarks"],
            "email" => $data["email"],
        ];

        $merchant_transaction = MerchantTransaction::query()
            ->where('transaction_no', $result['transactionID'])
            ->whereHas('user', function ($query) use ($result) {
                $query->where('email', $result['email']);
            })
            ->first();

        $dataToHash = md5($merchant_transaction->transaction_no . $merchant_transaction->address);

        if ($result['token'] === $dataToHash) {
            //proceed approval
            $merchant_transaction->update([
                'status' => $result['status'],
                'approval_reason' => $result['remarks'],
                'approval_by' => 'MetaFinX Admin',
                'approval_date' => today(),
            ]);

            $devices = OneSignal::getDevices();

            $fields = [];
            foreach ($devices['players'] as $player) {
                $fields['include_player_ids'][] = $player['id'];
            }
            Log::debug($fields);

            $message = 'Successfully approved $' . $result['amount'] . ', transaction number - ' . $result['transactionID'];
            OneSignal::sendPush($fields, $message);

            $merchant = $merchant_transaction->merchant;
            $wallet = MerchantWallet::find(10);
            if ($merchant_transaction->status == 2) {
                if ($merchant_transaction->type == 'deposit') {
                    $wallet->deposit_balance += $merchant_transaction->amount;
                    $wallet->gross_deposit += $merchant_transaction->amount;
                    $wallet->net_deposit += $merchant_transaction->total;
                    $wallet->save();

                    $total_deposit = MerchantWallet::where('merchant_id', $merchant->id)->sum('gross_deposit');
                    $ranking = Ranking::where('amount', '<=', $total_deposit)->orderBy('amount', 'desc')->first();
                    if ($ranking) {
                        if ($ranking->id != $merchant->ranking_id) {
                            $merchant->update(['ranking_id' => $ranking->id]);
                        }
                    }
                } else if ($merchant_transaction->type == 'withdrawal') {
                    $wallet->deposit_balance -= $merchant_transaction->amount;
                    $wallet->gross_withdrawal += $merchant_transaction->amount;
                    $wallet->net_withdrawal += $merchant_transaction->total;
                    $wallet->save();
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Approval Success']);
    }
}
