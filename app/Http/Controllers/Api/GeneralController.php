<?php

namespace App\Http\Controllers\Api;

use App\Exports\UserTransactionExport;
use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\MerchantWallet;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Ranking;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserTransaction;
use App\Rules\OtpVerify;
use App\Services\RunningNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Activitylog\Models\Activity;

class GeneralController extends Controller
{
    public function profileIndex(Request $request)
    {
        $profile = User::with(['merchant.ranking'])->find(Auth::id());

        return response()->json(['success' => true, 'data' => $profile]);
    }

    public function profileStore(Request $request)
    {
        if (Gate::denies('check-permission', 'allow-edit-profile')) {
            return response()->json(['success' => false, 'message' => 'Not enough Permission'], 403);
        }
        $user = Auth::user();
        $merchant = $user->merchant;
        $request->validate([
            'merchant_name' => ['required'],
            'email' => ['required', 'unique:users,email,' . $user->id],
            'phone' => ['required', 'unique:users,phone,' . $user->id],
        ]);
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();
        $merchant->name = $request->merchant_name;
        $merchant->save();
        return response()->json(['success' => true, 'message' => 'Update Profile Success']);
    }

    public function updateProfilePicture(Request $request)
    {
        $request->validate(['profile_picture' => ['required', 'image']]);
        $user = Auth::user();
        if ($user->profile_picture) {
            File::delete($user->profile_picture);
        }
        $profile_picture = $request->file('profile_picture')->store('uploads/profiles');
        $user->profile_picture = $profile_picture;
        $user->save();
        return response()->json(['success' => true, 'message' => 'Update Profile Picture Success']);
    }

    public function rankingIndex(Request $request)
    {
        $rankings = Ranking::all();
        return response()->json(['success' => true, 'data' => $rankings]);
    }

    public function walletIndex(Request $request)
    {
        $merchant = Auth::user()->merchant;
        $wallets = MerchantWallet::where('merchant_id', $merchant->id)->get();
        return response()->json(['success' => true, 'data' => $wallets]);
    }

    public function walletStore(Request $request)
    {
        if (Gate::denies('check-permission', 'allow-change-protocol')) {
            return response()->json(['success' => false, 'message' => 'Not enough Permission'], 403);
        }
        $request->validate([
            'erc20_address' => ['required'],
            'trc20_address' => ['required'],
            'btc_address' => ['required']
        ]);
        $merchant = Auth::user()->merchant;
        $ERC20Wallet = MerchantWallet::where('merchant_id', $merchant->id)->where('type', 'ERC20')->first();
        $ERC20Wallet->wallet_address = $request->erc20_address;
        $ERC20Wallet->save();
        $TRC20Wallet = MerchantWallet::where('merchant_id', $merchant->id)->where('type', 'TRC20')->first();
        $TRC20Wallet->wallet_address = $request->trc20_address;
        $TRC20Wallet->save();
        $BTCWallet = MerchantWallet::where('merchant_id', $merchant->id)->where('type', 'BTC')->first();
        $BTCWallet->wallet_address = $request->btc_address;
        $BTCWallet->save();
        return response()->json(['success' => true, 'message' => 'Update Wallet Address Success']);
    }



    public function changeSecurityPin(Request $request)
    {
        if (Gate::denies('check-permission', 'allow-change-6-digit-pin')) {
            return response()->json(['success' => false, 'message' => 'Not enough Permission'], 403);
        }
        $user = Auth::user();
        $merchant = $user->merchant;
        if (!Hash::check($request->current_security_pin, $merchant->security_pin)) {
            return response()->json(['message' => 'Current Security pin does not match']);
        }
        $request->validate([
            'new_security_pin' => ['required', 'numeric', 'digits:6', 'different:current_security_pin'],
            'tac' => ['required', new OtpVerify($user->email, 'change_security_pin')]
        ]);

        $merchant->security_pin = Hash::make($request->new_security_pin);
        $merchant->save();
        return response()->json(['success' => true, 'message' => 'Change Security pin Success']);
    }

