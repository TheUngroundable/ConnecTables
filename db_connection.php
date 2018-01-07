<?php
$nomehost = "localhost"; 
    $nomeuser = "root";
    $dbpassword = "";
    $dbname="my_connectables";
    $link = mysql_connect($nomehost,$nomeuser,$dbpassword) or die('Impossibile connettersi al server: ' . mysql_error());

    mysql_set_charset('utf8', $link);
    mysql_select_db($dbname) or die ('Accesso al database non riuscito: ' . mysql_error());
    ?>