<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $message->subject ?? 'Notification' }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #edf2f7;
            color: #2d3748;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .email-wrapper {
            width: 100%;
            background-color: #edf2f7;
            padding: 32px 16px;
            box-sizing: border-box;
        }
        .email-container {
            max-width: 570px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 4px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }
        .email-header {
            padding: 25px;
            text-align: center;
            background-color: #ffffff;
        }
        .email-header-title {
            margin: 0;
            font-size: 19px;
            font-weight: bold;
            color: #1a202c;
            text-decoration: none;
        }
        .email-body {
            padding: 35px;
        }
        .email-greeting {
            font-size: 16px;
            font-weight: bold;
            color: #1a202c;
            margin-top: 0;
            margin-bottom: 16px;
        }
        .email-line {
            font-size: 16px;
            color: #718096;
            margin-top: 0;
            margin-bottom: 16px;
        }
        .action-wrapper {
            margin: 30px 0;
            text-align: center;
        }
        .action-button {
            display: inline-block;
            padding: 10px 18px;
            border-radius: 4px;
            font-size: 15px;
            font-weight: bold;
            text-decoration: none;
            color: #ffffff !important;
            background-color: #2d3748;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.16);
        }
        .email-footer {
            padding: 32px;
            font-size: 12px;
            color: #b0b6b0;
            text-align: center;
        }
        .email-footer p {
            color: #b0b6b0;
            margin: 0;
        }
        .subcopy {
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e8e8e8;
            font-size: 12px;
            color: #718096;
            word-break: break-all;
        }
        .subcopy a {
            color: #3182ce;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <span class="email-header-title">{{ config('api-mail.from.name', config('app.name')) }}</span>
            </div>

            <div class="email-body">
                @if(!empty($message->greeting))
                    <h1 class="email-greeting">{{ $message->greeting }}</h1>
                @endif

                @foreach($message->introLines as $line)
                    <p class="email-line">{!! $line !!}</p>
                @endforeach

                @if(!empty($message->actionText) && !empty($message->actionUrl))
                    <div class="action-wrapper">
                        <a href="{{ $message->actionUrl }}" class="action-button" target="_blank" rel="noopener">
                            {{ $message->actionText }}
                        </a>
                    </div>
                @endif

                @foreach($message->outroLines as $line)
                    <p class="email-line">{!! $line !!}</p>
                @endforeach

                @if(!empty($message->salutation))
                    <p class="email-line" style="margin-top: 24px;">{!! $message->salutation !!}</p>
                @else
                    <p class="email-line" style="margin-top: 24px;">Regards,<br>{{ config('api-mail.from.name', config('app.name')) }}</p>
                @endif

                @if(!empty($message->actionText) && !empty($message->actionUrl))
                    <div class="subcopy">
                        If you're having trouble clicking the "{{ $message->actionText }}" button, copy and paste the URL below into your web browser:<br>
                        <a href="{{ $message->actionUrl }}">{{ $message->actionUrl }}</a>
                    </div>
                @endif
            </div>

            <div class="email-footer">
                <p>&copy; {{ date('Y') }} {{ config('api-mail.from.name', config('app.name')) }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
