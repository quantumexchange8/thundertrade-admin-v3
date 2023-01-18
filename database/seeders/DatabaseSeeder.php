<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Ranking;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\RunningNumber;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        RunningNumber::insert([
            [
                'type' => 'wallet_number',
                'prefix' => 'TT',
                'digits' => 8,
            ],
            [
                'type' => 'transaction_number',
                'prefix' => 'UTT',
                'digits' => 6,
            ]
        ]);
        Ranking::create(
            [
                'level' => 'Standard',
                'amount' => 0,
                'deposit' => 1,
                'withdrawal' => 1,
            ]
        );
        Ranking::create(
            [
                'level' => 'Diamond',
                'amount' => 100000,
                'deposit' => 1,
                'withdrawal' => 0.51,
            ]
        );
        Ranking::create(
            [
                'level' => 'VIP',
                'amount' => 1000000,
                'deposit' => 1,
                'withdrawal' => 0,
            ]
        );

        $dGroup = PermissionGroup::create(
            [
                'name' => "Deposit & Withdrawal",
                'code' => 'deposit-withdrawal'
            ]
        );

        Permission::insert([
            [
                'group_id' => $dGroup->id,
                'name' => 'View Gross Deposit',
                'code' => 'view-gross-deposit'
            ],
            [
                'group_id' => $dGroup->id,
                'name' => 'View Gross Withdrawal',
                'code' => 'view-gross-withdrawal'
            ],
            [
                'group_id' => $dGroup->id,
                'name' => 'View Net Deposit',
                'code' => 'view-net-deposit'
            ],
            [
                'group_id' => $dGroup->id,
                'name' => 'Allow Top Up Fund',
                'code' => 'allow-top-up-fund'
            ],
            [
                'group_id' => $dGroup->id,
                'name' => 'Allow Withdrawal Fund',
                'code' => 'allow-withdrawal-fund'
            ]
        ]);

        $rGroup = PermissionGroup::create(
            [
                'name' => "Report",
                'code' => 'report',
            ]
        );

        Permission::insert([
            [
                'group_id' => $rGroup->id,
                'name' => 'View Report',
                'code' => 'view-report'
            ],
            [
                'group_id' => $rGroup->id,
                'name' => 'View Combine',
                'code' => 'view-combine'
            ],
            [
                'group_id' => $rGroup->id,
                'name' => 'View In & Out',
                'code' => 'view-in-out'
            ]
        ]);

        $sGroup = PermissionGroup::create(
            [
                'name' => "Sub-Admin",
                'code' => 'sub-admin'
            ]
        );
        Permission::insert([
            [
                'group_id' => $sGroup->id,
                'name' => 'View Sub-Admin Listing',
                'code' => 'view-sub-admin-listing'
            ],
            [
                'group_id' => $sGroup->id,
                'name' => 'View Sub-Admin Activity Log',
                'code' => 'view-sub-admin-activity-log'
            ],
            [
                'group_id' => $sGroup->id,
                'name' => 'Allow Create Sub-Admin',
                'code' => 'allow-create-sub-admin'
            ],
            [
                'group_id' => $sGroup->id,
                'name' => 'Allow Delete Sub-Admin',
                'code' => 'allow-delete-sub-admin'
            ],
            [
                'group_id' => $sGroup->id,
                'name' => 'Allow Edit User Roles',
                'code' => 'allow-edit-user-roles'
            ]
        ]);

        $oGroup = PermissionGroup::create(['name' => 'Others', 'code' => 'others']);
        Permission::insert([
            [
                'group_id' => $oGroup->id,
                'name' => 'Allow Change 6 Digit PIN',
                'code' => 'allow-change-6-digit-pin',
            ],
            [
                'group_id' => $oGroup->id,
                'name' => 'Allow Change Protocol',
                'code' => 'allow-change-protocol',
            ],
            [
                'group_id' => $oGroup->id,
                'name' => 'Allow Edit Profile',
                'code' => 'allow-edit-profile',
            ],
        ]);
        $adminRole =  Role::create(['name' => 'Admin']);

        $permissions = Permission::query()->pluck('id');
        foreach ($permissions as $permission) {
            RolePermission::create(['role_id' => $adminRole->id, 'permission_id' => $permission]);
        }
        $merchantAdminRole =  Role::create(['name' => 'Merchant Admin Template']);
        foreach ($permissions as $permission) {
            RolePermission::create(['role_id' => $merchantAdminRole->id, 'permission_id' => $permission]);
        }
        User::create([
            'name' => 'Superadmin',
            'email' => 'superadmin@admin.com',
            'password' => Hash::make('Test1234.'),
            'phone' => '12345678',
            'role_id' => $adminRole->id,
        ]);
    }
}
