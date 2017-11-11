<?php
$output = json_decode(file_get_contents('php://input'),true);
$id = $output['message']['chat']['id'];
$token='469123782:AAHOpN4Fqow0wNjPYTW3wIke37V5JTwp9iI';
$message= $output['message']['text'];
$lat=$output['latitude']; //Широта
$lon=$output['longitude'];  //Долгота
/*switch ($message){

    case '/start':
        $message='Привет!';
        sendMessage($token,$id,$message.KeyboardMenu());
        break;
    case 'Где я?':
        $message='Вы находитесь здесь: ';
        sendMessage($token,$id,$message.KeyboardMenu());
        break;
    case 'Справка':
        $message='по вопросам разработки : vk.com/3vgenievich';
        sendMessage($token,$id,$message.KeyboardMenu());
        break;
    default:
        $message='Неправильный запрос.';
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
    $buttons = [[['text'=>"Где я?", 'request_location'=>true]],[['text'=>"Показать автосервисы"]],[['text'=>"Справка"]]];
    $keyboard =json_encode($keyboard=['keyboard' => $buttons,
                                        'resize_keyboard' => true,
                                        'one_time_keyboard'=> false,
                                        'selective' => true]);
    $reply_markup ='&reply_markup='.$keyboard.'';
    return $reply_markup;

}
*/
function send($action, $data, $token){
    $url = "https://api.telegram.org/bot$token/$action";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_exec($ch);
}


//https://core.telegram.org/bots/api#sendmessage
function send_message($chatid, $text, $keyboard = null, $token){
    $data = array();
    $data["chat_id"] = $chatid;
    $data["text"] = $text;

    if(isset($keyboard))
        $data["reply_markup"] = $keyboard;

    send("sendMessage", $data, $token);
}


//https://core.telegram.org/bots/api#sendlocation
function send_location($chatid, $lat, $lon, $keyboard = null, $token){
    $data = array();
    $data["chat_id"] = $chatid;
    $data["latitude"] = $lat;
    $data["longitude"] = $lon;

    if(isset($keyboard))
        $data["reply_markup"] = $keyboard;

    send("sendLocation", $data, $token);
}


//https://core.telegram.org/bots/api#keyboardbutton
function keyboardButton($text, $request_contact = false, $request_location = false){
    return array(
        "text" => $text,
        "request_contact" => $request_contact,
        "request_location" => $request_location,
    );
}


//https://core.telegram.org/bots/api#replykeyboardmarkup
function replyKeyboardMarkup($buttons, $resize_keyboard = true, $one_time_keyboard = false, $selective = false){
    $keyboard = array(
        "keyboard" => array($buttons),
        "resize_keyboard" => $resize_keyboard,
        "one_time_keyboard" => $one_time_keyboard,
        "selective" => $selective
    );

    return  json_encode($keyboard);
}




function setState($chatID, $state){
    file_put_contents("./states/chat_$chatID", $state);
}


function getState($chatID){
    $stateFile = "./states/chat_$chatID";

    if(!file_exists($stateFile))
        file_put_contents($stateFile, "getText");

    return file_get_contents($stateFile);
}



//--------------------------------------------------



//получаем данные, от Telegram
$data = json_decode(file_get_contents('php://input'));



$message = isset($data->message) ? $data->message : null;
$date = isset($message->date) ? $message->date : null;
$chatid = isset($message->chat->id) ? $message->chat->id : null;
$text = isset($message->text) ? $message->text : null;


$state =  getState($chatid); //получаем текущее состояние бота для данного пользователя


$buttons = array(
    keyboardButton("Услуги"),
    keyboardButton("Контакты"),
    keyboardButton("Заказать"),
);

$keyboard = replyKeyboardMarkup($buttons);


$textFiles = array(
    "Контакты" => "contacts.txt",
    "Заказать" => "order.txt",
    "Default" => "main.txt",
);


switch($state){

    case "getPhone":

        // проверяем, что отправляен именно номер, если да, то меняем состояние на ожидание отправки адреса
        if (isset($message->contact->phone_number) && trim($message->contact->phone_number)) {
            setState($chatid, "getLocation");
            $msg = "Отлично. Сейчас укажите куда привезти";
            $buttons = array(
                keyboardButton("Отправить мое местоположение", false, true),
            );

            $keyboard = replyKeyboardMarkup($buttons);

            //TODO: здесь логика для сохранения номера телефона
        }
        else{
            $msg = "Нажмите кнопку \"Отправить номер телефона\"";
            $keyboard = null;
        }

        send_message($chatid, $msg, $keyboard, $token);
        break;


    case "getLocation":

        // проверяем, что отправляены именно координаты
        if (isset($message->location)) {
            setState($chatid, "getText");
            $msg = "Отлично. Ваш заказ принят.";

            //TODO: здесь логика для сохранения координат и информирования исполнителя
        }
        else{
            $msg = "Нажмите кнопку \"Отправить мое местоположение\"";
            $keyboard = null;
        }

        send_message($chatid, $msg, $keyboard, $token);
        break;


    default:  //getText
        $file = array_key_exists($text, $textFiles) ? $textFiles["$text"] : $textFiles["Default"];
        $msg = file_get_contents($file);

        setState($chatid, "getText");

        if($text == "Контакты"){
            $geo = explode(",", file_get_contents("geo.txt"));
            send_location($chatid, trim($geo[0]), trim($geo[1]), $keyboard, $token);
        }

        if($text == "Заказать"){
            setState($chatid, "getPhone");  //меняем состояние на ожидание отправки номера телефона

            $buttons = array(
                keyboardButton("Отправить номер телефона", true),
            );

            $keyboard = replyKeyboardMarkup($buttons);
        }

        send_message($chatid, $msg, $keyboard, $token);

}

