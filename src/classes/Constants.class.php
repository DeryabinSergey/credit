<?php

class Constants
{
    /**
     * Премодерация предложений инвесторов
     */
    const INVESTOR_PREMODERATION        = true;
    
    /**
     * Премодерация компаний кредиторов
     */
    const CREDITOR_PREMODERATION         = true;
    
    /*********************************************
     * 
     * Авторизация
     * 
     *********************************************/
    
    /**
     * Кол-во неудачных попыток с одного IP не зависимо от пользователя
     */
    const AUTH_IP_TRY       = 5;
    /**
     * Кол-во часов, за которые считать неудачные попытки авторизации с одного IP
     */
    const AUTH_IP_PERIOD    = 6;
    /**
     * Кол-во минут задержки при срабатывании защиты
     */
    const AUTH_DELAY = 5;
    /**
     * Количество неудачных предыдущих попыток с учетом пользователя
     */
    const AUTH_USER_TRY = 10;
    /**
     * Кол-во часов, за сколько считать неудачные попытки авторизации от пользователя
     */
    const AUTH_USER_PERIOD = 6;
    
    /**
     * Логировать каждое действие (т.е. записывать в БД время последнего действия (инициализации) пользователя)
     */
    const USER_LOG_ACTION = true;
    /**
     * Глубина просмотра логов при авторизации по кукам/сессии
     */
    const USER_AUTH_LOG_DEEP = 3;
    /**
     * Время жизни куки авторизации пользователя: 2 * 365 * 24 * 3600
     */
    const COOKIE_LIFETIME = 63072000;
    
    /*********************************************
     * 
     * Изображения и привью
     * 
     *********************************************/
    
    // Размеры фотографий для запросов кредита
    const CREDIT_REQUEST_IMAGE_THUMB = 200;
    const CREDIT_REQUEST_IMAGE = 1800;    
    
    /**
     * Время жизни запроса кредита к кредитной организации в статусе Поступило
     */
    const CREDIT_REQUEST_CREDITOR_INCOME_LIFETIME = 18;
    
    /**
     * Открытый и закрытый токен для запросов к стандартизации и подсказкам DaData
     * @see https://dadata.ru/profile/#info
     */
    const DADATA_TOKEN      = '8e8269bebbb4d54cb70277e9fd2ea7955bc765b8';
    const DADATA_SECRET     = '086048138d8e2c8e44d5fefe32538eef3031691e';
}