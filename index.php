<?php
$output = json_decode(file_get_contents('php://input'),true);
$id = $output['message']['text'];
file_put_contents("logs.txt",$id);
