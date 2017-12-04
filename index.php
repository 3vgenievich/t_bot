<?php
$output = json_decode(file_get_contents('php://input'),true);
$id = $output['message']['chat']['id'];
$token='469123782:AAHOpN4Fqow0wNjPYTW3wIke37V5JTwp9iI'; //Токен телеграм. Убрать в отдельный файл!!!
$ApiKey='AIzaSyDJy5MnyWi09N_HXiPBuDHyC2ZhIe9kZf4';      //ApiKey гугл .
$message= $output['message']['text'];
$Location=$output['message']['location'];
switch ($message) {
    case '/start':
        $message = 'Привет! Нажми отправить местоположение чтобы начать.';
        sendMessage($token, $id, $message . KeyboardMenu());
        break;
    case $Location['location']:
        $lat = $Location['location'];
        $lon = $Location['longitude'];
        if (isset($Lat))
            {
                $message = "Отлично! ваше местонахождение определено." . $lat;
            }
        else
            {
                $message ="error";
            }
        sendMessage($token, $id, $message,$lat,$lon . KeyboardMenu() . get_address($lat, $lon, $ApiKey));
        break;
    case 'Показать автосервисы':
    {
        if (isset($location))
        {
            $message="ОТВЕТ ПРИХОДИТ";
            //логика вывода ближайших мест
        }
        else
            {
            $message='Ваше местонахождение не определено. Пожалуйста нажмите на кнопку "Отправить местоположение"';
            }
    }
        sendMessage($token,$id,$message.KeyboardMenu());
        break;
    case 'Справка':
        $message='по вопросам разработки : vk.com/3vgenievich';
        sendMessage($token,$id,$message.KeyboardMenu());
        break;
    default:
        $message='Неправильный запрос.';
        sendMessage($token,$id,$message.KeyboardMenu());
}
///sendMessage($token,$id,$message);
function sendMessage($token, $id,$message)
{
file_get_contents("https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $id . "&text=".$message);
}
//тест клавиатуры
function KeyboardMenu(){
    $buttons = [[['text'=>"Отправить местоположение", 'request_location'=>true]],[['text'=>"Показать автосервисы"]],[['text'=>"Справка"]]];
    $keyboard =json_encode($keyboard=['keyboard' => $buttons,
                                        'resize_keyboard' => true,
                                        'one_time_keyboard'=> false,
                                        'selective' => true]);
    $reply_markup ='&reply_markup='.$keyboard.'';
    return $reply_markup;

}
function is_empty(&$var)
{
    return !($var || (is_scalar($var) && strlen($var)));
}

/**
 * @param $lat
 * @param $lon
 * @param $ApiKey
 */
function get_address($lat, $lon, $ApiKey)
{
    $uri="https://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$lon."&key=".$ApiKey; //гугл апи. возвращает адрес по координатам
    return json_decode(file_get_contents($uri));
}
function get_nearest_places($lat,$lon,$ApiKey)
{
    //логика поиска ближайших сервисов/шиномонтажек
}
?>
