<?php
$db = mysqli_connect('localhost', 'root', '', 'salvatori');

if(! $db){
    echo 'Hubo un error';
    exit;
}
?>