<?php
$output = json_decode(file_get_contents('php://input'),true);
$id = $output['message']['chat']['id'];
$token='469123782:AAHOpN4Fqow0wNjPYTW3wIke37V5JTwp9iI';
$message= $output['message']['text'];

switch ($message){

    case 'Привет':
        $message='Привет!';
        sendMessage($token,$id,$message.KeyboardMenu());
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
    $buttons = [['Привет'],['как дела?'],['справка'],['123']];
    $keyboard =json_encode($keyboard=['keyboard' => $buttons,
                                        'resize_keyboard' => true,
                                        'one_time_keyboard'=> false,
                                        'selective' => true]);
    $reply_markup ='&reply_markup='.$keyboard.'';
    return $reply_markup;

}
