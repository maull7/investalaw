<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Konsultasi Kak Vesta — {{ $session->title }}</title>
    <style>
        * { box-sizing: border-box; }
        @page { margin: 20mm 18mm; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #071833;
            line-height: 1.55;
            margin: 0;
        }
        .header {
            border-bottom: 2px solid #c99a3e;
            padding-bottom: 14px;
            margin-bottom: 22px;
        }
        .brand {
            display: inline-block;
            vertical-align: middle;
        }
        .brand .mark {
            display: inline-block;
            width: 28px;
            height: 28px;
            background: #c99a3e;
            color: #071b3a;
            text-align: center;
            font-weight: 800;
            font-size: 16px;
            line-height: 28px;
            border-radius: 6px;
            vertical-align: middle;
            margin-right: 8px;
        }
        .brand .name {
            display: inline-block;
            font-size: 14px;
            font-weight: 700;
            color: #071b3a;
            vertical-align: middle;
        }
        .brand .tag {
            display: block;
            font-size: 8px;
            font-weight: 600;
            color: #c99a3e;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-left: 36px;
            margin-top: -2px;
        }
        .doc-meta {
            float: right;
            text-align: right;
            font-size: 9.5px;
            color: #667085;
        }
        .doc-meta strong {
            color: #071833;
        }
        .clearfix { clear: both; }

        .session-title {
            font-size: 18px;
            font-weight: 700;
            color: #071833;
            margin: 0 0 8px;
        }
        .session-info {
            font-size: 10px;
            color: #667085;
            margin: 0 0 16px;
        }

        .regulations-section {
            background: #f6f8fb;
            border: 1px solid #e7eaf0;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        .regulations-title {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #c99a3e;
            margin: 0 0 8px;
        }
        .regulations-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .regulations-list li {
            font-size: 10px;
            color: #071833;
            padding: 3px 0;
            border-bottom: 1px solid #e7eaf0;
        }
        .regulations-list li:last-child {
            border-bottom: none;
        }

        .messages-container {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .message {
            padding: 12px 16px;
            border-radius: 12px;
            max-width: 90%;
        }
        .message.user {
            background: linear-gradient(135deg, #071b3a 0%, #0b2a55 100%);
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }
        .message.assistant {
            background: #f6f8fb;
            border: 1px solid #e7eaf0;
            color: #071833;
            border-bottom-left-radius: 4px;
        }
        .message-role {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }
        .message.user .message-role {
            color: #c99a3e;
        }
        .message.assistant .message-role {
            color: #c99a3e;
        }
        .message-content {
            font-size: 11px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .message-time {
            font-size: 8px;
            color: #667085;
            margin-top: 6px;
            text-align: right;
        }
        .message.user .message-time {
            color: rgba(255,255,255,0.6);
        }

        .watermark {
            position: fixed;
            bottom: 10mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #667085;
            letter-spacing: 1px;
        }
        .watermark span {
            color: #c99a3e;
            font-weight: 600;
        }

        @page {
            margin-footer: 15mm;
            margin-header: 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">
            <span class="mark">I</span>
            <span class="name">InvestaLawCo</span>
            <span class="tag">Legal · Strategic · Trusted</span>
        </div>
        <div class="doc-meta">
            <strong>Konsultasi Kak Vesta</strong><br>
            {{ $session->title }}<br>
            @if(isset($user))
                {{ $user->name }}
            @endif
        </div>
        <div class="clearfix"></div>
    </div>

    @if(!empty($regulationsList))
    <div class="regulations-section">
        <p class="regulations-title">Regulasi yang Dianalisis</p>
        <ul class="regulations-list">
            @foreach($regulationsList as $reg)
                <li>{{ $reg }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="messages-container">
        @foreach($messagesData as $msg)
            <div class="message {{ $msg['role'] }}">
                <div class="message-role">{{ $msg['role'] === 'user' ? 'Anda' : 'Kak Vesta' }}</div>
                <div class="message-content">{{ $msg['content'] }}</div>
                <div class="message-time">{{ $msg['created_at'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="watermark">
        <span>InvestaLawCo</span> — Legal · Strategic · Trusted
    </div>
</body>
</html>
