<?php


    include_once("../db_connection.php");


    $battleID = $_GET['id'];
    $sourceID = $_GET['source'];
    $targetID = $_GET['target'];
    $distance = $_GET['distance'];

/*=========================================================================
GET LAST ROUND FOR THIS BATTLE
===========================================================================*/

  $sql = "UPDATE distances SET Distance=".$distance." WHERE FK_Battle =".$battleID." AND FK_Target =".$targetID." AND FK_Source=".$sourceID;

  $result = mysql_query($sql);

  echo "Source: ".$sourceID." - Target: ".$targetID." - Distance: ".$distance;
   

?>