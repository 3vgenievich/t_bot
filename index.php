<?php
$output = json_decode(file_get_contents('php://input'),true);
$id = $output['message']['chat']['id'];
$token='469123782:AAHOpN4Fqow0wNjPYTW3wIke37V5JTwp9iI';
$message= $output['message']['text'];
$location = new SendLocation();
switch ($message){

    case '/start':
        $message='Привет';
        sendMessage($token,$id,,$message.KeyboardMenu());
        break;
    case 'Где я?':
        $message='Ваше местоположение:';
        sendMessage($token,$id,$location,$message.KeyboardMenu());
        break;
    case 'как дела?':
        $message='отлично';
        sendMessage($token,$id,$message.KeyboardMenu());
        break;
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
    $keyboard =json_encode($keyboard=['keyboard' =>$buttons,
                                        'resize_keyboard' => true,
                                        'one_time_keyboard'=> false,
                                        'selective' => true,
                                        'request_location'=> true]);
    $reply_markup ='&reply_markup='.$keyboard.'';
    return $reply_markup;
