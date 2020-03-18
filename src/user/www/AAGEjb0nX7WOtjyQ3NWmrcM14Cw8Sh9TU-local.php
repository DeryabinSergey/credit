<?php

require '../../../config.inc.php';

function send($chatId, $text)
{
    return file_get_contents(
        "https://api.telegram.org/bot".TELEGRAM_API."/sendMessage", 
        false, 
        stream_context_create(
            array(
                'http' => array(
                    'method' => 'POST', 
                    'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL, 
                    'content' => http_build_query(array('chat_id' => $chatId, 'text' => $text, 'parse_mode' => 'Markdown'))
                )
            )
        )
    );        
}

$memcacheUserKey = 'TELEGRAM-MEMCACHED-BOT-';

$content = file_get_contents("php://input");
$update = json_decode($content, true);

$userId = is_array($update) && isset($update['message']) && is_array($update['message']) && isset($update['message']['from']) && is_array($update['message']['from']) && isset($update['message']['from']['id']) && $update['message']['from']['id'] ? intval($update['message']['from']['id']) : false;
$text = is_array($update) && isset($update['message']) && is_array($update['message']) && isset($update['message']['text']) && $update['message']['text'] ? trim($update['message']['text']) : false;
$entities = is_array($update) && isset($update['message']) && is_array($update['message']) && isset($update['message']['entities']) && is_array($update['message']['entities']) && $update['message']['entities'] ? $update['message']['entities'] : array();

$botCommand = $botCommandWaiting = $needUpdateStorage = false;
/**
 * Хранилице в Memcache для текущего пользователя. Здесь и данные для сохранения между запросами и ожидаемая комманда.
 */
$botStorage = array();
$user = null;

if ($userId) {
    $memcacheUserKey .= $userId;
    $botStorage = Cache::me()->get($memcacheUserKey) ? Cache::me()->get($memcacheUserKey) : array();
    $botCommandWaiting = is_array($botStorage) && isset($botStorage['command']) && $botStorage['command'] ? $botStorage['command'] : false;
    
    try {
        $user = User::dao()->getByLogic(Expression::eq('telegram_id', $userId));
    } catch (Exception $e) { }
}

//error_log(print_r($botStorage, true));
error_log(print_r($update, true));

