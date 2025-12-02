<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifre Sıfırlama</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">

    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tr>
            <td style="padding: 40px 20px; text-align: center;">
                
                <div style="margin-bottom: 25px;">
                    <img src="https://www.google.com/url?sa=i&url=https%3A%2F%2Ftr.pinterest.com%2Fpin%2F640848221969926126%2F&psig=AOvVaw26I-1ShSZNYbDpDU18vp7L&ust=1764768280455000&source=images&cd=vfe&opi=89978449&ved=0CBIQjRxqFwoTCLDlnaaAn5EDFQAAAAAdAAAAABAE" alt="Logo" style="width: 100px; height: auto;">
                </div>

                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden;">
                    
                    <tr>
                        <td style="height: 6px; background: linear-gradient(90deg, #0099ff 0%, #ff6600 100%);"></td>
                    </tr>

                    <tr>
                        <td style="padding: 40px 30px; text-align: center;">
                            <h1 style="margin: 0 0 20px; color: #1e293b; font-size: 24px; font-weight: 700;">Şifre Sıfırlama İsteği</h1>
                            
                            <p style="margin: 0 0 30px; color: #64748b; font-size: 16px; line-height: 1.6;">
                                Merhaba, <br>
                                Yemekhane Sistemi hesabınız için şifre sıfırlama talebinde bulundunuz. Aşağıdaki doğrulama kodunu kullanarak işleminizi tamamlayabilirsiniz.
                            </p>

                            <div style="background-color: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 12px; padding: 20px; display: inline-block; margin-bottom: 30px;">
                                <span style="font-family: 'Courier New', monospace; font-size: 32px; font-weight: 800; color: #0099ff; letter-spacing: 8px;">
                                    {{ $token }}
                                </span>
                            </div>

                            <p style="margin: 0; color: #94a3b8; font-size: 14px;">
                                Bu kodu siz talep etmediyseniz, hesabınız güvendedir. Bu e-postayı görmezden gelebilirsiniz.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #f8fafc; padding: 20px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; color: #64748b; font-size: 12px;">
                                &copy; {{ date('Y') }} ZMC
                            </p>
                            <p style="margin: 5px 0 0; font-size: 12px;">
                                <a href="https://tr.wikipedia.org/wiki/Anasayfa" style="color: #0099ff; text-decoration: none;">Link bölümü</a>
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>