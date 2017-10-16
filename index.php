<?php
$output = json_decode(file_get_contents('php://input'),true);
$id = $output['message']['chat']['id'];
$token='469123782:AAHOpN4Fqow0wNjPYTW3wIke37V5JTwp9iI';
function sendMessage($token,$id)
{
file_get_contents("https://api.telegram.org/bot".$token."/sendMessage?chat_id=".$id."&text=hello");
}
file_put_contents("logs.txt",$id);