<?php
/* ------------------------------------------------------------------------
 * to do:
 * +++1)Токен телеграм. Убрать в отдельный файл!
 * +++2)ApiKey гугл . Убрать в отдельный файл!
 * +++3)Создать вторую клавиатуру!!!
 * +++4)Починить get_address
 * 5)Разобраться с $lat $lon - добавить бд для широты и долготы, загрузка широты и долготы из бд
 * 6)Написать логику поиска ближайших сервисов/шиномонтажек
 * 7)Написать импорт номеров автоэвакуаторов из БД
 * 8)Изменить расширение файлов с токеном и apikey . закрыть к ним доступ на сервере.
 * 9
 *-------------------------------------------------------------------------
 * */
$output = json_decode(file_get_contents('php://input'),true);
$id = $output['message']['chat']['id'];
$token=file_get_contents('./token.txt');
$ApiKey=file_get_contents('./ApiKey.txt');
$message= $output['message']['text'];
$Location=$output['message']['location'];
switch ($message) {
    /*клавиатура 1*/
    case '/start':
        $message = 'Привет! Нажми отправить местоположение чтобы начать.';
        sendMessage($token, $id, $message . KeyboardMenu());
        break;
    case $Location['location']:
        global $lat,$lon;
        $lat = $Location['latitude'];
        $lon = $Location['longitude'];
        global $lat,$lon;
        if (isset($lat,$lon))
            {
                $message = "Отлично! ваше местонахождение определено. Широта: ".$lat."  Долгота: ".$lon."  Адрес: ".get_address($lat,$lon,$ApiKey);
            }
        else
            {
                $message ="Произошла ошибка, пожалуйста попробуйте ещё раз.";
            }
        sendMessage($token, $id, $message.KeyboardMenu());
        break;
    case 'Поиск ближайших мест': # сделать так что бы при пустой локации клавиатура 2 не открывалась
        if (isset($lat,$lon))
        {
            $message="Ответ приходит , всё покайфу";
            //Открытие доп. клавиатуры, логика вывода ближайших мест!!!
        }
        else
        {
            $message = 'Ваше местонахождение не определено. Пожалуйста нажмите на кнопку "Отправить местоположение"';
        }
        sendMessage($token,$id,$message.KeyboardMenu2());
        break;
    case 'Справка':
        $message='по вопросам разработки : vk.com/3vgenievich';
        sendMessage($token,$id,$message.KeyboardMenu());
        break;
    /*клавиатура 2*/
    case 'Ближайшие автосервисы':
        $keyword='автосервис';
        $message=get_nearest_places($lat,$lon,$keyword,$ApiKey);
        sendMessage($token,$id,$message.KeyboardMenu2());
        break;
    case 'Ближайшие шиномонтажи':
        $keyword='шиномонтаж';
        $message=get_nearest_places($lat,$lon,$keyword,$ApiKey);
        sendMessage($token,$id,$message.KeyboardMenu2());
        break;
    case 'Телефоны эвакуаторов':
        $message='телефоны из БД';
        sendMessage($token,$id,$message.KeyboardMenu2());
        break;
    case 'Назад':
        $message='Главное меню';
        sendMessage($token,$id,$message.KeyboardMenu());
        break;
    default:
        $message='Неправильный запрос. Для получения справки нажмите "Справка"';
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
    $url="https://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$lon."&key=".$ApiKey; //возвращает адрес по координатам
    $address = get_object_vars(json_decode(file_get_contents($url)));
    $address = $address['results'][0]->formatted_address;
    return $address;

}
function get_nearest_places($lat,$lon,$keyword,$ApiKey)
{
    $url="https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$lat.",".$lon."&radius=5000&type=car_repair&keyword=".$keyword."&key=".$ApiKey;//находит автосервисы в радиусе 5км
    $place = get_object_vars(json_decode(file_get_contents($url)));
    $place = $place['results'][0]->name.",".$place['results'][0]->opening_hours.",".$place['results'][0]->vicinity;
    return $place;



    /*
     *для автосервиса: type:car_repair keyword:автосервис
     *для шиномонтажа type:car_repair keyword:шиномонтаж
     *для эвакуаторов type:car_repair keyword:эвакуатор
     * */
}
function evacuation_call()
{
    //импорт номеров автоэвакуаторов из БД
}
?>

