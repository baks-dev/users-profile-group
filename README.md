# BaksDev Profile Group

[![Version](https://img.shields.io/badge/version-7.1.10-blue)](https://github.com/baks-dev/users-profile-group/releases)
![php 8.3+](https://img.shields.io/badge/php-min%208.3-red.svg)

Модуль групп профилей пользователя

## Установка

``` bash
$ composer require baks-dev/users-profile-group
```

## После первичной установки

Добавляем Администратора ресурса

``` bash
php bin/console baks:users-profile-type:user
php bin/console baks:auth-email:admin
php bin/console baks:users-profile-user:admin
php bin/console baks:users-profile-group:admin
```

## Дополнительно

Установка конфигурации и файловых ресурсов:

``` bash
php bin/console baks:assets:install
```

Рекомендуется в composer.json проекта добавить в секцию автоматическое выполнение

``` json
"scripts": {
    "auto-scripts": {
        "baks:assets:install": "symfony-cmd",
        "baks:cache:clear": "symfony-cmd"
    },
    "post-install-cmd": [
        "@auto-scripts"
    ],
    "post-update-cmd": [
        "@auto-scripts"
    ]
}
```

Изменения в схеме базы данных с помощью миграции

``` bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

## Тестирование

``` bash
php bin/phpunit --group=users-profile-group
```

## Лицензия ![License](https://img.shields.io/badge/MIT-green)

The MIT License (MIT). Обратитесь к [Файлу лицензии](LICENSE.md) за дополнительной информацией.