if ($userId && $text) {
    
    foreach($entities as $entity) {
        if ($entity['type'] == 'bot_command') {
            $botCommand = mb_substr($text, $entity['offset'], $entity['length']);
            break;
        }
    }

    /**
     * Если пришла и распознана комманда от бота
     */
    if ($botCommand) {
        
        switch ($botCommand) {
            
            case '/start':
                    
                $confirm = null;
                $hash = $text ? trim(mb_substr($text, 7)) : '';
                if ($hash) {
                    try {
                        $confirm = Confirm::dao()->getByLogic(Expression::andBlock(Expression::eq('type_id', ConfirmType::TYPE_TELEGRAM_LINK), Expression::eq('code', $hash)));
                        $confirm->getUser()->dao()->save($confirm->getUser()->setTelegramId($userId)-setTelegramBotEnabled(true));
                        send($userId, "Приветствуем Вас, {$confirm->getUser()->getName()}.\r\nМы будем присылать вам уведомления в Telegram.");
                    } catch(ObjectNotFoundException $e) { }
                }
            
                if (!$confirm instanceof Confirm) {
                    /** Если пользователь был привязан к данной учетной записи **/
                    if ($user instanceof User) {
                        if (!$user->getTelegramBotEnabled()) { 
                            $user->dao()->save($user->setTelegramBotEnabled(true)); 
                            send($userId, "С возвращением, {$user->getName()}.\r\nМы будем снова присылать вам уведомления в Telegram.");
                        } else {
                            send($userId, "Приветствуем Вас, {$user->getName()}.\r\nМы на связи.");
                        }
                    } else {

                        send($userId, "Укажите номер телефона, который указывали при регистрации на сайте.");
                        $botStorage['command'] = 'phone-'.$botCommand;
                        $needUpdateStorage = true;
                    }
                }

                break;
            
            case '/unlink':
                
                if ($user instanceof User) {
                    $user->dao()->save($user->setTelegramId(null)->setTelegramBotEnabled(false));
                    send($userId, "Готово, учетная запись отвязана.");
                }
                
            case '/stop':
                
                if ($user instanceof User && $user->getTelegramBotEnabled()) {
                    $user->dao()->save($user->setTelegramBotEnabled(false)); 
                    send($userId, "{$user->getName()}, мы больше не будем присылать Вам сюда уведомления");
                } 
                
                break;
        }
        
    } elseif ($botCommandWaiting) {
        
        switch ($botCommandWaiting) {
            
            case 'phone-/start':
                
                $phone = substr(preg_replace("/[^\d]/", "", $text), -10);
                $user = null;
                if ($phone && strlen($phone) == 10) {
                    try {
                        $user = User::dao()->getByLogic(Expression::eq('phone', $phone));
                    } catch(ObjectNotFoundException $e) { }
                }
                
                if ($user instanceof User) {
                    /**
                     * смотрим существование кода для этого номера телефона
                     * - если кода нет - создаем и отправляем
                     */
                    try {
                        $now = Timestamp::makeNow()->toStamp();
                        $codeExists = Confirm::dao()->getByLogic(Expression::andBlock(Expression::eq('type_id', ConfirmType::TYPE_TELEGRAM_LINK), Expression::eq('phone', $phone)));
                        if ($codeExists->getTry() < 3) {
                            send($userId, "Напишите код из отправленного Вам ранее сообщения");
                            $botStorage['phone'] = $phone;
                            $botStorage['command'] = 'confirm-/start';
                        } elseif ($codeExists->getExpiredTime()->toStamp() > $now) {
                            $minute = floor(($codeExists->getExpiredTime()->toStamp() - $now) / 60);
                            $second = $codeExists->getExpiredTime()->toStamp() - $now - $minute * 60;
                            send($userId, "Новый код можно будет запросить через {$minute} ".RussianTextUtils::selectCaseForNumber($minute, array('минуту', 'минуты', 'минут'))." {$second} ".RussianTextUtils::selectCaseForNumber($second, array('секунду', 'секунды','секунд')) );
                            $botStorage = array();
                            $needUpdateStorage = true;
                        }
                    } catch(ObjectNotFoundException $e) {
                        
                        $codeExists = 
                            Confirm::dao()->add(
                                Confirm::create()->
                                    setUser($user)->
                                    setType(ConfirmType::create(ConfirmType::TYPE_TELEGRAM_LINK))->
                                    setPhone($phone)->
                                    setCode(random_int(1, 999999))
                            );
                        $botStorage['phone'] = $phone;
                        $botStorage['command'] = 'confirm-/start';
                        $needUpdateStorage = true;
                        SmsUtils::send("7{$phone}", sprintf("Код подтверждения для привязки Telegram: %06d", $codeExists->getCode()));
                        send($userId, "Отправили Вам SMS сообщение с кодом подтверждения.\r\nНапишите код из сообщения.");
                        
                    }
                }
                
                break;
                
            case 'confirm-/start':
                
                $code = substr(preg_replace("/[^\d]/", "", $text), -6);
                $user = null;
                if ($botStorage['phone'] && strlen($botStorage['phone']) == 10) {
                    try {
                        $user = User::dao()->getByLogic(Expression::eq('phone', $botStorage['phone']));
                    } catch(ObjectNotFoundException $e) { }
                }
                
                if ($user instanceof User && $code) {
                    
                    /**
                     * смотрим существование кода для этого номера телефона
                     * - если кода нет - создаем и отправляем
                     */
                    try {
                        $codeExists = Confirm::dao()->getByLogic(Expression::andBlock(Expression::eq('type_id', ConfirmType::TYPE_TELEGRAM_LINK), Expression::eq('phone', $botStorage['phone'])));
                        
                        if (!$codeExists->getUser() instanceof User || $codeExists->getUser()->getId() != $user->getId()) throw new ObjectNotFoundException();
                        
                        if (Timestamp::compare($codeExists->getExpiredTime(), Timestamp::makeNow()) == -1 || $codeExists->getTry() >= 3) throw new ObjectNotFoundException();
                        
                        if ($codeExists->getCode() != $code) {
                            $codeExists->dao()->save($codeExists->setTry($codeExists->getTry() + 1));
                        } else {
                            $user = $user->dao()->save($user->setTelegramId($userId)->setTelegramBotEnabled(true));
                            $codeExists->dao()->dropById($codeExists->getId());
                            send($userId, "Рады приветствовать Вас, {$user->getName()}!\r\nТеперь мы будем присылать Вам cюда важные уведомления.");
                            $botStorage = array();
                            $needUpdateStorage = true;
                        }                        
                    } catch(ObjectNotFoundException $e) {
                        
                        $botStorage = array();
                        $needUpdateStorage = true;
                        
                    }
                    
                } else {
                    $botStorage = array();
                    $needUpdateStorage = true;
                }
                
                
                
                break;
            
        }
        
    }
    
}

if ($needUpdateStorage) { Cache::me()->set($memcacheUserKey, $botStorage, Cache::EXPIRES_MEDIUM); }