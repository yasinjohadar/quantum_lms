<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>شهادة إكمال كورس</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', 'Tahoma', sans-serif; background: #f5f5f5; padding: 20px; }
        .certificate { width: 100%; max-width: 1200px; margin: 0 auto; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 60px; text-align: center; box-shadow: 0 0 30px rgba(0,0,0,0.1); color: #fff; }
        .title { font-size: 56px; margin-bottom: 30px; font-weight: bold; }
        .subtitle { font-size: 28px; margin-bottom: 50px; opacity: 0.9; }
        .text { font-size: 22px; margin: 25px 0; line-height: 1.8; }
        .name { font-size: 42px; margin: 40px 0; font-weight: bold; text-decoration: underline; }
        .subject { font-size: 32px; margin: 30px 0; font-weight: bold; }
        .date { font-size: 18px; margin-top: 50px; opacity: 0.9; }
        .number { font-size: 16px; margin-top: 30px; opacity: 0.8; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="title">شهادة إكمال كورس</div>
        <div class="subtitle">Course Completion Certificate</div>
        <div class="text">هذه الشهادة تثبت أن</div>
        <div class="name">{{ $certificate->user->name ?? 'USER_NAME' }}</div>
        <div class="text">قد أكمل بنجاح كورس</div>
        @if($certificate->subject)
        <div class="subject">{{ $certificate->subject->name }}</div>
        @endif
        <div class="date">تاريخ الإصدار: {{ $certificate->issued_at->format('Y-m-d') }}</div>
        <div class="number">رقم الشهادة: {{ $certificate->certificate_number }}</div>
    </div>
</body>
</html>

