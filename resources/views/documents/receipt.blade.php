<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"/>
    <title>Оффлайн чек</title>

    <style>
        body {
            margin: 0;
            width: 330px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #3A4350;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        td {
            padding: 0;
        }

        .line {
            border-top: 1px solid #EBEBEB;
        }

        .accent {
            font-weight: 600;
        }

        .muted {
            color: #757575;
        }

        .body {
            padding: 30px;
        }

        .title {
            font-weight: bold;
            font-size: 20px;
            line-height: 1.2;
            padding-bottom: 10px;
        }

        .date {
            padding-right: 8px
        }

        .main-date {
            padding-bottom: 15px;
        }

        .user {
            font-size: 16px;
            line-height: 1.4;
            padding-bottom: 10px;
        }

        .about-item {
            padding-top: 4px;
            padding-bottom: 4px;
            line-height: 1.2;
        }

        .sections-start {
            padding-top: 15px;
            padding-bottom: 18px;
        }

        .section-line {
            margin-top: 18px;
            padding-bottom: 18px;
        }

        .services-header {
            font-weight: 700;
        }

        .services-item-td {
            padding-top: 12px;
            vertical-align: top;
        }

        .services-item-td-value {
            text-align: right;
        }

        .price {
            white-space: nowrap;
        }

        .services-item-name-wrapper {
            padding-right: 4px;
        }

        .services-item-name-td {
            vertical-align: top;
            padding-right: 5px;
        }

        .services-item-name-td-title {
            word-wrap: break-word;
        }

        .total {
            font-weight: 700;
            font-size: 16px;
        }

        .total-price {
            text-align: right;
            padding-left: 5px;
        }

        .customer-title {
            font-weight: 700;
            padding-bottom: 3px;
        }

        .customer-inn {
            white-space: nowrap;
        }

        .qr-container {
            text-align: center;
        }

    </style>
</head>
<body>
<table>
    <tbody>
    <tr>
        <td class="body">
            <table>
                <tbody>
                <tr>
                    <td>
                        <div class="title">
                            Чек №{{ $payout->receipt_id }}
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="main-date">
                        <table>
                            <tbody>
                            <tr class="muted">
                                <td class="date">{{ $payout->getCreatedDateAttribute() }}</td>
                                <td align="right">{{ $payout->getCreatedTime() }}({{ $payout->getCreatedTimeZone() }})</td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td class="user">
                        <div>{{ mb_strtoupper($payout->user->getFullNameAttribute()) }}</div>
                    </td>
                </tr>

                <tr>
                    <td class="sections-start">
                        <div class="line"></div>
                    </td>
                </tr>

                <tr>
                    <td class="section">
                        <table>
                            <tbody>
                            <tr class="services-header">
                                <td>Наименование</td>
                                <td class="services-item-td-value">Сумма</td>
                            </tr>
                            <tr>
                                <td class="services-item-td services-item-name-wrapper">
                                    <table>
                                        <tr>
                                            <td class="services-item-name-td">1.</td>
                                            <td class="services-item-name-td services-item-name-td-title">
                                                {{ $payout->task->name }}
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                                <td class="services-item-td services-item-td-value price">{{ $payout->sum }} ₽</td>
                            </tr>
                            </tbody>
                        </table>

                        <div class="section-line line"></div>
                    </td>
                </tr>
                <tr>
                    <td class="section">
                        <table class="total">
                            <tr>
                                <td>
                                    Итого:
                                </td>
                                <td class="price total-price">
                                    {{ $payout->sum }} ₽
                                </td>
                            </tr>
                        </table>
                        <div class="section-line line"></div>
                    </td>
                </tr>
                <tr>
                    <td class="section">
                        <table class="about-item">
                            <tr>
                                <td class="about-item">Режим НО:</td>
                                <td align="right" class="about-item">
                                    НПД
                                </td>
                            </tr>
                            <tr>
                                <td class="about-item">ИНН:</td>
                                <td align="right" class="about-item">
                                    {{ $payout->user->inn }}
                                </td>
                            </tr>
                        </table>
                        <div class="section-line line"></div>
                    </td>
                </tr>
                <tr>
                    <td class="section">
                        <table class="about-item muted">
                            <tr>
                                <td class="about-item muted">Чек сформировал:</td>
                                <td align="right" class="about-item muted">
                                    {{ config('npd.partner_name') }}
                                </td>
                            </tr>
                            <tr>
                                <td class="about-item muted">ИНН:</td>
                                <td align="right" class="about-item muted">
                                    {{ config('npd.inn') }}
                                </td>
                            </tr>
                        </table>
                        <div class="section-line line"></div>
                    </td>
                </tr>
                <tr>
                    <td class="section">
                        <div class="customer-title">
                            Покупатель:
                        </div>
                        <div>
                            ИНН: {{ $payout->project->company->inn }}
                        </div>
                        <div class="section-line line"></div>
                    </td>
                </tr>
                <tr>
                    <td class="qr-container">
                        <img align="center" height="150"
                             src="{{ $qr_data }}"/>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
