<?php
/* ------------------------------------------------------------------------
 * to do:
 * 1)Токен телеграм. Убрать в отдельный файл!
 * 2)ApiKey гугл . Убрать в отдельный файл!
 * 3)Написать Открытие доп. клавиатуры, логика вывода ближайших мест!!!
 * 4)Починить get_adress
 * 5)Разобраться с $lat $lon
 * 6)Написать логику поиска ближайших сервисов/шиномонтажек
 * 7)Написать импорт номеров автоэвакуаторов из БД
 *-------------------------------------------------------------------------
 * */
$output = json_decode(file_get_contents('php://input'),true);
$id = $output['message']['chat']['id'];
$token='469123782:AAHOpN4Fqow0wNjPYTW3wIke37V5JTwp9iI'; //Токен телеграм. Убрать в отдельный файл!!!
$ApiKey='AIzaSyDJy5MnyWi09N_HXiPBuDHyC2ZhIe9kZf4';      //ApiKey гугл . Убрать в отдельный файл!!!
$message= $output['message']['text'];
$Location=$output['message']['location'];

switch ($message) {
    /*клавиатура 1*/
    case '/start':
        $message = 'Привет! Нажми отправить местоположение чтобы начать.';
        sendMessage($token, $id, $message . KeyboardMenu());
        break;
    case $Location['location']:
        $lat = $Location['latitude'];
        $lon = $Location['longitude'];
        if (isset($lat,$lon))
            {
                $message = "Отлично! ваше местонахождение определено. Широта: ".$lat."  Долгота: ".$lon;
            }
        else
            {
                $message ="Произошла ошибка, пожалуйста попробуйте ещё раз.";
            }
        sendMessage($token, $id, $message.KeyboardMenu());
        break;
    case 'Поиск ближайших мест': #сделать так что бы при пустой локации клавиатура 2 не открывалась
        if (isset($lat,$lon))
        {
            $message="ОТВЕТ ПРИХОДИТ";
            //Открытие доп. клавиатуры, логика вывода ближайших мест!!!
        }
        else
        {
            $message = 'Ваше местонахождение не определено. Пожалуйста нажмите на кнопку "Отправить местоположение"';
        }
        sendMessage($token,$id,$message.KeyboardMenu2());
        break;
    case 'FAQ':
        $message='по вопросам разработки : vk.com/3vgenievich';
        sendMessage($token,$id,$message.KeyboardMenu());
        break;
    /*клавиатура 2*/
    case 'Ближайшие автосервисы':
        $message='вывод ближайших ... через апи';
        sendMessage($token,$id,$message.KeyboardMenu2());
        break;
    case 'Ближайшие шиномонтажи':
        $message='вывод ближайших ... через апи';
        sendMessage($token,$id,$message.KeyboardMenu2());
        break;
    case 'Телефоны эвакуаторов':
        $message='телефоны из БД';
        sendMessage($token,$id,$message.KeyboardMenu2());
        break;
    case 'Назад':
        sendMessage($token,$id.KeyboardMenu());
        break;
    default:
        $message='Неправильный запрос. Для получения справки нажмите "FAQ"';
        sendMessage($token,$id,$message.KeyboardMenu());
}
///sendMessage($token,$id,$message);
function sendMessage($token, $id,$message)
{
file_get_contents("https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $id . "&text=".$message);
}

function KeyboardMenu()  #Основная клавиатура
{
    $buttons = [[['text'=>"Отправить местоположение", 'request_location'=>true]],[['text'=>"Поиск ближайших мест"]],[['text'=>"Справка"]]];
    $keyboard =json_encode($keyboard=['keyboard' => $buttons,
                                        'resize_keyboard' => true,
                                        'one_time_keyboard'=> false,
                                        'selective' => true]);
    $reply_markup ='&reply_markup='.$keyboard.'';
    return $reply_markup;
}

function KeyboardMenu2()  #дополнительная клавиатура
{
    $buttons=[[['text'=>"Ближайшие автосервисы"]],[['text'=>"Ближайшие шиномонтажи"]],[['text'=>"Телефоны эвакуаторов"]],[['text'=>"Назад"]]];
    $keyboard=json_encode($keyboard=['keyboard' => $buttons,
        'resize_keyboard' => true,
        'one_time_keyboard'=> false,
        'selective' => true]);
    $reply_markup ='&reply_markup='.$keyboard.'';
    return $reply_markup;

}
function get_address($lat, $lon, $ApiKey)
{
    $uri="https://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$lon."&key=".$ApiKey; //гугл апи. возвращает адрес по координатам
    return json_decode(file_get_contents($uri));
}
function get_nearest_places($lat,$lon,$ApiKey)
{
    //логика поиска ближайших сервисов/шиномонтажек
}
function evacuation_call()
{
    //импорт номеров автоэвакуаторов из БД
}
?>

