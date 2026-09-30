<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <title>رسالة تواصل جديدة</title>
</head>
<body style="font-family: Arial, sans-serif; direction: rtl; text-align: right; background: #f5f5f5; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2 style="color: #2563eb; margin-bottom: 20px;">رسالة تواصل جديدة</h2>
        
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
            <tr>
                <td style="padding: 8px 12px; background: #f8f9fa; font-weight: bold; width: 30%;">الاسم:</td>
                <td style="padding: 8px 12px;">{{ $contactMessage->name }}</td>
            </tr>
            <tr>
                <td style="padding: 8px 12px; background: #f8f9fa; font-weight: bold;">البريد:</td>
                <td style="padding: 8px 12px;">{{ $contactMessage->email }}</td>
            </tr>
            @if($contactMessage->phone)
            <tr>
                <td style="padding: 8px 12px; background: #f8f9fa; font-weight: bold;">الهاتف:</td>
                <td style="padding: 8px 12px;">{{ $contactMessage->phone }}</td>
            </tr>
            @endif
            @if($contactMessage->subject)
            <tr>
                <td style="padding: 8px 12px; background: #f8f9fa; font-weight: bold;">الموضوع:</td>
                <td style="padding: 8px 12px;">{{ $contactMessage->subject }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 8px 12px; background: #f8f9fa; font-weight: bold;">التاريخ:</td>
                <td style="padding: 8px 12px;">{{ $contactMessage->created_at->format('Y-m-d H:i') }}</td>
            </tr>
        </table>

        <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-right: 4px solid #2563eb;">
            <h4 style="margin-top: 0; color: #333;">الرسالة:</h4>
            <p style="white-space: pre-wrap; line-height: 1.6;">{{ $contactMessage->message }}</p>
        </div>

        <p style="margin-top: 20px; color: #666; font-size: 14px;">
            تم استلام هذه الرسالة من خلال نموذج "تواصل معنا" في تطبيق ماجاديت.
        </p>
    </div>
</body>
</html>
