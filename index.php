<?php
$output = json_decode(file_get_contents('php://input'),true);
$id = $output['message']['chat']['id'];
$token='469123782:AAHOpN4Fqow0wNjPYTW3wIke37V5JTwp9iI';
$message= $output['message']['text'];

switch ($message){

    case '/start':
        $message='Привет!';
        sendMessage($token,$id,$message);
        break;
    case 'inlineKeyboard':
        $message='норм';
        sendMessage($token,$id,$message.inlineKeyboard());
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

function KeyboardMenu(){
    $buttons = [['hi'],['how are you'],['two'],['three']];
    $keyboard =json_encode($keyboard=['keyboard' => $buttons,
                                        'resize_keyboard' => true,
                                        'one_time_keyboard'=> false,
                                        'selective' => true]);
    $reply_markup ='&reply_markup='.$keyboard.'';
    return $reply_markup;

}
function inlineKeyboard(){

    $reply_markup = '';

    $button1= array('text'=>'Меню','callback_data' =>'inline_one');
    $button2=array('text'=>'Справка','callback_data' =>'inline_two');
    $opt=[[$button1],[$button2]];

    $keyboard = array("inline_keyboard" =>$opt);
    $keyboard = json_encode($keyboard,true);
    $reply_markup = '&$reply_markup='.$keyboard;
    return $reply_markup;

}
