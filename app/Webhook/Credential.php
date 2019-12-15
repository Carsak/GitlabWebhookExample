<?php

namespace App\Webhook;


/**
 * Учетный данные от Redmine
 * тестовый Redmine http://www.hostedredmine.com/
 * Данные храняться в Environment variables. Не нужно хранить пароли в Git
 * Также, их проще менять через веб интерфейс Heroku
 */
class Credential
{
    /**
     * Логин для подключения
     * @return string
     */
    public function getUserName(): string
    {
        return getenv('REDMINE_USERNAME');
    }

    /**
     * Пароль для подключения
     * @return string
     */
    public function getPassword(): string
    {
        return getenv('REDMINE_PASSWORD');
    }

    /**
     * Лучше использовать АПИ ключ
     * @return string
     */
    public function getBotApiKey(): string
    {
        return getenv('REDMINE_BOT_API_KEY');
    }

    public function getRedmineHost()
    {
        return 'http://www.hostedredmine.com/';
    }
}

