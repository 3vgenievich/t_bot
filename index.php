<?php
$output = json_decode(file_get_contents('php://input'),true);
$id = $output['message']['chat']['id'];
$token='469123782:AAHOpN4Fqow0wNjPYTW3wIke37V5JTwp9iI';
$ApiKey='AIzaSyDJy5MnyWi09N_HXiPBuDHyC2ZhIe9kZf4';
$message= $output['message']['text'];
$Location=$output['message']['location'];



$url = parse_url(getenv("mysql://bf201afc3c04bc:67a8b83e@eu-cdbr-west-01.cleardb.com/heroku_b8eb8cf712bc20c?reconnect=true"));
$server ='eu-cdbr-west-01.cleardb.com';
$username = 'bf201afc3c04bc';
$password = '67a8b83e';
$db = 'heroku_b8eb8cf712bc20c';
$table='locations';
$conn = new mysqli($server, $username, $password, $db);
switch ($message){
    case '/start':
        $message = 'Привет! Нажми отправить местоположение чтобы начать.';
        sendMessage($token, $id, $message . KeyboardMenu());
        break;
    case $Location['location']:
        $lat = $Location['latitude'];
        $lon = $Location['longitude'];
        $idusr=$id['id'];
        if (isset($lat,$lon))
            {
                $message = "Отлично! ваше местонахождение определено.  Широта: ".$lat."  Долгота: ".$lon."  Адрес: ".get_address($lat,$lon,$ApiKey).write_location($conn,$lat,$lon,$id);
            }
        else
            {
                $message ="Произошла ошибка, пожалуйста попробуйте ещё раз.";
            }
        sendMessage($token, $id, $message.KeyboardMenu());
        break;
    case 'Поиск ближайших мест':
        if ((mysqli_num_rows($conn->query("SELECT lat,lon FROM heroku_b8eb8cf712bc20c.locations WHERE id='$id'")))>0)
        {
            $message="Выберите";
            sendMessage($token,$id,$message.KeyboardMenu2());
            //Открытие доп. клавиатуры, логика вывода ближайших мест!!!
        }
        else
        {
            $message = 'Ваше местонахождение не определено. Пожалуйста нажмите на кнопку "Отправить местоположение"';
            sendMessage($token,$id,$message.KeyboardMenu());
        }
        break;
    case 'Справка':
        $message='по вопросам разработки : vk.com/3vgenievich';
        sendMessage($token,$id,$message.KeyboardMenu());
        break;
    /*клавиатура 2*/

    case 'Ближайшие автосервисы':
        $type='car_repair';
        $keyword='автосервис';
        $message="ближайший к вам автосервис: ".get_nearest_places($type,$keyword,$ApiKey,$conn,$id);
        sendMessage($token,$id,$message.KeyboardMenu2());
        break;
    case 'Ближайшие шиномонтажи':
        $type='car_repair';
        $keyword='шиномонтаж';
        $message="ближайший к вам шиномонтаж:  ".get_nearest_places($type,$keyword,$ApiKey,$conn,$id);
        sendMessage($token,$id,$message.KeyboardMenu2());
        break;
    case 'Ближайшие автомойки':
        $type='car_wash';
        $keyword='мойка';
        $message="ближайшие автомойки:  ".get_nearest_places($type,$keyword,$ApiKey,$conn,$id);
        sendMessage($token,$id,$message.KeyboardMenu2());
        break;

    case 'Телефоны эвакуаторов':
        $message='ну тут из бд будут телефоны да';
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
function sendMessage($token, $id,$message)
{
    file_get_contents("https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $id . "&text=".$message);
}

function write_location($conn,$lat,$lon,$id)
{
    if ((mysqli_num_rows($conn->query("SELECT id FROM heroku_b8eb8cf712bc20c.locations WHERE id='$id'")))>0)
    {
        $conn->query("UPDATE heroku_b8eb8cf712bc20c.locations  SET lat='$lat',lon='$lon' WHERE id='$id'");
        // Если уже отправлял местоположение
    }
    else
    {
        $conn->query("INSERT INTO heroku_b8eb8cf712bc20c.locations  SET id='$id',lat='$lat',lon='$lon'");
        // Если в первый раз
    }
}

function get_address($lat, $lon, $ApiKey)
{
    $url="https://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$lon."&key=".$ApiKey."&language=ru"; //возвращает адрес по координатам
    $address = get_object_vars(json_decode(file_get_contents($url)));
    $address = $address['results'][0]->formatted_address;
    return $address;

}
function get_nearest_places($type,$keyword,$ApiKey,$conn,$id)
{
    $res = $conn->query("SELECT * FROM heroku_b8eb8cf712bc20c.locations WHERE  id ='$id'");
    $res = mysqli_fetch_assoc($res);
    $lat =$res['lat'];
    $lon=$res['lon'];
    $url="https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=".$lat.",".$lon."&rankby=distance&type=.$type.&keyword=".$keyword."&key=".$ApiKey."&language=ru";//находит автосервисы в радиусе 5км
    $place = get_object_vars(json_decode(file_get_contents($url)));
    $place = "1)  ".$place['results'][0]->name.",".$place['results'][0]->vicinity.PHP_EOL."  2) ".$place['results'][1]->name.",".$place['results'][1]->vicinity.PHP_EOL."  3)  ".$place['results'][2]->name.",".$place['results'][2]->vicinity;
    return $place;

    /*
     *для автосервиса: type:car_repair keyword:автосервис
     *для шиномонтажа type:car_repair keyword:шиномонтаж
     *для эвакуаторов type:car_repair keyword:эвакуатор
     * */
}

###КЛАВИАТУРЫ###
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
    $buttons=[[['text'=>"Ближайшие автосервисы"]],[['text'=>"Ближайшие шиномонтажи"]],[['text'=>"Ближайшие автомойки"]],[['text'=>"Телефоны эвакуаторов"]],[['text'=>"Назад"]]];
    $keyboard=json_encode($keyboard=['keyboard' => $buttons,
        'resize_keyboard' => true,
        'one_time_keyboard'=> false,
        'selective' => true]);
    $reply_markup ='&reply_markup='.$keyboard.'';
    return $reply_markup;

}



