<?php

namespace App\Http\Controllers;

use App\Models\MerchantTransaction;
use App\Models\MerchantWallet;
use App\Models\User;
use App\Services\RunningNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $users = User::all(); // Retrieve all users

        foreach ($users as $user) {
            $dataToHash = md5($user->name . $user->email);

            if ($result['token'] === $dataToHash) {
                //proceed deposit
                $merchant = $user->merchant;
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
}
