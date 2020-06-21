<?php

class SmsUtils 
{
    const API_USER              = 'deryabinsergey@gmail.com';
    const API_PASSWORD          = '14K2H4J12B';
    const API_SENDER            = 'FinResurs';
    
    const API_TOKEN             = 'X7tvWPbCrdRq6UYSHZqM';
    
    /**
     * url для отправки одного сообщения одному пользователю 
     */
    const API_URL               = 'https://auth.terasms.ru/outbox/send';
    
    /**
     * 
     * @param type $phone
     * @param type $text
     * @param type $text
     * @return integer
     * @throw BaseException
     */
    public static function send($phone, $text, $voice = false)
    {
        if (!defined('__LOCAL_DEBUG__') || __LOCAL_DEBUG__ == false) {

            return
                file_get_contents(
                    self::API_URL, false,
                    stream_context_create(
                        array(
                            'http' => array(
                                'method' => 'POST',
                                'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL,
                                'content' => http_build_query( self::buildParams($phone, $text, $voice) )
                            )
                        ) 
                    )
                );

        } else {
            file_get_contents("https://api.telegram.org/bot366545092:AAHcMTR1zugJYVTh_Vb9TYp4Q4cLIyAdN2Q/sendMessage", false, stream_context_create( array( 'http' => array( 'method' => 'POST', 'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL, 'content' => http_build_query(array('chat_id' => 149734351, 'text' => "Номер: {$phone}\r\n\r\n{$text}")) ) ) ) );
        }
    }
    
    protected static function buildParams($phone, $text, $voice = false)
    {
        $request = array(
            'login' => self::API_USER,
            'target' => $phone,
            'message' => $text,
            'sender' => self::API_SENDER
        );
        
        if ($voice) {
            $request['type'] = 'voice';
        }
        
        ksort($request);
        $s = "";
        foreach($request as $key => $value) {
            $s .= "{$key}={$value}";
        }
        $request['sign'] = md5($s.self::API_TOKEN);
        
        return $request;
    }
}

?>