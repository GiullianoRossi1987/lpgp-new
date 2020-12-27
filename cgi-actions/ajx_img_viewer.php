<?php

if(isset($_FILES['img-auto-load'])){
    if(move_uploaded_file($_FILES['img-auto-load']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . "/media/tmp/" . $_FILES['img-auto-load']['name'])){
        die("/media/tmp/" . $_FILES['img-auto-load']['name']);
    }
    else die($_FILES['img-auto-load']['error']);
}

 ?>
