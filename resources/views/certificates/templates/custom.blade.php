<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>شهادة</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', 'Tahoma', sans-serif; background: #f5f5f5; padding: 20px; }
        .certificate { width: 100%; max-width: 1200px; margin: 0 auto; background: #fff; border: 15px solid #007bff; padding: 60px; text-align: center; box-shadow: 0 0 30px rgba(0,0,0,0.1); }
        .title { font-size: 56px; color: #007bff; margin-bottom: 30px; font-weight: bold; }
        .subtitle { font-size: 28px; color: #666; margin-bottom: 50px; }
        .text { font-size: 22px; color: #333; margin: 25px 0; line-height: 1.8; }
        .name { font-size: 42px; color: #000; margin: 40px 0; font-weight: bold; text-decoration: underline; text-decoration-color: #007bff; }
        .subject { font-size: 32px; color: #007bff; margin: 30px 0; font-weight: bold; }
        .date { font-size: 18px; color: #666; margin-top: 50px; }
        .number { font-size: 16px; color: #999; margin-top: 30px; }
    </style>
</head>
<body>
    <div class="certificate">
        {!! $template->template_html ?? '' !!}
    </div>
</body>
</html>

