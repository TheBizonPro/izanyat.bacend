<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Установка
Для запуска проекта необходимо иметь установленный docker, docker-compose, nginx

Конфиг для nginx'a находится в данном репозитории по пути ./deploy/external/nginx/api.izanyat.local

## Запуск контейнеров
Существует несколько окружений - local, development, test и production, для каждого нужен свой .env. 
Для запуска необходимо выполнить следующую команду:

```shell
$ docker-compose -p "ENVIRONMENT" up -d
```

Где ENVIRONMENT это необходимое окружения из списка:

- local: Локальное окружение, разворачивается на компьютере разработчика, используется для локальной разработки 
- development: Дев окружение, внутренний сайт, доступ к которому есть только у команды проекта
- test: Тестовое окружение, общедоступный сайт, используется для тестирования функционала без финансовых и юридических обязательств
- production: Продакшен окружение - боевой сайт

### Остановка контейнеров
Для остановки контейнеров необходимо выполнить следующую команду:

```shell
$ docker-compose -p "ENVIRONMENT" down
```
Где ENVIRONMENT - окружение, в котором запущены контейнеры
