<?php

return [
    'partner_name'     => env('NPD_PARTNER_NAME'), // Название платформы
    'description'      => env('NPD_DESCRIPTION'), // Описание
    'transition_link'  => env('NPD_TRANSITION_LINK'), // Ссылка
    'text'             => env('NPD_TEXT'), // Текст о платформе
    'inn'              => env('NPD_INN'), // ИНН партнера
    'phone'            => env('NPD_PHONE'), // Телефон
    'picture_path'     => env('NPD_PICTURE_PATH'), // Путь к изображению
    'partner_type'     => env('NPD_PARTNER_TYPE'), // Тип платформы
    'source_device_id' => env('NPD_SOURCE_DEVICE_ID'), // ID мобильного приложения
    'income_type'      => env('NPD_INCOME_TYPE'), // Тип дохода
    'ktir_type'        => env('NPD_KTIR_TYPE'), // Тип тестового контура
    'ktir_url'         => env('NPD_KTIR_2_URL'), // URL тестового контура
    'auth_url'         => env('NPD_KTIR_2_URL') . env('NPD_SOAP_AUTH_URL'), // URL сервиса авторизации
];
