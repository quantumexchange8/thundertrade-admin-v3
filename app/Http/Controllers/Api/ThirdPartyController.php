<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\MerchantTransaction;
use App\Models\MerchantWallet;
use App\Services\RunningNumberService;
use Illuminate\Http\Request;

class ThirdPartyController extends Controller
{
    public function deposit(Request $request)
    {
        $data = $request->validate([
            'merchantId' => ['required', 'exists:merchants,id'],
            'merchantOrderNo' => ['required'],
            'address' => ['required'],
            'currency' => ['required'],
            'TxID' => ['required'],
            'amount' => ['required'],
            'receipt' => ['required', 'image'],
            'sign' => ['required'],
        ]);

        $merchant = Merchant::find($data['merchantId']);
        $result = [
            'merchantId' => $data['merchantId'],
            'merchantOrderNo' => $this->decrypt($data['merchantOrderNo'], $merchant->api_key),
            'address' =>  $this->decrypt($data['address'], $merchant->api_key),
            'currency' =>  $this->decrypt($data['currency'], $merchant->api_key),
            'TxID' =>  $this->decrypt($data['TxID'], $merchant->api_key),
            'amount' =>  $this->decrypt($data['amount'], $merchant->api_key),
            'receipt' => $data['receipt'],
            'sign' =>  $data['sign'],

        ];

        $signature =   $this->sign($request->only('merchantId', 'merchantOrderNo', 'address', 'currency', 'TxID', 'amount'));

        if ($result['sign'] != $signature) {
            return response()->json(['message' => "Invalid signature"]);
        }
        $wallet = MerchantWallet::where('merchant_id', $result['merchantId'])->where('type', $result['currency'])->first();


        $transaction_no = RunningNumberService::getID('transaction_number');
        //https://stackoverflow.com/a/63033291
        //https://stackoverflow.com/a/70232052
        $receipt = $result['receipt'];
        $path = $receipt->store('uploads/transaction/' . $data['merchantId']);
        /*  $receipt_name = pathinfo($receipt->getClientOriginalName(), PATHINFO_FILENAME) . time() . '.' . $receipt->getClientOriginalExtension();
        $receipt->move("uploads/transaction/" . $merchant->id, $receipt_name); */


        MerchantTransaction::create([
            'transaction_no' => $transaction_no,
            'merchant_transaction_no' => $result['merchantOrderNo'],
            'merchant_id' => $result['merchantId'],
            'address' => $result['address'],
            'currency' => $result['currency'],
            'amount' => $result['amount'],
            'TxID' => $result['TxID'],
            'receipt' => $path,
            'transaction_type' => 'deposit',
            'wallet_id' => $wallet->id,
        ]);

        return response()->json(['message' => 'Success']);
    }





    private function sign($params)
    {
        ksort($params);
        $string = "";
        foreach ($params as $value) {
            $string .= $value;
        }

        $sha1Encrypt = sha1($string);
        $encryptedstring = md5($sha1Encrypt);
        return $encryptedstring;
    }



    private function encrypt($data, $secret)
    {
        //Take first 8 bytes of $key and append them to the end of $key.
        $subkey = substr($secret, 0, 8);
        // Encrypt data
        $encData = openssl_encrypt(
            $data,
            "DES-EDE3-CBC",
            $secret,
            OPENSSL_RAW_DATA,
            $subkey
        );
        return bin2hex($encData);
    }

    private function decrypt($data, $secret)
    {
        //Take first 8 bytes of $key and append them to the end of $key.
        $subkey = substr($secret, 0, 8);
        // Encrypt data
        $data = hex2bin($data);
        $encData = openssl_decrypt(
            $data,
            "DES-EDE3-CBC",
            $secret,
            OPENSSL_RAW_DATA,
            $subkey
        );
        // return bin2hex($encData);
        return $encData;
    }
}
