<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * إعادة ترقيم عمود order لكل مجموعة إخوة (نفس parent_id) ليكون 0، 1، 2، ...
     * حتى يظهر الترقيم 1، 2، 3 ضمن كل مستوى.
     */
    public function up(): void
    {
        $groups = DB::table('subject_sections')
            ->select('subject_id', 'parent_id')
            ->groupBy('subject_id', 'parent_id')
            ->get();

        foreach ($groups as $group) {
            $sections = DB::table('subject_sections')
                ->where('subject_id', $group->subject_id)
                ->where('parent_id', $group->parent_id)
                ->orderBy('order')
                ->orderBy('id')
                ->pluck('id');

            foreach ($sections->values() as $index => $id) {
                DB::table('subject_sections')->where('id', $id)->update(['order' => $index]);
            }
        }
    }

    public function down(): void
    {
        // لا يمكن استرجاع القيم القديمة
    }
};
