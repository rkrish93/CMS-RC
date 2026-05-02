<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PermissionGroup;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Permission Groups
        $groups = [
            ['id' => 1, 'name' => 'Users Management'],
            ['id' => 2, 'name' => 'Patients Management'],
            ['id' => 3, 'name' => 'Appointments Management'],
            ['id' => 4, 'name' => 'Consultations Management'],
            ['id' => 5, 'name' => 'Units Management'],
            ['id' => 6, 'name' => 'Reports & Analytics'],
            ['id' => 7, 'name' => 'Menu & Dashboard'],
        ];

        foreach ($groups as $group) {
            PermissionGroup::firstOrCreate(['id' => $group['id']], ['group_name' => $group['name']]);
        }

        // Users Management Permissions
        $userPermissions = [
            'users-view',
            'users-create',
            'users-edit',
            'users-delete',
        ];

        // Patients Management Permissions
        $patientPermissions = [
            'patients-view',
            'patients-create',
            'patients-edit',
            'patients-delete',
        ];

        // Appointments Management Permissions
        $appointmentPermissions = [
            'appointments-view',
            'appointments-create',
            'appointments-edit',
            'appointments-delete',
        ];

        // Consultations Management Permissions
        $consultationPermissions = [
            'consultations-view',
            'consultations-create',
            'consultations-edit',
            'consultations-delete',
        ];

        // Units Management Permissions
        $unitPermissions = [
            'units-view',
            'units-create',
            'units-edit',
            'units-delete',
        ];

        // Reports & Analytics Permissions
        $reportPermissions = [
            'reports-view',
            'reports-export',
            'analytics-view',
        ];

        // Menu & Dashboard Permissions
        $menuDashboardPermissions = [
            'dashboard-view',
            'menu-users',
            'menu-patients',
            'menu-appointments',
            'menu-consultations',
            'menu-units',
            'menu-reports',
            'menu-roles',
            'menu-permissions',
        ];

        // Create all permissions
        $allPermissions = [
            1 => $userPermissions,
            2 => $patientPermissions,
            3 => $appointmentPermissions,
            4 => $consultationPermissions,
            5 => $unitPermissions,
            6 => $reportPermissions,
            7 => $menuDashboardPermissions,
        ];

        foreach ($allPermissions as $groupId => $permissions) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(
                    ['name' => $permission, 'guard_name' => 'web'],
                    ['group_id' => $groupId]
                );
            }
        }

        // Create Roles
        // Remove old Super Admin role if exists
        Role::where('name', 'Super Admin')->delete();

        $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $doctorRole = Role::firstOrCreate(['name' => 'Doctor', 'guard_name' => 'web']);
        $nurseRole = Role::firstOrCreate(['name' => 'Nurse', 'guard_name' => 'web']);
        $receptionist = Role::firstOrCreate(['name' => 'Receptionist', 'guard_name' => 'web']);
        $midwifeRole = Role::firstOrCreate(['name' => 'Mid wife', 'guard_name' => 'web']);
        $phiRole = Role::firstOrCreate(['name' => 'PHI', 'guard_name' => 'web']);
        $pharmacistRole = Role::firstOrCreate(['name' => 'Pharmacist', 'guard_name' => 'web']);

        // Assign all permissions to Admin
        $adminRole->syncPermissions(Permission::all());

        // Assign specific permissions to Doctor
        $doctorPermissions = Permission::whereIn('name', [
            'dashboard-view',
            'menu-patients',
            'menu-appointments',
            'menu-consultations',
            'menu-reports',
            'patients-view',
            'patients-edit',
            'appointments-view',
            'appointments-create',
            'appointments-edit',
            'consultations-view',
            'consultations-create',
            'consultations-edit',
            'reports-view',
            'reports-export',
        ])->get();
        $doctorRole->syncPermissions($doctorPermissions);

        // Assign specific permissions to Nurse
        $nursePermissions = Permission::whereIn('name', [
            'dashboard-view',
            'menu-patients',
            'menu-appointments',
            'menu-consultations',
            'menu-reports',
            'patients-view',
            'patients-edit',
            'appointments-view',
            'appointments-edit',
            'consultations-view',
            'consultations-edit',
            'reports-view',
        ])->get();
        $nurseRole->syncPermissions($nursePermissions);

        // Assign specific permissions to Receptionist
        $receptionistPermissions = Permission::whereIn('name', [
            'dashboard-view',
            'menu-patients',
            'menu-appointments',
            'patients-view',
            'patients-create',
            'patients-edit',
            'appointments-view',
            'appointments-create',
            'appointments-edit',
        ])->get();
        $receptionist->syncPermissions($receptionistPermissions);

        // Assign specific permissions to Mid wife
        $midwifePermissions = Permission::whereIn('name', [
            'dashboard-view',
            'menu-patients',
            'menu-appointments',
            'menu-consultations',
            'menu-reports',
            'patients-view',
            'patients-edit',
            'appointments-view',
            'appointments-edit',
            'consultations-view',
            'consultations-create',
            'consultations-edit',
            'reports-view',
        ])->get();
        $midwifeRole->syncPermissions($midwifePermissions);

        // Assign specific permissions to PHI (Public Health Inspector)
        $phiPermissions = Permission::whereIn('name', [
            'dashboard-view',
            'menu-patients',
            'menu-appointments',
            'menu-units',
            'menu-reports',
            'patients-view',
            'patients-edit',
            'appointments-view',
            'units-view',
            'reports-view',
            'reports-export',
            'analytics-view',
        ])->get();
        $phiRole->syncPermissions($phiPermissions);

        // Assign specific permissions to Pharmacist
        $pharmacistPermissions = Permission::whereIn('name', [
            'dashboard-view',
            'menu-patients',
            'menu-consultations',
            'menu-reports',
            'patients-view',
            'consultations-view',
            'reports-view',
        ])->get();
        $pharmacistRole->syncPermissions($pharmacistPermissions);
    }
}
