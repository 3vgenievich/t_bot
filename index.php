<?php
$output = json_decode(file_get_contents('php://input'),true);
$id = $output['message']['chat']['id'];
$token='469123782:AAHOpN4Fqow0wNjPYTW3wIke37V5JTwp9iI';
$message= $output['message']['text'];

switch ($message){

    case '/start':
        $message='hello!';
        sendMessage($token,$id,$message.KeyboardMenu());
        break;
    case 'how are you':
        $message='namana';
        sendMessage($token,$id,$message.KeyboardMenu());
        break;
    case 'Где я?':
        sendMessage($token,$id,$message.KeyboardMenu());

    default:
        $message='что?';
        sendMessage($token,$id,$message);
}
///sendMessage($token,$id,$message);
function sendMessage($token, $id,$message)
{
file_get_contents("https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $id . "&text=".$message);
}
file_put_contents("logs.txt",$id);
//тест клавиатуры
function KeyboardMenu(){
    $buttons = [['Где я?'],['how are you'],['two'],['three']];
    $keyboard =json_encode($keyboard=['keyboard' => $buttons,
                                        'resize_keyboard' => true,
                                        'one_time_keyboard'=> false,
                                        'selective' => true,
                                        'request_location'=> true]);
    $reply_markup ='&reply_markup='.$keyboard.'';
    return $reply_markup;

}