<?php

$access_token = "FacebookAPIKey";
$json_string = file_get_contents('php://input');
$json_object = json_decode($json_string);
$messaging = $json_object->entry{0}->messaging{0};

define('API_KEY', 'DocomoAPIKey');
require_once(__DIR__ . '/vendor/autoload.php');
use jp3cki\docomoDialogue\Dialogue;
$dialog = new Dialogue(API_KEY);

// ToDo::会話を継続するためのコンテキストIDとモード。一般公開時までに実装する
// $context = null;
// $mode = null;

if(isset($messaging->message)) {
    $text = $json_object->entry{0}->messaging{0}->message->text;
    $id = $messaging->sender->id;
    //Docomo
        // 送信パラメータの準備
        $dialog->parameter->reset();
        $dialog->parameter->utt = $text;
        $dialog->parameter->context = $context;
        $dialog->parameter->mode = $mode;

        $ret = $dialog->request();

        if($ret === false) {
            $text = "通信に失敗しました";
        }
        $text =$ret->utt;

    //Facebook
    $post = <<< EOM
    {
        "recipient":{
            "id":"{$id}"
        },
        "message":{
            "text":"{$text}"
        }
    }
EOM;
    
    
    //ToDo::前後の会話履歴をユーザー毎に管理するためユーザ−IDなどをMySQLに入れる
    // $id = $messaging->sender->id;
    // $context = $ret->context;
    // $mode = $ret->mode;
    
    api_send_request($access_token, $post);
}

function api_send_request($access_token, $post) {
    error_log("api_get_message_content_request start");
    $url = "https://graph.facebook.com/v2.6/me/messages?access_token={$access_token}";
    $headers = array(
            "Content-Type: application/json"
    );

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    $output = curl_exec($curl);
}
