<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $template->name }}</title>
    <style>
        @page {
            margin: 20mm;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            direction: rtl;
            font-size: 12px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .content {
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 11px;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: right;
        }
        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .stats-box {
            display: inline-block;
            margin: 10px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-align: center;
            min-width: 150px;
        }
        .stats-box h3 {
            margin: 0;
            font-size: 24px;
            color: #007bff;
        }
        .stats-box p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 12px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $template->name }}</h1>
        <p>تاريخ التقرير: {{ now()->format('Y-m-d H:i') }}</p>
        @if($template->description)
            <p>{{ $template->description }}</p>
        @endif
    </div>
    
    <div class="content">
        @if($template->type == 'student')
            @include('admin.pages.reports.pdf-partials.student', ['data' => $data])
        @elseif($template->type == 'course')
            @include('admin.pages.reports.pdf-partials.course', ['data' => $data])
        @else
            @include('admin.pages.reports.pdf-partials.system', ['data' => $data])
        @endif
    </div>
    
    <div class="footer">
        <p>تم إنشاء هذا التقرير تلقائياً من نظام Quantum LMS</p>
        <p>الصفحة {PAGENO} من {PAGETOTAL}</p>
    </div>
</body>
</html>

