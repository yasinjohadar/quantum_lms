<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إعادة تعيين كلمة المرور</title>
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; width: 100%; border-collapse: collapse; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td align="center" style="padding: 40px 30px 30px; background-color: #ffffff; border-radius: 8px 8px 0 0;">
                            <h1 style="margin: 0; font-size: 32px; font-weight: 700; color: #1f2937; text-align: center;">
                                {{ $academyName }}
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px; background-color: #ffffff;">
                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #374151;">
                                مرحباً!
                            </p>
                            
                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 1.6; color: #374151;">
                                أنت تتلقى هذا الإيميل لأننا تلقينا طلب إعادة تعيين كلمة المرور لحسابك.
                            </p>
                            
                            <!-- Button -->
                            <table role="presentation" style="width: 100%; margin: 30px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $url }}" style="display: inline-block; padding: 14px 32px; background-color: #4f46e5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 16px;">
                                            إعادة تعيين كلمة المرور
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 20px 0; font-size: 14px; line-height: 1.6; color: #6b7280;">
                                سينتهي صلاحية رابط إعادة تعيين كلمة المرور خلال {{ $count }} دقيقة.
                            </p>
                            
                            <p style="margin: 20px 0 0; font-size: 16px; line-height: 1.6; color: #374151;">
                                إذا لم تطلب إعادة تعيين كلمة المرور، لا حاجة لاتخاذ أي إجراء آخر.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px; background-color: #f9fafb; border-radius: 0 0 8px 8px; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0 0 10px; font-size: 14px; line-height: 1.6; color: #374151;">
                                مع التحية،<br>
                                <strong>{{ $academyName }}</strong>
                            </p>
                            
                            <!-- Subcopy -->
                            <p style="margin: 30px 0 0; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 12px; line-height: 1.6; color: #6b7280;">
                                إذا واجهت مشكلة في النقر على زر "إعادة تعيين كلمة المرور"، انسخ والصق الرابط أدناه في متصفح الويب الخاص بك:<br><br>
                                <a href="{{ $url }}" style="color: #4f46e5; word-break: break-all;">{{ $url }}</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>