    public function setSecurityPin(Request $request)
    {
        if (Gate::denies('check-permission', 'allow-change-6-digit-pin')) {
            return response()->json(['success' => false, 'message' => 'Not enough Permission'], 403);
        }
        $user = Auth::user();
        $merchant = $user->merchant;

        $request->validate([
            'security_pin' => ['required', 'numeric', 'digits:6'],
            'tac' => ['required', new OtpVerify($user->email, 'set_security_pin')]
        ]);

        $merchant->security_pin = Hash::make($request->security_pin);
        $merchant->save();
        return response()->json(['success' => true, 'message' => 'Set Security Pin Success']);
    }

    public function RoleIndex(Request $request)
    {
        $user = Auth::user();
        $merchant = $user->merchant;
        $roles = Role::where('merchant_id', $merchant->id)->get();
        return response()->json(['success' => true, 'data' => $roles]);
    }

    public function userIndex(Request $request)
    {
        $user = Auth::user();
        $merchant = $user->merchant;
        $users = User::where('merchant_id', $merchant->id)->with(['role'])->get();
        return response()->json(['success' => true, 'data' => $users]);
    }

    public function userStore(Request $request)
    {
        if (Gate::denies('check-permission', 'allow-create-sub-admin')) {
            return response()->json(['success' => false, 'message' => 'Not enough Permission'], 403);
        }

        $data =  $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'name' => ['required', 'string'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'role' => ['required', 'numeric'],
            'password' => ['required', Password::min(8)->mixedCase()]
        ]);

