<?php

namespace App\Services;

use App\Models\FunctionModel;
use App\Models\Role;
use App\Models\RoleDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoleService
{

    public function getAll()
    {
        $roles = Role::withCount([
            'users as total_users'
        ])->get();

        return $roles;
    }

    public function getRoleDetail(Role $role)
    {
        $role = Role::with('role_details.function')->findOrFail($role->id);
        $roleDetails = $role->role_details;

        $permission = $roleDetails
            ->groupBy('function_id')
            ->map(function ($items) {
                return [
                    'function_name' => $items->first()->function->function_name,
                    'canView'       => $items->contains('action', 'VIEW'),
                    'canCreate'     => $items->contains('action', 'ADD'),
                    'canUpdate'     => $items->contains('action', 'UPDATE'),
                    'canDelete'     => $items->contains('action', 'DELETE')
                ];
            })->values();

        $response = [
            "id"           => $role->id,
            "role_name"    => $role->role_name,
            "role_details" => $permission
        ];

        return $response;
    }

    public function getRoleDetailById(int $id)
    {
        $role = Role::with('role_details.function')->find($id);
        if (!$role) return null;

        $roleDetails = $role->role_details;

        $permission = $roleDetails
            ->groupBy('function_id')
            ->map(function ($items) {
                return [
                    'function_name' => $items->first()->function->function_name,
                    'canView'       => $items->contains('action', 'VIEW'),
                    'canCreate'     => $items->contains('action', 'ADD'),
                    'canUpdate'     => $items->contains('action', 'UPDATE'),
                    'canDelete'     => $items->contains('action', 'DELETE')
                ];
            })->values();

        $response = [
            "id"           => $role->id,
            "role_name"    => $role->role_name,
            "role_details" => $permission
        ];

        return $response;
    }

    // *** format gửi tạo 1 phân quyền
    // {
    //   "tenNhomQuyen": "Nhân viên bán hàng",
    //   "role_details": [
    //     {
    //       "tenChucNang": "QL_NHOMHP",
    //       "canView": true,
    //       "canCreate": false,
    //       "canUpdate": false,
    //       "canDelete": false
    //     },
    //     {
    //       "tenChucNang": "QL_CAUHOI",
    //       "canView": true,
    //       "canCreate": true,
    //       "canUpdate": false,
    //       "canDelete": false
    //     }
    //   ]
    // }
    public function createRoleWithPermissions(array $data)
    {
        // Transaction to ensure complete rollback in case of error
        return DB::transaction(function () use ($data) {
            // 1. Create role
            $role = Role::create([
                'role_name' => $data['role_name'],
            ]);

            // 2. Prepare role_details data
            $roleDetailsData = [];
            foreach ($data['role_details'] as $roleDetail) {
                // Find function for each permission set
                $function = FunctionModel::where('function_name', $roleDetail['function_name'])->firstOrFail();

                if ($roleDetail['canView']) {
                    $roleDetailsData[] = [
                        'role_id'     => $role->id,
                        'function_id' => $function->id,
                        'action'      => 'VIEW',
                    ];
                }

                if ($roleDetail['canCreate']) {
                    $roleDetailsData[] = [
                        'role_id'     => $role->id,
                        'function_id' => $function->id,
                        'action'      => 'ADD',
                    ];
                }

                if ($roleDetail['canUpdate']) {
                    $roleDetailsData[] = [
                        'role_id'     => $role->id,
                        'function_id' => $function->id,
                        'action'      => 'UPDATE',
                    ];
                }

                if ($roleDetail['canDelete']) {
                    $roleDetailsData[] = [
                        'role_id'     => $role->id,
                        'function_id' => $function->id,
                        'action'      => 'DELETE',
                    ];
                }
            }

            // 3. Batch insert
            RoleDetail::insert($roleDetailsData);

            return $role->load('role_details.function');
        });
    }

    public function updateRoleWithPermissions(array $data, int $id)
    {
        return DB::transaction(function () use ($data, $id) {

            // 1. Get role to update
            $role = Role::findOrFail($id);

            // 2. Update role info
            $role->update([
                'role_name' => $data['role_name'],
            ]);

            // 3. Delete all old permissions
            RoleDetail::where('role_id', $role->id)->delete();

            // 4. Prepare new permission data
            $roleDetailsData = [];

            foreach ($data['role_details'] as $roleDetail) {

                // Find corresponding function
                $functionCategory = FunctionModel::where('function_name', $roleDetail['function_name'])->firstOrFail(); // NOTE: Action/tenChucNang -> FunctionModel/function_name

                if ($roleDetail['canView']) {
                    $roleDetailsData[] = [
                        'role_id'     => $role->id,
                        'function_id' => $functionCategory->id,
                        'action'      => 'VIEW',
                    ];
                }

                if ($roleDetail['canCreate']) {
                    $roleDetailsData[] = [
                        'role_id'     => $role->id,
                        'function_id' => $functionCategory->id,
                        'action'      => 'ADD',
                    ];
                }

                if ($roleDetail['canUpdate']) {
                    $roleDetailsData[] = [
                        'role_id'     => $role->id,
                        'function_id' => $functionCategory->id,
                        'action'      => 'UPDATE',
                    ];
                }

                if ($roleDetail['canDelete']) {
                    $roleDetailsData[] = [
                        'role_id'     => $role->id,
                        'function_id' => $functionCategory->id,
                        'action'      => 'DELETE',
                    ];
                }
            }

            // 5. Batch insert new permissions
            if (!empty($roleDetailsData)) {
                RoleDetail::insert($roleDetailsData);
            }

            return $role->load('role_details.function');
        });
    }

    public function deleteRoleWithPermissions(int $id)
    {
        return DB::transaction(function () use ($id) {

            // 1. Get role to delete
            $role = Role::findOrFail($id);

            // 2. Delete all permissions of the role
            RoleDetail::where('role_id', $role->id)->delete();

            // 3. Delete role
            $role->delete();

            return true;
        });
    }

    public function hasRole(int $id, string $permission): bool
    { // For middleware check
        $role = Role::findOrFail($id);
        if (!$role || !isset($role['role_details'])) {
            return false; // Return false if no permissions found
        }

        $parts = explode('_', $permission, 2);

        [$action, $function] = $parts;

        $actionMap = [
            'view'   => 'canView',
            'create' => 'canCreate',
            'update' => 'canUpdate',
            'delete' => 'canDelete',
        ];

        $field = $actionMap[$action];

        $roleDetailData = $this->getRoleDetail($role);

        foreach ($roleDetailData["role_details"] as $detail) {
            if ($detail['function_name'] === $function) {
                return $detail[$field]; // Returns true/false
            }
        }

        // Log required and existing permissions
        Log::info("Permission Check", [
            'role_id'              => $id,
            'permission_checked'   => $permission,
            'user_has_permission'  => $this->getRoleDetail($role),
        ]);

        return false;
    }
}
