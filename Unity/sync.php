<?php

    include_once("../db_connection.php");


    $battleID = $_GET['id'];

/*=========================================================================
GET LAST ROUND FOR THIS BATTLE
===========================================================================*/

  $sql = "SELECT round.ID as round, turn.FK_Phase as phase, turn.FK_Player as player, phase_type.type as type from battle, round, turn, phase_type, player
WHERE round.FK_Battle = battle.ID AND round.FK_Turn = turn.ID AND turn.FK_Phase = phase_type.ID AND turn.FK_Player = player.ID AND battle.ID = '".$battleID."' ORDER BY round.FK_Turn DESC LIMIT 1";

  $result = mysql_query($sql);
  $row = mysql_fetch_array($result);

  $typeForRedirect = $row['type'];


echo $typeForRedirect;
   

?>