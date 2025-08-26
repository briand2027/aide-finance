<?php
    define("hostname","localhost");
    define("database","aide_fianance");
    define("username","root");
    define("password","");

    $dsn= 'mysql:dbname='.database.'; host='.hostname.';charset=utf8';

    $conn= new PDO($dsn,username,password); 

    $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
?>
