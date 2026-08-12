<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aktivasi Akun {{ config('app.name') }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f2ec; font-family:'Segoe UI',Arial,Helvetica,sans-serif; -webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f2ec; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%;">
                    {{-- Header --}}
                    <tr>
                        <td align="center" style="padding:0 0 24px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center">
                                        <span style="font-size:26px; font-weight:800; color:#071b3a; letter-spacing:-0.5px;">InvestaLaw<span style="color:#c99a3e;">Co</span></span>
                                        <p style="margin:4px 0 0; font-size:11px; letter-spacing:2.5px; color:#667085; text-transform:uppercase;">Legal · Strategic · Trusted</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td style="background:#ffffff; border-radius:18px; border:1px solid #e7eaf0; box-shadow:0 8px 26px rgba(7,27,58,.06); overflow:hidden;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                {{-- Accent bar --}}
                                <tr>
                                    <td style="background:linear-gradient(135deg,#071b3a,#0b2a55); height:8px; font-size:0; line-height:0;">&#8203;</td>
                                </tr>
                                <tr>
                                    <td style="padding:36px 40px;">
                                        <p style="margin:0 0 6px; font-size:11px; font-weight:700; letter-spacing:2.5px; text-transform:uppercase; color:#c99a3e;">Aktivasi Akun</p>
                                        <h1 style="margin:0 0 12px; font-size:24px; font-weight:800; color:#071b3a; letter-spacing:-0.3px;">Halo, {{ $user->name }}!</h1>
                                        <p style="margin:0 0 24px; font-size:15px; line-height:1.6; color:#667085;">
                                            Terima kasih telah mendaftar di {{ config('app.name') }}. Untuk mengaktifkan akun Anda, klik tombol di bawah ini:
                                        </p>

                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
                                            <tr>
                                                <td align="center">
                                                    <a href="{{ $url }}" style="display:inline-block; padding:14px 32px; border-radius:12px; background:linear-gradient(135deg,#c99a3e,#e6c06a); color:#ffffff; font-size:15px; font-weight:800; text-decoration:none; box-shadow:0 8px 20px rgba(201,154,62,.35);">Aktivasi Akun Sekarang</a>
                                                </td>
                                            </tr>
                                        </table>

                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#faf7ef; border:1px solid #e7e0cd; border-radius:12px; margin:0 0 24px;">
                                            <tr>
                                                <td style="padding:16px 20px; font-size:13px; line-height:1.6; color:#667085;">
                                                    Jika tombol di atas tidak berfungsi, salin dan tempel tautan ini di browser Anda:<br>
                                                    <span style="color:#8c6a25; word-break:break-all;">{{ $url }}</span>
                                                </td>
                                            </tr>
                                        </table>

                                        <p style="margin:0 0 24px; font-size:13px; line-height:1.6; color:#667085;">
                                            Link aktivasi berlaku selama 60 menit. Jika Anda tidak mendaftar di {{ config('app.name') }}, abaikan email ini.
                                        </p>

                                        <p style="margin:0; font-size:13px; color:#667085;">Salam hangat,<br><strong style="color:#071b3a;">Tim {{ config('app.name') }}</strong></p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding:20px 0 0;">
                            <p style="margin:0 0 4px; font-size:12px; color:#667085;">© {{ date('Y') }} {{ config('app.name') }}. Semua hak dilindungi.</p>
                            <p style="margin:0; font-size:12px; color:#667085;">Konsultan Hukum Investasi &amp; Pasar Modal</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>