        $user = Auth::user();
        $merchant = $user->merchant;
        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role_id' => $data['role'],
            'merchant_id' => $merchant->id,
        ]);
        return response()->json(['success' => true, 'message' => 'Create Sub Admin Success']);
    }

    public function userDestroy(Request $request, $user)
    {
        if (Gate::denies('check-permission', 'allow-delete-sub-admin')) {
            return response()->json(['success' => false, 'message' => 'Not enough Permission'], 403);
        }
        $user = Auth::user();
        $merchant = $user->merchant;
        $request->validate([
            'security_pin' => ['required'],
            'tac' => ['required', new OtpVerify($user->email, 'delete_sub_admin')],
        ]);
        if (!Hash::check($request->security_pin, $merchant->security_pin)) {
            return response()->json(['message' => 'Security pin does not match']);
        }

        $rec = User::where('merchant_id', $merchant->id)->where('id', $user)->first();

        $rec->delete();

        return response()->json(['success' => true, 'message' => 'Delete Sub Admin Success']);
    }

    public function ActivityLogIndex()
    {
        $user = Auth::user();
        $merchant = $user->merchant;
        $users = User::where('merchant_id', $merchant->id)->pluck('id');
        $logs = Activity::where('causer_type', 'App\Models\User')->whereIn('causer_id', $users)->latest();
        return response()->json(['success' => true, 'data' => $logs]);
    }

    public function rolePermissionIndex(Request $request, $role)
    {
        $permissions = PermissionGroup::with(['permissions'])->get();
        $rolePermissions = Permission::whereRelation('rolePermission', 'role_id', $role)->pluck('code');
        return response()->json(['success' => true, 'data' => ['lists' => $permissions, 'permissions' => $rolePermissions]]);
    }

    public function rolePermissionStore(Request $request, $role)
    {
        if (Gate::denies('check-permission', 'allow-edit-user-roles')) {
            return response()->json(['success' => false, 'message' => 'Not enough Permission'], 403);
        }
        $request->validate(['permissions' => ['required', 'array']]);

        $perm =  $request->permissions;
        $permissions = Permission::whereIn('code', $perm)->pluck('id');
        foreach ($permissions as $row) {
            RolePermission::firstOrCreate([
                'role_id' => $role,
                'permission_id' => $row
            ]);
        }
        RolePermission::where('role_id', $role)->whereRelation('permission', function ($q) use ($permissions) {
            $q->whereNotIn('id', $permissions);
        })->delete();
        return response()->json(['success' => true, 'message' => 'Update Success']);
    }

    public function deposit(Request $request)
    {
        if (Gate::denies('check-permission', 'allow-top-up-fund')) {
            return response()->json(['success' => false, 'message' => 'Not enough Permission'], 403);
        }
        $data = $request->validate([
            'address' => ['required'],
            'currency' => ['required', 'in:TRC20,ERC20,BTC'],
            'amount' => ['required', 'numeric'],
            'TxID' => ['required'],
            'receipt' => ['required', 'file'],

        ]);
        $user = Auth::user();
        $merchant = $user->merchant;
        $wallet = MerchantWallet::where('merchant_id', $merchant->id)->where('type', $data['currency'])->first();

        $transaction_no = RunningNumberService::getID('transaction_number');

        $path = $request->file('receipt')->store('uploads/transactions');

        UserTransaction::create([
            'transaction_no' => $transaction_no,
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'address' => $data['address'],
            'currency' => $data['currency'],
            'amount' => $data['amount'],
            'TxID' => $data['TxID'],
            'receipt' => $path,
            'transaction_type' => 'deposit',
            'wallet_id' => $wallet->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Deposit Success']);
    }

    public function withdrawal(Request $request)
    {
        if (Gate::denies('check-permission', 'allow-withdrawal-fund')) {
            return response()->json(['success' => false, 'message' => 'Not enough Permission'], 403);
        }
        $user = Auth::user();
        $merchant = $user->merchant;

        $data = $request->validate([
            'address' => ['required'],
            'currency' => ['required', 'in:TRC20,ERC20,BTC'],
            'amount' => ['required', 'numeric'],
            'security_pin' => ['required'],
            'tac' => ['required', new OtpVerify($user->email, 'withdrawal')],
        ]);
        if (!Hash::check($request->security_pin, $merchant->security_pin)) {
            return response()->json(['success' => false, 'message' => 'Security pin does not match']);
        }

        $wallet = MerchantWallet::where('merchant_id', $merchant->id)->where('type', $data['currency'])->first();

        $transaction_no = RunningNumberService::getID('transaction_number');


        UserTransaction::create([
            'transaction_no' => $transaction_no,
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'address' => $data['address'],
            'currency' => $data['currency'],
            'amount' => $data['amount'],
            'transaction_type' => 'withdrawal',
            'wallet_id' => $wallet->id,
        ]);

        return response()->json(['success' => true, 'message' => 'Withdrawal Success']);
    }

    public function transactionIndex(Request $request)
    {
        $user = Auth::user();
        $merchant = $user->merchant;
        $transactions = UserTransaction::query()
            ->where('merchant_id', $merchant->id)
            ->when($request->status, function ($query, $search) {
                $query->where('status', $search);
            })
            ->when($request->status, function ($query, $search) {
                $query->where('transaction_type', $search);
            })
            ->when($request->start_date, function ($query, $search) {
                $query->whereDate('created_at', '>=', $search);
            })
            ->when($request->end_date, function ($query, $search) {
                $query->whereDate('created_at', '<=', $search);
            });
        // ->select(['id',  'user_id', 'status', 'transaction_type', 'address', 'currency', 'amount', 'charges', 'total', 'transaction_no', 'TxID', 'receipt', 'created_at']);


        if ($request->export) {
            return (new UserTransactionExport($transactions))->download('transaction.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
        }
        $transactions = $transactions->get();
        return response()->json(['success' => true, 'data' => $transactions]);
    }

    public function transactionShow(Request $request, $transaction)
    {
        $user = Auth::user();
        $merchant = $user->merchant;
        $transaction = UserTransaction::query()
            ->where('merchant_id', $merchant->id)
            ->where('id', $transaction)
            ->first();


        return response()->json(['success' => true, 'data' => $transaction]);
    }

    public function userSettingStore(Request $request)
    {
        $request->validate([
            'setting_notification' => ['required', 'boolean'],
            'setting_notification_preview' => ['required', 'boolean'],
            'setting_biometric' => ['required', 'boolean'],
        ]);

        $user = Auth::user();
        $user->setting_notification = $request->setting_notification;
        $user->setting_notification_preview = $request->setting_notification_preview;
        $user->setting_biometric = $request->setting_biometric;
        $user->save();

        return response()->json(['success' => true, 'message' => 'Update Setting Success']);
    }
}
