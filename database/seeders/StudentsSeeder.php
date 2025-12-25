<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء أو جلب دور الطالب
        $studentRole = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        // قائمة بأسماء عربية حقيقية
        $names = [
            'أحمد محمد علي',
            'محمد خالد حسن',
            'علي أحمد إبراهيم',
            'خالد سعد عبدالله',
            'سعد فهد المطيري',
            'فهد ناصر العتيبي',
            'ناصر عبدالرحمن القحطاني',
            'عبدالرحمن يوسف الشمري',
            'يوسف عمر الدوسري',
            'عمر حمد الزهراني',
            'حمد تركي الحربي',
            'تركي بندر العنزي',
            'بندر ماجد الغامدي',
            'ماجد راشد البقمي',
            'راشد سلطان العسيري',
            'سلطان فيصل الجهني',
            'فيصل مشعل المالكي',
            'مشعل نواف الرشيد',
            'نواف وليد السبيعي',
            'وليد هشام العلي',
            'هشام بدر النجار',
            'بدر طارق الفهد',
            'طارق زياد الصالح',
            'زياد رائد الحسن',
            'رائد معن العلي',
            'معن سامي الكندري',
            'سامي تامر الشمري',
            'تامر جمال العتيبي',
            'جمال رامي القحطاني',
            'رامي وسام الدوسري',
            'وسام لؤي الزهراني',
            'لؤي مروان الحربي',
            'مروان كريم العنزي',
            'كريم يزن الغامدي',
            'يزن مازن البقمي',
            'مازن ريان العسيري',
            'ريان جاد الجهني',
            'جاد نادر المالكي',
            'نادر وسيم الرشيد',
            'وسيم باسل السبيعي',
            'باسل حازم العلي',
            'حازم فارس النجار',
            'فارس قيس الفهد',
            'قيس ليث الصالح',
            'ليث عدي الحسن',
            'عدي زيد العلي',
            'زيد عمرو الكندري',
            'عمرو خليل الشمري',
            'خليل أسامة العتيبي',
            'أسامة حاتم القحطاني',
        ];

        $password = Hash::make('123456789');

        foreach ($names as $index => $name) {
            $email = 'student' . ($index + 1) . '@student.com';
            
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => $password,
                    'phone' => '05' . str_pad(rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            // تعيين دور الطالب
            if (!$user->hasRole('student')) {
                $user->assignRole($studentRole);
            }
        }

        $this->command->info('تم إنشاء ' . count($names) . ' طالب بنجاح!');
        $this->command->info('كلمة المرور الموحدة لجميع الطلاب: 123456789');
    }
}