@if(isset($data['system']))
    <div style="margin-bottom: 30px;">
        <h2 style="color: #333; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
            إحصائيات النظام
        </h2>
        <div style="display: flex; flex-wrap: wrap; justify-content: space-around;">
            <div class="stats-box">
                <h3>{{ $data['system']['total_users'] ?? 0 }}</h3>
                <p>إجمالي المستخدمين</p>
            </div>
            <div class="stats-box">
                <h3>{{ $data['system']['total_students'] ?? 0 }}</h3>
                <p>إجمالي الطلاب</p>
            </div>
            <div class="stats-box">
                <h3>{{ $data['system']['total_subjects'] ?? 0 }}</h3>
                <p>إجمالي الكورسات</p>
            </div>
            <div class="stats-box">
                <h3>{{ $data['system']['total_lessons'] ?? 0 }}</h3>
                <p>إجمالي الدروس</p>
            </div>
            <div class="stats-box">
                <h3>{{ $data['system']['total_quizzes'] ?? 0 }}</h3>
                <p>إجمالي الاختبارات</p>
            </div>
            <div class="stats-box">
                <h3>{{ $data['system']['total_questions'] ?? 0 }}</h3>
                <p>إجمالي الأسئلة</p>
            </div>
        </div>
    </div>
@endif

@if(isset($data['analytics']))
    <div style="margin-bottom: 30px;">
        <h2 style="color: #333; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
            تحليلات النظام
        </h2>
        <table>
            <tr>
                <th style="width: 50%;">المعلومة</th>
                <th>القيمة</th>
            </tr>
            <tr>
                <td>إجمالي الأحداث</td>
                <td>{{ $data['analytics']['total_events'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>المستخدمون المميزون</td>
                <td>{{ $data['analytics']['unique_users'] ?? 0 }}</td>
            </tr>
        </table>
    </div>
@endif

