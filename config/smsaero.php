<?php 

return [
  'login'    => env('SMSAERO_LOGIN'),    // Логин на http://smsaero.ru/
  'password' => env('SMSAERO_PASSWORD'), // Пароль на http://smsaero.ru/
  'sign'     => env('SMSAERO_SIGN'),     // Подпись по умолчанию
  'url'      => env('SMSAERO_GATE')      // Шлюз
];