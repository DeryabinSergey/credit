#!/usr/bin/php
<?php

$chatId = 854231046;
$text = trim(file_get_contents("php://stdin"));

if ($text) {
    file_get_contents(
        "https://api.telegram.org/bot366545092:AAHcMTR1zugJYVTh_Vb9TYp4Q4cLIyAdN2Q/sendMessage", 
        false, 
        stream_context_create(
            array(
                'http' => array(
                    'method' => 'POST', 
                    'header' => 'Content-Type: application/x-www-form-urlencoded' . PHP_EOL, 
                    'content' => http_build_query(array('chat_id' => $chatId, 'text' => $text))
                )
            )
        )
    );
}