@if(isset($data['student']))
    <div style="margin-bottom: 30px;">
        <h2 style="color: #333; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
            معلومات الطالب
        </h2>
        <table>
            <tr>
                <th style="width: 30%;">الاسم</th>
                <td>{{ $data['student']->name }}</td>
            </tr>
            <tr>
                <th>البريد الإلكتروني</th>
                <td>{{ $data['student']->email }}</td>
            </tr>
            @if($data['student']->phone)
                <tr>
                    <th>الهاتف</th>
                    <td>{{ $data['student']->phone }}</td>
                </tr>
            @endif
        </table>
    </div>
@endif

@if(isset($data['progress']) && count($data['progress']) > 0)
    <div style="margin-bottom: 30px;">
        <h2 style="color: #333; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
            التقدم في الكورسات
        </h2>
        <table>
            <thead>
                <tr>
                    <th>الكورس</th>
                    <th>التقدم الإجمالي</th>
                    <th>الدروس</th>
                    <th>الاختبارات</th>
                    <th>الأسئلة</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['progress'] as $item)
                    @php
                        $progress = $item['progress'];
                    @endphp
                    <tr>
                        <td>{{ $item['subject']->name }}</td>
                        <td>{{ number_format($progress['overall_percentage'], 1) }}%</td>
                        <td>{{ $progress['lessons_completed'] }}/{{ $progress['lessons_total'] }}</td>
                        <td>{{ $progress['quizzes_completed'] }}/{{ $progress['quizzes_total'] }}</td>
                        <td>{{ $progress['questions_completed'] }}/{{ $progress['questions_total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

@if(isset($data['analytics']))
    <div style="margin-bottom: 30px;">
        <h2 style="color: #333; border-bottom: 1px solid #ddd; padding-bottom: 10px;">
            التحليلات
        </h2>
        <div style="display: flex; flex-wrap: wrap; justify-content: space-around;">
            <div class="stats-box">
                <h3>{{ $data['analytics']['total_events'] ?? 0 }}</h3>
                <p>إجمالي الأحداث</p>
            </div>
            <div class="stats-box">
                <h3>{{ $data['analytics']['lessons_viewed'] ?? 0 }}</h3>
                <p>دروس تم عرضها</p>
            </div>
            <div class="stats-box">
                <h3>{{ $data['analytics']['quizzes_completed'] ?? 0 }}</h3>
                <p>اختبارات مكتملة</p>
            </div>
        </div>
    </div>
@endif

