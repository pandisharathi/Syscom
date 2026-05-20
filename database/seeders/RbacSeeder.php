<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Course;
use App\Models\ExpenseType;
use App\Models\Faculty;
use App\Models\Institution;
use App\Models\InstitutionModule;
use App\Models\InternshipBatch;
use App\Models\InternshipCourse;
use App\Models\InternshipStudent;
use App\Models\Menu;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect([
            ['name' => 'View dashboard', 'slug' => 'dashboard.view', 'group' => 'dashboard'],
            ['name' => 'Manage institutions', 'slug' => 'institutions.manage', 'group' => 'institutions'],
            ['name' => 'Manage courses', 'slug' => 'courses.manage', 'group' => 'academics'],
            ['name' => 'Manage batches', 'slug' => 'batches.manage', 'group' => 'academics'],
            ['name' => 'Manage faculty', 'slug' => 'faculty.manage', 'group' => 'academics'],
            ['name' => 'Manage students', 'slug' => 'students.manage', 'group' => 'academics'],
            ['name' => 'Mark attendance', 'slug' => 'attendance.manage', 'group' => 'attendance'],
            ['name' => 'Attendance reports', 'slug' => 'attendance.reports', 'group' => 'attendance'],
            ['name' => 'Internship courses', 'slug' => 'internship.courses', 'group' => 'internship'],
            ['name' => 'Internship batches', 'slug' => 'internship.batches', 'group' => 'internship'],
            ['name' => 'Internship enquiries', 'slug' => 'internship.enquiries', 'group' => 'internship'],
            ['name' => 'Internship students', 'slug' => 'internship.students', 'group' => 'internship'],
            ['name' => 'Internship attendance', 'slug' => 'internship.attendance', 'group' => 'internship'],
            ['name' => 'Internship reports', 'slug' => 'internship.reports', 'group' => 'internship'],
            ['name' => 'Internship payments', 'slug' => 'internship.payments', 'group' => 'internship'],
            ['name' => 'Internship certificates', 'slug' => 'internship.certificates', 'group' => 'internship'],
            ['name' => 'Expense types', 'slug' => 'expense.types', 'group' => 'expense'],
            ['name' => 'Manage expenses', 'slug' => 'expense.manage', 'group' => 'expense'],
            ['name' => 'Expense reports', 'slug' => 'expense.reports', 'group' => 'expense'],
            ['name' => 'Global reports', 'slug' => 'reports.view', 'group' => 'reports'],
            ['name' => 'Settings', 'slug' => 'settings.manage', 'group' => 'settings'],
            ['name' => 'Manage users', 'slug' => 'users.manage', 'group' => 'users'],
            ['name' => 'Manage roles & permissions', 'slug' => 'permissions.manage', 'group' => 'users'],
        ])->mapWithKeys(fn (array $p) => [
            $p['slug'] => Permission::query()->updateOrCreate(
                ['slug' => $p['slug']],
                ['name' => $p['name'], 'group' => $p['group'], 'description' => null]
            ),
        ]);

        $super = Role::query()->updateOrCreate(
            ['slug' => User::ROLE_SUPER_ADMIN],
            ['name' => 'Super Admin', 'description' => 'Full system access', 'is_system' => true]
        );
        $super->permissions()->sync($permissions->pluck('id'));

        $admin = Role::query()->updateOrCreate(
            ['slug' => 'admin'],
            ['name' => 'Admin / Institution', 'description' => 'Institution administrator', 'is_system' => true]
        );
        $admin->permissions()->sync(
            $permissions->except(['permissions.manage', 'institutions.manage'])->pluck('id')
        );

        $faculty = Role::query()->updateOrCreate(
            ['slug' => 'faculty'],
            ['name' => 'Faculty', 'description' => 'Teaching staff', 'is_system' => true]
        );
        $faculty->permissions()->sync(
            $permissions->only([
                'dashboard.view',
                'attendance.manage',
                'attendance.reports',
                'internship.attendance',
                'internship.reports',
                'students.manage',
                'reports.view',
            ])->pluck('id')
        );

        $staff = Role::query()->updateOrCreate(
            ['slug' => 'staff'],
            ['name' => 'Staff', 'description' => 'Operations staff', 'is_system' => true]
        );
        $staff->permissions()->sync(
            $permissions->only([
                'dashboard.view',
                'expense.types',
                'expense.manage',
                'expense.reports',
                'internship.enquiries',
                'students.manage',
                'reports.view',
            ])->pluck('id')
        );

        $student = Role::query()->updateOrCreate(
            ['slug' => 'student'],
            ['name' => 'Student', 'description' => 'Student portal', 'is_system' => true]
        );
        $student->permissions()->sync(
            $permissions->only(['dashboard.view', 'reports.view'])->pluck('id')
        );

        $this->seedMenus();

        $institution = Institution::query()->updateOrCreate(
            ['code' => 'DEMO001'],
            [
                'name' => 'Demo Institution',
                'email' => 'contact@demo.edu',
                'phone' => '+911234567890',
                'address' => 'Demo City',
                'subscription_plan' => 'enterprise',
                'subscription_starts_at' => now()->subYear(),
                'subscription_ends_at' => now()->addYear(),
                'is_active' => true,
                'enquiry_enabled' => true,
            ]
        );

        foreach ([
            'dashboard', 'institutions', 'students', 'internship', 'attendance', 'expense', 'reports', 'settings', 'users',
        ] as $key) {
            InstitutionModule::query()->updateOrCreate(
                ['institution_id' => $institution->id, 'module_key' => $key],
                ['enabled' => true]
            );
        }

        if (! User::query()->where('email', 'superadmin@syscom.test')->exists()) {
            User::query()->create([
                'name' => 'Super Admin',
                'email' => 'superadmin@syscom.test',
                'password' => Hash::make('password'),
                'role_id' => $super->id,
                'institution_id' => null,
                'status' => User::STATUS_ACTIVE,
            ]);
        }

        if (! User::query()->where('email', 'admin@demo.edu')->exists()) {
            User::query()->create([
                'name' => 'Institution Admin',
                'email' => 'admin@demo.edu',
                'password' => Hash::make('password'),
                'role_id' => $admin->id,
                'institution_id' => $institution->id,
                'status' => User::STATUS_ACTIVE,
            ]);
        }

        Faculty::query()->firstOrCreate(
            ['institution_id' => $institution->id, 'email' => 'faculty@demo.edu'],
            [
                'first_name' => 'Jane',
                'last_name' => 'Faculty',
                'qualification' => 'M.Tech',
                'experience_years' => 5,
                'phone' => '+919999999999',
                'address' => 'Campus',
                'status' => 'active',
            ]
        );

        if (! User::query()->where('email', 'faculty@demo.edu')->exists()) {
            User::query()->create([
                'name' => 'Jane Faculty',
                'email' => 'faculty@demo.edu',
                'password' => Hash::make('password'),
                'role_id' => $faculty->id,
                'institution_id' => $institution->id,
                'status' => User::STATUS_ACTIVE,
            ]);
        }

        Course::query()->firstOrCreate(
            ['institution_id' => $institution->id, 'code' => 'CS101'],
            [
                'name' => 'Computer Science Fundamentals',
                'duration' => '6 months',
                'fees' => 25000,
                'description' => 'Intro course',
                'status' => 'active',
            ]
        );

        InternshipCourse::query()->firstOrCreate(
            ['institution_id' => $institution->id, 'code' => 'INT-WEB'],
            [
                'name' => 'Web Development Internship',
                'duration' => '3 months',
                'fees' => 15000,
                'description' => 'Full stack internship covering HTML, CSS, JavaScript, React, and Laravel',
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->addMonths(3),
                'status' => 'active',
            ]
        );

        InternshipCourse::query()->firstOrCreate(
            ['institution_id' => $institution->id, 'code' => 'INT-DS'],
            [
                'name' => 'Data Science Internship',
                'duration' => '6 months',
                'fees' => 25000,
                'description' => 'Data science internship covering Python, ML, and数据分析',
                'start_date' => now()->addMonth(),
                'end_date' => now()->addMonths(7),
                'status' => 'active',
            ]
        );

        InternshipCourse::query()->firstOrCreate(
            ['institution_id' => $institution->id, 'code' => 'INT-DM'],
            [
                'name' => 'Digital Marketing Internship',
                'duration' => '3 months',
                'fees' => 10000,
                'description' => 'Digital marketing internship covering SEO, SEM, and social media',
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->addMonths(3),
                'status' => 'active',
            ]
        );

        ExpenseType::query()->firstOrCreate(
            ['institution_id' => $institution->id, 'code' => 'SUP'],
            ['name' => 'Supplies', 'status' => 'active']
        );

        $course = Course::query()->where('institution_id', $institution->id)->first();
        $facultyRow = Faculty::query()->where('institution_id', $institution->id)->first();
        Batch::query()->firstOrCreate(
            [
                'institution_id' => $institution->id,
                'course_id' => $course->id,
                'name' => 'Batch A — '.now()->year,
            ],
            [
                'faculty_id' => $facultyRow?->id,
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->addMonths(6),
                'timing' => '10:00 AM - 1:00 PM',
                'number_of_days' => 120,
                'status' => 'active',
            ]
        );

        // --- Internship seed data ---

        $intWeb = InternshipCourse::query()->where('code', 'INT-WEB')->where('institution_id', $institution->id)->first();
        $intDs = InternshipCourse::query()->where('code', 'INT-DS')->where('institution_id', $institution->id)->first();

        $intBatch1 = InternshipBatch::query()->firstOrCreate(
            [
                'institution_id' => $institution->id,
                'internship_course_id' => $intWeb?->id,
                'name' => 'Web Dev Batch A — '.now()->year,
            ],
            [
                'faculty_id' => $facultyRow?->id,
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->addMonths(3),
                'timing' => '9:00 AM - 12:00 PM',
                'capacity' => 30,
                'number_of_days' => 60,
                'status' => 'active',
            ]
        );

        $intBatch2 = InternshipBatch::query()->firstOrCreate(
            [
                'institution_id' => $institution->id,
                'internship_course_id' => $intDs?->id,
                'name' => 'Data Science Batch A — '.now()->year,
            ],
            [
                'faculty_id' => $facultyRow?->id,
                'start_date' => now()->addMonth(),
                'end_date' => now()->addMonths(7),
                'timing' => '2:00 PM - 5:00 PM',
                'capacity' => 25,
                'number_of_days' => 120,
                'status' => 'active',
            ]
        );

        $students = [
            [
                'first_name' => 'Aarav', 'last_name' => 'Sharma',
                'email' => 'aarav.sharma@example.com', 'phone' => '+919000000001',
                'whatsapp_number' => '+919000000001', 'gender' => 'male',
                'date_of_birth' => '2002-05-15', 'educational_qualification' => 'B.Sc. Computer Science',
                'college_name' => 'Delhi University', 'address' => '123, MG Road',
                'city' => 'Delhi', 'state' => 'Delhi', 'pincode' => '110001',
                'joining_date' => now()->subDays(30), 'status' => 'active',
            ],
            [
                'first_name' => 'Priya', 'last_name' => 'Patel',
                'email' => 'priya.patel@example.com', 'phone' => '+919000000002',
                'whatsapp_number' => '+919000000002', 'gender' => 'female',
                'date_of_birth' => '2003-08-22', 'educational_qualification' => 'B.Tech IT',
                'college_name' => 'Mumbai University', 'address' => '456, Linking Road',
                'city' => 'Mumbai', 'state' => 'Maharashtra', 'pincode' => '400001',
                'joining_date' => now()->subDays(20), 'status' => 'active',
            ],
            [
                'first_name' => 'Rahul', 'last_name' => 'Verma',
                'email' => 'rahul.verma@example.com', 'phone' => '+919000000003',
                'whatsapp_number' => '+919000000003', 'gender' => 'male',
                'date_of_birth' => '2001-12-10', 'educational_qualification' => 'BCA',
                'college_name' => 'Pune University', 'address' => '789, FC Road',
                'city' => 'Pune', 'state' => 'Maharashtra', 'pincode' => '411001',
                'joining_date' => now()->subDays(45), 'status' => 'active',
            ],
            [
                'first_name' => 'Sneha', 'last_name' => 'Reddy',
                'email' => 'sneha.reddy@example.com', 'phone' => '+919000000004',
                'whatsapp_number' => '+919000000004', 'gender' => 'female',
                'date_of_birth' => '2002-03-18', 'educational_qualification' => 'B.E. CSE',
                'college_name' => 'Anna University', 'address' => '321, Mount Road',
                'city' => 'Chennai', 'state' => 'Tamil Nadu', 'pincode' => '600001',
                'joining_date' => now()->subDays(15), 'status' => 'inactive',
            ],
            [
                'first_name' => 'Vikram', 'last_name' => 'Singh',
                'email' => 'vikram.singh@example.com', 'phone' => '+919000000005',
                'whatsapp_number' => '+919000000005', 'gender' => 'male',
                'date_of_birth' => '2000-07-25', 'educational_qualification' => 'M.Sc. Data Science',
                'college_name' => 'IIT Bombay', 'address' => '55, Powai Estate',
                'city' => 'Mumbai', 'state' => 'Maharashtra', 'pincode' => '400076',
                'joining_date' => now()->subDays(60), 'status' => 'completed',
            ],
        ];

        foreach ($students as $i => $s) {
            InternshipStudent::query()->firstOrCreate(
                [
                    'institution_id' => $institution->id,
                    'email' => $s['email'],
                ],
                array_merge($s, [
                    'institution_id' => $institution->id,
                    'internship_batch_id' => $i < 3 ? $intBatch1->id : $intBatch2->id,
                ])
            );
        }
    }

    private function seedMenus(): void
    {
        $dashboard = Menu::query()->updateOrCreate(
            ['route_name' => 'admin.dashboard'],
            ['parent_id' => null, 'name' => 'Dashboard', 'icon' => 'fa-gauge-high', 'permission_slug' => 'dashboard.view', 'module_key' => 'dashboard', 'sort_order' => 1, 'is_active' => true]
        );

        $inst = Menu::query()->updateOrCreate(
            ['route_name' => 'admin.institutions.index'],
            ['parent_id' => null, 'name' => 'Institutions', 'icon' => 'fa-building', 'permission_slug' => 'institutions.manage', 'module_key' => 'institutions', 'sort_order' => 2, 'is_active' => true]
        );

        $students = Menu::query()->updateOrCreate(
            ['name' => 'Student Management', 'parent_id' => null],
            ['route_name' => null, 'icon' => 'fa-user-graduate', 'permission_slug' => null, 'module_key' => 'students', 'sort_order' => 10, 'is_active' => true]
        );

        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.courses.index', 'parent_id' => $students->id],
            ['name' => 'Courses', 'icon' => 'fa-book', 'permission_slug' => 'courses.manage', 'module_key' => 'students', 'sort_order' => 1, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.batches.index', 'parent_id' => $students->id],
            ['name' => 'Batches', 'icon' => 'fa-layer-group', 'permission_slug' => 'batches.manage', 'module_key' => 'students', 'sort_order' => 2, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.faculties.index', 'parent_id' => $students->id],
            ['name' => 'Faculty', 'icon' => 'fa-chalkboard-user', 'permission_slug' => 'faculty.manage', 'module_key' => 'students', 'sort_order' => 3, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.students.index', 'parent_id' => $students->id],
            ['name' => 'Students', 'icon' => 'fa-users', 'permission_slug' => 'students.manage', 'module_key' => 'students', 'sort_order' => 4, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.attendances.index', 'parent_id' => $students->id],
            ['name' => 'Attendance', 'icon' => 'fa-clipboard-check', 'permission_slug' => 'attendance.manage', 'module_key' => 'attendance', 'sort_order' => 5, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.attendance-reports.index', 'parent_id' => $students->id],
            ['name' => 'Attendance Reports', 'icon' => 'fa-file-lines', 'permission_slug' => 'attendance.reports', 'module_key' => 'attendance', 'sort_order' => 6, 'is_active' => true]
        );

        $intern = Menu::query()->updateOrCreate(
            ['name' => 'Internship Management', 'parent_id' => null],
            ['route_name' => null, 'icon' => 'fa-briefcase', 'permission_slug' => null, 'module_key' => 'internship', 'sort_order' => 20, 'is_active' => true]
        );

        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.internship-courses.index', 'parent_id' => $intern->id],
            ['name' => 'Internship Courses', 'icon' => 'fa-book-open', 'permission_slug' => 'internship.courses', 'module_key' => 'internship', 'sort_order' => 1, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.internship-batches.index', 'parent_id' => $intern->id],
            ['name' => 'Internship Batches', 'icon' => 'fa-layer-group', 'permission_slug' => 'internship.batches', 'module_key' => 'internship', 'sort_order' => 2, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.internship-enquiries.index', 'parent_id' => $intern->id],
            ['name' => 'Internship Enquiries', 'icon' => 'fa-envelope', 'permission_slug' => 'internship.enquiries', 'module_key' => 'internship', 'sort_order' => 3, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.internship-students.index', 'parent_id' => $intern->id],
            ['name' => 'Internship Students', 'icon' => 'fa-user-group', 'permission_slug' => 'internship.students', 'module_key' => 'internship', 'sort_order' => 4, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.internship-attendances.index', 'parent_id' => $intern->id],
            ['name' => 'Internship Attendance', 'icon' => 'fa-clipboard-list', 'permission_slug' => 'internship.attendance', 'module_key' => 'internship', 'sort_order' => 5, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.internship-reports.index', 'parent_id' => $intern->id],
            ['name' => 'Internship Reports', 'icon' => 'fa-chart-column', 'permission_slug' => 'internship.reports', 'module_key' => 'internship', 'sort_order' => 6, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.internship-payments.index', 'parent_id' => $intern->id],
            ['name' => 'Internship Payments', 'icon' => 'fa-money-bill-wave', 'permission_slug' => 'internship.payments', 'module_key' => 'internship', 'sort_order' => 7, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.internship-certificates.templates', 'parent_id' => $intern->id],
            ['name' => 'Certificate Templates', 'icon' => 'fa-panorama', 'permission_slug' => 'internship.certificates', 'module_key' => 'internship', 'sort_order' => 8, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.internship-certificates.generate', 'parent_id' => $intern->id],
            ['name' => 'Generate Certificate', 'icon' => 'fa-award', 'permission_slug' => 'internship.certificates', 'module_key' => 'internship', 'sort_order' => 9, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.internship-certificates.index', 'parent_id' => $intern->id],
            ['name' => 'Certificate List', 'icon' => 'fa-list-check', 'permission_slug' => 'internship.certificates', 'module_key' => 'internship', 'sort_order' => 10, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.internship-certificates.verification-logs', 'parent_id' => $intern->id],
            ['name' => 'Verification Logs', 'icon' => 'fa-clock-rotate-left', 'permission_slug' => 'internship.certificates', 'module_key' => 'internship', 'sort_order' => 11, 'is_active' => true]
        );

        $exp = Menu::query()->updateOrCreate(
            ['name' => 'Expenses', 'parent_id' => null],
            ['route_name' => null, 'icon' => 'fa-wallet', 'permission_slug' => null, 'module_key' => 'expense', 'sort_order' => 30, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.expense-types.index', 'parent_id' => $exp->id],
            ['name' => 'Expense Types', 'icon' => 'fa-tags', 'permission_slug' => 'expense.types', 'module_key' => 'expense', 'sort_order' => 1, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.expenses.create', 'parent_id' => $exp->id],
            ['name' => 'Add Expense', 'icon' => 'fa-plus', 'permission_slug' => 'expense.manage', 'module_key' => 'expense', 'sort_order' => 2, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.expenses.index', 'parent_id' => $exp->id],
            ['name' => 'Expense List', 'icon' => 'fa-list', 'permission_slug' => 'expense.manage', 'module_key' => 'expense', 'sort_order' => 3, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.expense-reports.index', 'parent_id' => $exp->id],
            ['name' => 'Expense Reports', 'icon' => 'fa-file-invoice-dollar', 'permission_slug' => 'expense.reports', 'module_key' => 'expense', 'sort_order' => 4, 'is_active' => true]
        );

        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.reports.index'],
            ['parent_id' => null, 'name' => 'Reports', 'icon' => 'fa-chart-pie', 'permission_slug' => 'reports.view', 'module_key' => 'reports', 'sort_order' => 40, 'is_active' => true]
        );

        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.settings.index'],
            ['parent_id' => null, 'name' => 'Settings', 'icon' => 'fa-gear', 'permission_slug' => 'settings.manage', 'module_key' => 'settings', 'sort_order' => 50, 'is_active' => true]
        );

        $usersMenu = Menu::query()->updateOrCreate(
            ['name' => 'Users & Access', 'parent_id' => null],
            ['route_name' => null, 'icon' => 'fa-user-shield', 'permission_slug' => null, 'module_key' => 'users', 'sort_order' => 60, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.users.index', 'parent_id' => $usersMenu->id],
            ['name' => 'Users', 'icon' => 'fa-users-gear', 'permission_slug' => 'users.manage', 'module_key' => 'users', 'sort_order' => 1, 'is_active' => true]
        );
        Menu::query()->updateOrCreate(
            ['route_name' => 'admin.role-permissions.index', 'parent_id' => $usersMenu->id],
            ['name' => 'Roles & Permissions', 'icon' => 'fa-key', 'permission_slug' => 'permissions.manage', 'module_key' => 'users', 'sort_order' => 2, 'is_active' => true]
        );

        // ensure dashboard exists even if duplicate route_name issues — noop
        unset($dashboard, $inst);
    }
}
