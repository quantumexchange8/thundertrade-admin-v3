<?php

namespace App\Http\Controllers\Api;

use App\Exports\MerchantTransactionExport;
use App\Http\Controllers\Controller;
use App\Mail\ForgotSecurityPin;
use App\Models\Merchant;
use App\Models\MerchantTransaction;
use App\Models\MerchantWallet;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Ranking;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use App\Rules\OtpVerify;
use App\Services\RunningNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Facades\LogBatch;
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
        LogBatch::startBatch();
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

        activity('activity-log')->causedBy(Auth::user())->log('edit-profile');
        LogBatch::endBatch();
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
        LogBatch::startBatch();
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

        activity('activity-log')->causedBy(Auth::user())->log('change-protocol');
        LogBatch::endBatch();
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
        LogBatch::startBatch();
        $merchant->security_pin = Hash::make($request->new_security_pin);
        $merchant->save();
        activity('activity-log')->causedBy(Auth::user())->log('change-6-digit-pin');
        LogBatch::endBatch();
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
        LogBatch::startBatch();
        $merchant->security_pin = Hash::make($request->security_pin);
        $merchant->save();
        activity('activity-log')->causedBy(Auth::user())->log('set-6-digit-pin');
        LogBatch::endBatch();
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
            'role' => ['required', 'string'],
            'password' => ['required', Password::min(8)->mixedCase()]
        ]);

        if (Role::where('name', $data['role'])->exists()) {
            throw ValidationException::withMessages(['role' => 'Role existed']);
        }
        $role = Role::create(['name' => $data['role']]);

        $user = Auth::user();
        $merchant = $user->merchant;
        LogBatch::startBatch();
        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role_id' => $role->id,
            'merchant_id' => $merchant->id,
        ]);

        activity('activity-log')->causedBy(Auth::user())->log('create-sub-admin');
        LogBatch::endBatch();
        return response()->json(['success' => true, 'message' => 'Create Sub Admin Success']);
    }

    public function userDestroy(Request $request, $id)
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
        LogBatch::startBatch();
        $rec = User::where('merchant_id', $merchant->id)->where('id', $id)->first();

        $rec->delete();
        activity('activity-log')->causedBy(Auth::user())->log('delete-sub-admin');
        LogBatch::endBatch();
        return response()->json(['success' => true, 'message' => 'Delete Sub Admin Success']);
    }

    public function activityLogIndex()
    {
        $user = Auth::user();
        $merchant = $user->merchant;
        $users = User::where('merchant_id', $merchant->id)->pluck('id');
        $logs = Activity::inLog(['activity-log'])->where('causer_type', 'App\\Models\\User')->whereIn('causer_id', $users)->latest()->with(['causer', 'subject'])->get();
        return response()->json(['success' => true, 'data' => $logs]);
    }
    public function activityLogShow(Request $request, $id)
    {
        $data = Activity::forBatch($id)->inLog(['default'])->with(['causer', 'subject'])->get();
        return response()->json(['success' => true, 'data' => $data]);
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
        LogBatch::startBatch();
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
        activity('activity-log')->causedBy(Auth::user())->log('edit-user-roles');
        LogBatch::endBatch();
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
        $charges = $data['amount'] * ($merchant->ranking->deposit / 100);
        $total = $data['amount'] - $charges;
        $transaction_no = RunningNumberService::getID('transaction_number');

        $path = $request->file('receipt')->store('uploads/transactions');
        LogBatch::startBatch();
        MerchantTransaction::create([
            'transaction_no' => $transaction_no,
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'address' => $data['address'],
            'currency' => $data['currency'],
            'amount' => $data['amount'],
            'charges' => $charges,
            'total' => $total,
            'TxID' => $data['TxID'],
            'receipt' => $path,
            'transaction_type' => 'deposit',
            'wallet_id' => $wallet->id,
            'channel' => 'merchant'
        ]);
        activity('activity-log')->causedBy(Auth::user())->log('top-up');
        LogBatch::endBatch();
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
            'address.*' => ['required'],
            'amount.*' => ['required', 'numeric'],
            'currency' => ['required', 'in:TRC20,ERC20,BTC'],
            'security_pin' => ['required'],
            'tac' => ['required', new OtpVerify($user->email, 'withdrawal')],
        ]);
        if (!Hash::check($request->security_pin, $merchant->security_pin)) {
            return response()->json(['success' => false, 'message' => 'Security pin does not match']);
        }

        $wallet = MerchantWallet::where('merchant_id', $merchant->id)->where('type', $data['currency'])->first();

        $transaction_no = RunningNumberService::getID('transaction_number');
        LogBatch::startBatch();
        $totalAmount = 0;
        for ($i = 0; $i < count($data['address']); $i++) {
            $totalAmount += $data['amount'][$i];
            $charges = $data['amount'][$i] * ($merchant->ranking->withdrawal / 100);
            $total = $data['amount'][$i] - $charges;
            MerchantTransaction::create([
                'transaction_no' => $transaction_no,
                'user_id' => $user->id,
                'merchant_id' => $merchant->id,
                'address' => $data['address'][$i],
                'currency' => $data['currency'],
                'amount' => $data['amount'][$i],
                'charges' => $charges,
                'total' => $total,
                'transaction_type' => 'withdrawal',
                'wallet_id' => $wallet->id,
                'channel' => 'merchant'
            ]);
        }
        activity('activity-log')->causedBy(Auth::user())->withProperties(['total_amount' => $totalAmount . " " . $data['currency']])->log('withdrawal');
        LogBatch::endBatch();
        return response()->json(['success' => true, 'message' => 'Withdrawal Success']);
    }

    public function transactionIndex(Request $request)
    {
        $user = Auth::user();
        $merchant = $user->merchant;
        $transactions = MerchantTransaction::query()
            ->where('merchant_id', $merchant->id)
            ->when($request->status, function ($query, $search) {
                $query->where('status', $search);
            })
            ->when($request->transaction_type, function ($query, $search) {
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
            return (new MerchantTransactionExport($transactions))->download('transaction.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
        }
        $transactions = $transactions->get();
        return response()->json(['success' => true, 'data' => $transactions]);
    }

    public function transactionShow(Request $request, $transaction)
    {
        $user = Auth::user();
        $merchant = $user->merchant;
        $transaction = MerchantTransaction::query()
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

    public function forgotSecurityPin()
    {
        if (Gate::denies('check-permission', 'allow-change-6-digit-pin')) {
            return response()->json(['success' => false, 'message' => 'Not enough Permission'], 403);
        }
        $user = Auth::user();
        $merchant = $user->merchant;
        $security_pin = str_pad(
            string: strval(random_int(
                min: 000_000,
                max: 999_999,
            )),
            length: 6,
            pad_string: '0',
            pad_type: STR_PAD_LEFT,
        );

        Mail::to($user)->send(new ForgotSecurityPin($security_pin));
        LogBatch::startBatch();
        $merchant->security_pin = Hash::make($security_pin);
        $merchant->save();
        activity('activity-log')->causedBy(Auth::user())->log('reset-security-pin');
        LogBatch::endBatch();
        return response()->json(['success' => true, 'message' => 'Email Sent']);
    }

    public function activityLogStore(Request $request)
    {
        $request->validate(['action' => 'required']);
        activity('activity-log')->causedBy(Auth::user())->log($request->action);
        return response()->json(['success' => true, 'message' => 'Log Success']);
    }
}
