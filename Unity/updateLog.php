<?php

    include_once("../db_connection.php");


    $battleID = $_GET['id'];

    $logID = $_GET['log'];
/*=========================================================================
GET LAST ROUND FOR THIS BATTLE
===========================================================================*/

  $sql = "UPDATE death_log SET done = 1 WHERE FK_Battle = ".$battleID." AND ID = ".$logID;

  $result = mysql_query($sql);
  
  echo "Updated Log n".$logID;
 

?>