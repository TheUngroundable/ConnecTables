<?php

    include_once("../db_connection.php");


    $battleID = $_GET['id'];

/*=========================================================================
GET LAST ROUND FOR THIS BATTLE
===========================================================================*/

  $sql = "SELECT ID as id, FK_Source as source, FK_Target as target, slain as slain FROM death_log WHERE FK_Battle = ".$battleID." AND Done = '0' ORDER BY death_log.ID";

  $result = mysql_query($sql);
  while($row = mysql_fetch_array($result)){

  	echo "ID:".$row['id']."|Source:".$row['source']."|Target:".$row['target']."|Slain:".$row['slain'].";";

  }
 

?>