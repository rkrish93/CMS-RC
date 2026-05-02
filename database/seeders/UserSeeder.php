<?php

namespace Database\Seeders;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::where('name', 'Admin')->first();
        $doctorRole = Role::where('name', 'doctor')->first(); // lowercase in database
        $nurseRole = Role::where('name', 'Nurse')->first();
        $receptionistRole = Role::where('name', 'Receptionist')->first();
        $midwifeRole = Role::where('name', 'Mid wife')->first();
        $phiRole = Role::where('name', 'PHI')->first();
        $pharmacistRole = Role::where('name', 'Pharmacist')->first();

        // Get first unit or create one if none exists
        $unit = Unit::first();
        if (!$unit) {
            $unit = Unit::create([
                'unit_name' => 'Main Hospital',
                'unit_code' => 'MAIN',
                'location' => 'City Center',
                'contact_number' => '011-1234567',
                'status' => 1,
            ]);
        }

        // Create Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@cmsrc.com'],
            [
                'fname' => 'System',
                'lname' => 'Administrator',
                'phone' => '0771234567',
                'nic' => '123456789V',
                'designation' => 'System Administrator',
                'unit_id' => $unit->id,
                'join_date' => now()->format('Y-m-d'),
                'status' => 1,
                'password' => Hash::make('123456789V'), // NIC as password
                'force_password_change' => 0,
            ]
        );
        $admin->assignRole($adminRole);

        // Create Doctor Users
        $doctors = [
            [
                'fname' => 'Dr. John',
                'lname' => 'Smith',
                'email' => 'doctor1@cmsrc.com',
                'phone' => '0772345678',
                'nic' => '234567890V',
                'designation' => 'Senior Doctor',
            ],
            [
                'fname' => 'Dr. Sarah',
                'lname' => 'Johnson',
                'email' => 'doctor2@cmsrc.com',
                'phone' => '0773456789',
                'nic' => '345678901V',
                'designation' => 'Pediatrician',
            ],
        ];

        foreach ($doctors as $doctorData) {
            $doctor = User::firstOrCreate(
                ['email' => $doctorData['email']],
                array_merge($doctorData, [
                    'unit_id' => $unit->id,
                    'join_date' => now()->format('Y-m-d'),
                    'status' => 1,
                    'password' => Hash::make($doctorData['nic']),
                    'force_password_change' => 0,
                ])
            );
            $doctor->assignRole($doctorRole);
        }

        // Create Nurse Users
        $nurses = [
            [
                'fname' => 'Mary',
                'lname' => 'Williams',
                'email' => 'nurse1@cmsrc.com',
                'phone' => '0774567890',
                'nic' => '456789012V',
                'designation' => 'Staff Nurse',
            ],
            [
                'fname' => 'Emma',
                'lname' => 'Brown',
                'email' => 'nurse2@cmsrc.com',
                'phone' => '0775678901',
                'nic' => '567890123V',
                'designation' => 'Senior Nurse',
            ],
        ];

        foreach ($nurses as $nurseData) {
            $nurse = User::firstOrCreate(
                ['email' => $nurseData['email']],
                array_merge($nurseData, [
                    'unit_id' => $unit->id,
                    'join_date' => now()->format('Y-m-d'),
                    'status' => 1,
                    'password' => Hash::make($nurseData['nic']),
                    'force_password_change' => 0,
                ])
            );
            $nurse->assignRole($nurseRole);
        }

        // Create Receptionist Users
        $receptionists = [
            [
                'fname' => 'Lisa',
                'lname' => 'Davis',
                'email' => 'receptionist1@cmsrc.com',
                'phone' => '0776789012',
                'nic' => '678901234V',
                'designation' => 'Receptionist',
            ],
        ];

        foreach ($receptionists as $receptionistData) {
            $receptionist = User::firstOrCreate(
                ['email' => $receptionistData['email']],
                array_merge($receptionistData, [
                    'unit_id' => $unit->id,
                    'join_date' => now()->format('Y-m-d'),
                    'status' => 1,
                    'password' => Hash::make($receptionistData['nic']),
                    'force_password_change' => 0,
                ])
            );
            $receptionist->assignRole($receptionistRole);
        }

        // Create Midwife Users
        $midwives = [
            [
                'fname' => 'Anna',
                'lname' => 'Miller',
                'email' => 'midwife1@cmsrc.com',
                'phone' => '0777890123',
                'nic' => '789012345V',
                'designation' => 'Midwife',
            ],
        ];

        foreach ($midwives as $midwifeData) {
            $midwife = User::firstOrCreate(
                ['email' => $midwifeData['email']],
                array_merge($midwifeData, [
                    'unit_id' => $unit->id,
                    'join_date' => now()->format('Y-m-d'),
                    'status' => 1,
                    'password' => Hash::make($midwifeData['nic']),
                    'force_password_change' => 0,
                ])
            );
            $midwife->assignRole($midwifeRole);
        }

        // Create PHI Users
        $phis = [
            [
                'fname' => 'Robert',
                'lname' => 'Wilson',
                'email' => 'phi1@cmsrc.com',
                'phone' => '0778901234',
                'nic' => '890123456V',
                'designation' => 'Public Health Inspector',
            ],
        ];

        foreach ($phis as $phiData) {
            $phi = User::firstOrCreate(
                ['email' => $phiData['email']],
                array_merge($phiData, [
                    'unit_id' => $unit->id,
                    'join_date' => now()->format('Y-m-d'),
                    'status' => 1,
                    'password' => Hash::make($phiData['nic']),
                    'force_password_change' => 0,
                ])
            );
            $phi->assignRole($phiRole);
        }

        // Create Pharmacist Users
        $pharmacists = [
            [
                'fname' => 'James',
                'lname' => 'Taylor',
                'email' => 'pharmacist1@cmsrc.com',
                'phone' => '0779012345',
                'nic' => '901234567V',
                'designation' => 'Pharmacist',
            ],
        ];

        foreach ($pharmacists as $pharmacistData) {
            $pharmacist = User::firstOrCreate(
                ['email' => $pharmacistData['email']],
                array_merge($pharmacistData, [
                    'unit_id' => $unit->id,
                    'join_date' => now()->format('Y-m-d'),
                    'status' => 1,
                    'password' => Hash::make($pharmacistData['nic']),
                    'force_password_change' => 0,
                ])
            );
            $pharmacist->assignRole($pharmacistRole);
        }
    }
}
