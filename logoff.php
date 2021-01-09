<?php
require_once "core/Core.php";
use templateSystem\ErrorTemplate;
// if(session_status() == PHP_SESSION_NONE) session_start();

if($_COOKIE['user-logged'] == "false"){
    $err = new ErrorTemplate("/core/templates/500-error-internal.html", "There's no user logged!", __FILE__, 7, "<button class=\"default-btn btn darkble-btn\" onclick=\"window.location.replace('http://localhost/')\">Back to the index</button>");
    die($err->parseFile());
}
else{
    session_unset();
    session_destroy();
    setcookie("user-logged", false, time() + 3600);
    unset($_COOKIE["user"]);
    unset($_COOKIE["mode"]);
    unset($_COOKIE["user-icon"]);
    unset($_COOKIE["checked"]);
    echo "<script src=\"js/main-script.js\"></script>";
    echo "<script>resetVals();\n</script>";

    header("Location: ../index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>LPGP Oficial Server</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link rel="stylesheet" href="css/layout.css">
    <script src="js/main-script.js"></script>
    <link rel="stylesheet" href="bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap/font-awesome.min.css">
    <script src="bootstrap/jquery-3.3.1.slim.min.js"></script>
    <script src="bootstrap/bootstrap.min.js"></script>
</head>
</html>
