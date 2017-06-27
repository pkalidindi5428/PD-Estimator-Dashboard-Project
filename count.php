<?php
?>


<!DOCTYPE html>
<html>
<body>

<br><br><p>

<form action="upload.php" method="post" enctype="multipart/form-data">
    Select xlsm to upload:
    <input type="file" name="fileToUpload" id="fileToUpload">
    <input type="submit" value="Upload file" name="submit">
</form>

<br><br><p>

<?
$host_ip=$_SERVER['HTTP_HOST'];
echo "<a href='http://".$host_ip."/pd_estimation/show_adv.php'>Historical Records</a>";
?>

</body>
</html>
