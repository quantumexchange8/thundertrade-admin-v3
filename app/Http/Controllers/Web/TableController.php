<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\MerchantTransaction;
use App\Models\MerchantWallet;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Ranking;
use App\Models\Role;
use App\Models\User;
use App\Traits\ExportableTrait;
use App\Traits\SearchableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class TableController extends Controller
{
    use SearchableTrait, ExportableTrait;

    public function MerchantTable(Request $request)
    {
        $query = $this->search(Merchant::class, ['ranking:id,level']);

        if ($request->export) {
            return $this->export($query, $request->type);
        }
        return $query->paginate($request->rowsPerPage);
    }

    public function TransactionTable(Request $request)
    {
        $query = $this->search(MerchantTransaction::class, ['wallet:id,wallet_number', 'merchant:id,name', 'adminUser:id,name']);

        if ($request->export) {
            return $this->export($query, $request->type);
        }
        return $query->paginate($request->rowsPerPage);
    }

    public function RankingTable(Request $request)
    {
        $query = $this->search(Ranking::class);

        if ($request->export) {
            return $this->export($query, $request->type);
        }
        return $query->paginate($request->rowsPerPage);
    }

    public function RoleTable(Request $request)
    {
        $query = $this->search(Role::class, ['merchant:id,name']);

        if ($request->export) {
            return $this->export($query, $request->type);
        }
        return $query->paginate($request->rowsPerPage);
    }

    public function UserTable(Request $request)
    {
        $query = $this->search(User::class, ['role:id,name', 'merchant:id,name']);

        if ($request->export) {
            return $this->export($query, $request->type);
        }
        return $query->paginate($request->rowsPerPage);
    }

    public function PermissionGroupTable(Request $request)
    {
        $query = $this->search(PermissionGroup::class);

        if ($request->export) {
            return $this->export($query, $request->type);
        }
        return $query->paginate($request->rowsPerPage);
    }


    public function PermissionTable(Request $request)
    {
        $query = $this->search(Permission::class, ['permissionGroup:id,name']);

        if ($request->export) {
            return $this->export($query, $request->type);
        }
        return $query->paginate($request->rowsPerPage);
    }

    public function ActivityLogTable(Request $request)
    {
        $query = $this->search(Activity::class, ['causer', 'subject']);

        if ($request->export) {
            return $this->export($query, $request->type);
        }
        return $query->paginate($request->rowsPerPage);
    }

    public function MerchantWalletTable(Request $request, $merchant)
    {
        $query = $this->search(MerchantWallet::class, ['merchant:id,name'])
            ->where('merchant_id', $merchant);

        if ($request->export) {
            return $this->export($query, $request->type);
        }
        return $query->paginate($request->rowsPerPage);
    }

    public function MerchantUserTable(Request $request, $merchant)
    {
        $query = $this->search(User::class, ['role:id,name'])
            ->where('merchant_id', $merchant);

        if ($request->export) {
            return $this->export($query, $request->type);
        }
        return $query->paginate($request->rowsPerPage);
    }

    public function MerchantRoleTable(Request $request, $merchant)
    {
        $query = $this->search(Role::class)
            ->where('merchant_id', $merchant);

        if ($request->export) {
            return $this->export($query, $request->type);
        }
        return $query->paginate($request->rowsPerPage);
    }

    public function MerchantTransactionTable(Request $request, $merchant)
    {
        $query = $this->search(MerchantTransaction::class, ['wallet:id,wallet_number', 'adminUser:id,name'])
            ->where('merchant_id', $merchant);

        if ($request->export) {
            return $this->export($query, $request->type);
        }
        return $query->paginate($request->rowsPerPage);
    }
}
