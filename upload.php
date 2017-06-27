<?php

require_once('../login/isLoggedIn.php');

$uploaddir = '/var/www/pd_estimation/uploads/';
$uploadfile = $uploaddir . basename($_FILES['fileToUpload']['name']);
$privatedir = '/var/www/pd_estimation/uploads/'.$login->getUsername().'/';

$uploadOk = 1;
$FileType = pathinfo($uploadfile,PATHINFO_EXTENSION);

// Check if file already exists
/*
if (file_exists($uploadfile)) {
    echo "Sorry, file already exists.";
    //adding timestamp
    $uploadOk = 0;
}
*/

// Allow certain file formats
if($FileType != "xlsm" && $FileType != "xls") {
    echo "Sorry, only xlsm & xls files are allowed.";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $uploadfile)) {
        mkdir($privatedir, 0777);
        copy($uploadfile, $privatedir.date('Ymd-His').'.'.$FileType);
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
        header( "Location: http://".$_SERVER['HTTP_HOST']."/pd_estimation/show_adv.php");
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

?>
