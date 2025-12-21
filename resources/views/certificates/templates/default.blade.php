<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>شهادة إكمال</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', 'Tahoma', sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .certificate {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            background: #fff;
            border: 15px solid #007bff;
            padding: 60px;
            text-align: center;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
        }
        .title {
            font-size: 56px;
            color: #007bff;
            margin-bottom: 30px;
            font-weight: bold;
        }
        .subtitle {
            font-size: 28px;
            color: #666;
            margin-bottom: 50px;
        }
        .text {
            font-size: 22px;
            color: #333;
            margin: 25px 0;
            line-height: 1.8;
        }
        .name {
            font-size: 42px;
            color: #000;
            margin: 40px 0;
            font-weight: bold;
            text-decoration: underline;
            text-decoration-color: #007bff;
        }
        .subject {
            font-size: 32px;
            color: #007bff;
            margin: 30px 0;
            font-weight: bold;
        }
        .date {
            font-size: 18px;
            color: #666;
            margin-top: 50px;
        }
        .number {
            font-size: 16px;
            color: #999;
            margin-top: 30px;
        }
        .footer {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 2px solid #ddd;
            display: flex;
            justify-content: space-around;
        }
        .signature {
            text-align: center;
        }
        .signature-name {
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="title">شهادة إكمال</div>
        <div class="subtitle">Certificate of Completion</div>
        <div class="text">هذه الشهادة تثبت أن</div>
        <div class="name">{{ $certificate->user->name ?? 'USER_NAME' }}</div>
        <div class="text">قد أكمل بنجاح</div>
        @if($certificate->subject)
        <div class="subject">{{ $certificate->subject->name }}</div>
        @endif
        <div class="date">تاريخ الإصدار: {{ $certificate->issued_at->format('Y-m-d') }}</div>
        <div class="number">رقم الشهادة: {{ $certificate->certificate_number }}</div>
        <div class="footer">
            <div class="signature">
                <div>___________________</div>
                <div class="signature-name">المدير</div>
            </div>
            <div class="signature">
                <div>___________________</div>
                <div class="signature-name">الختم</div>
            </div>
        </div>
    </div>
</body>
</html>

