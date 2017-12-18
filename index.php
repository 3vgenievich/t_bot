<?php
/*
 *Написать функцию добавления в бд. Сделать проверку по ID, если такой ID уже есть в бд то UPDATE, если нет то INSERT
 * Написать функцию загрузки местоположения из бд
 * добавить клавиатуру "показать след. место"
 * */
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
                $message = "Отлично! ваше местонахождение определено.  Широта: ".$lat."  Долгота: ".$lon."  Адрес: ".get_address($lat,$lon,$ApiKey);
                $res = $conn->query("SELECT COUNT(*) FROM heroku_b8eb8cf712bc20c.locations WHERE id ='$id'") or die();
                $row = $conn->fetch_row($res);
                if ($row[0] < 0)
                {
                    $conn->query("INSERT INTO heroku_b8eb8cf712bc20c.locations  SET id='$id',lat='$lat',lon='$lon'");
                    // нет данных
                }
                else
                {
                    $conn->query("UPDATE heroku_b8eb8cf712bc20c.locations  SET id='$id',lat='$lat',lon='$lon'");
                    // Есть данные
                }
            }
        else
            {
                $message ="Произошла ошибка, пожалуйста попробуйте ещё раз.";
            }
        sendMessage($token, $id, $message.KeyboardMenu());
        break;
    case 'Поиск ближайших мест': # сделать так что бы при пустой локации клавиатура 2 не открывалась
        $lat=$conn->query("SELECT lat FROM heroku_b8eb8cf712bc20c.locations WHERE id='$id'");
        $lon=$conn->query("SELECT lon FROM heroku_b8eb8cf712bc20c.locations WHERE id='$id'");
        if (isset($lat,$lon))
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
        $lat=$conn->query("SELECT lat FROM heroku_b8eb8cf712bc20c.locations WHERE id='$id'");
        $lon=$conn->query("SELECT lon FROM heroku_b8eb8cf712bc20c.locations WHERE id='$id'");
        $keyword='автосервис';
        $message="ближайший к вам автосервис".get_nearest_places($lat,$lon,$keyword,$ApiKey).$lat.$lon;
        sendMessage($token,$id,$message.KeyboardMenu2());
        break;
    case 'Ближайшие шиномонтажи':
        $lat=$conn->query("SELECT lat FROM heroku_b8eb8cf712bc20c.locations WHERE id='$id'");
        $lon=$conn->query("SELECT lon FROM heroku_b8eb8cf712bc20c.locations WHERE id='$id'");
        $keyword='шиномонтаж';
        $message="ближайший к вам шиномонтаж".get_nearest_places($lat,$lon,$keyword,$ApiKey);
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
    $url="https://maps.googleapis.com/maps/api/geocode/json?latlng=".$lat.",".$lon."&key=".$ApiKey."&language=ru"; //возвращает адрес по координатам
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
?>

