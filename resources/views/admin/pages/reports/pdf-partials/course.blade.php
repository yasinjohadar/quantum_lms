@if(isset($data['subject']))
    <div style="margin-bottom: 30px;">
        <h2 style="color: #333; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
            معلومات الكورس
        </h2>
        <table>
            <tr>
                <th style="width: 30%;">اسم الكورس</th>
                <td>{{ $data['subject']->name }}</td>
            </tr>
            @if($data['subject']->description)
                <tr>
                    <th>الوصف</th>
                    <td>{{ $data['subject']->description }}</td>
                </tr>
            @endif
        </table>
    </div>
@endif

@if(isset($data['statistics']))
    <div style="margin-bottom: 30px;">
        <h2 style="color: #333; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
            الإحصائيات
        </h2>
        <div style="display: flex; flex-wrap: wrap; justify-content: space-around;">
            <div class="stats-box">
                <h3>{{ $data['statistics']['total_students'] ?? 0 }}</h3>
                <p>إجمالي الطلاب</p>
            </div>
            <div class="stats-box">
                <h3>{{ $data['statistics']['total_lessons'] ?? 0 }}</h3>
                <p>إجمالي الدروس</p>
            </div>
            <div class="stats-box">
                <h3>{{ $data['statistics']['total_quizzes'] ?? 0 }}</h3>
                <p>إجمالي الاختبارات</p>
            </div>
        </div>
    </div>
@endif

@if(isset($data['analytics']))
    <div style="margin-bottom: 30px;">
        <h2 style="color: #333; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
            التحليلات
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
                <td>الطلاب المميزون</td>
                <td>{{ $data['analytics']['unique_students'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>دروس تم عرضها</td>
                <td>{{ $data['analytics']['lessons_viewed'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>اختبارات مكتملة</td>
                <td>{{ $data['analytics']['quizzes_completed'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>متوسط المشاركة</td>
                <td>{{ number_format($data['analytics']['average_engagement'] ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>
@endif

