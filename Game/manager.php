<?php

/*=========================================================================
BRAINSTORMING
===========================================================================*/
/*
	First: owner starts
    Understand order of phases with phase_type.ID
    Start at round 1, turn player, phase 1
    Increment phases till last, then with the same button increment turn
    After last player of round finishes, increment round and start over

	manager does things and then redirects to correct page
	after everything is done, the page redirects to manager
	manager then does his things and redirects to the next page

	everytime $battleID is given via $_GET['id']

	FIRST STEP: Change phases by clicking button

	Get Last round, turn and phase
	If empty for this battle, initialize table

	N STEP:
	
	Logic:
	I need to understand when the current player has played the last phase
	When it happens, i need to start over with the next player in the array of players
	When the last player in game has played his last phase i need to start over with the owner and increment the round.



	Control:
	If you go to manager with permission, manager will update the phase/turn/round and redirects you to the correct page

	Whenever anybody accesses the manager without permission, he redirects to correct page based on the phase BUT if it is not your turn, you must go to wait page which refreshes checking if it is your turn.
	If you access any other phase while waiting, you must go back to waiting room


*/

/*=========================================================================
SESSION CONTROL
===========================================================================*/
	
	session_start();

    include_once("../db_connection.php");

    if(!isset($_SESSION['session'])){ 

        header("Location: login.php");  

    }



    
/*=========================================================================
GETTERS $_GET[];
===========================================================================*/

	$battleID = $_GET['id'];
	$permission = $_GET['permission'];

    
/*=========================================================================
GET OWNER BY ID IN BATTLE
===========================================================================*/

	$sql = "SELECT player.ID as owner from player, battle WHERE battle.FK_Player = player.ID AND battle.ID = ".$battleID;
	$result = mysql_query($sql);
	$temp = mysql_fetch_assoc($result);
	$ownerID = $temp['owner'];

	echo "ownerID: ".$ownerID;

/*=========================================================================
INITIALIZATION
===========================================================================*/
    //If the battle is "virgin", initialize it

	$sql = "SELECT * FROM battle, round, turn, player WHERE battle.FK_Player = player.ID AND round.FK_Battle = battle.ID AND round.FK_Turn = turn.ID AND turn.FK_Player = player.ID AND battle.ID = ".$battleID;

	$result = mysql_query($sql);


	 //Check if virgin battle
	if(!mysql_num_rows($result)){

	 	echo "lawl";

		 //Initialize Turn
	 	$sql = "INSERT INTO turn (ID, FK_Phase, FK_Player) VALUES (NULL, '1', '".$ownerID."')";

	 	mysql_query($sql) or die (mysql_errno());

	 	//Initialize Round

	 	$sql = "INSERT INTO round (ID, FK_Battle, FK_Turn) VALUES ('1', '".$battleID."', '".mysql_insert_id()."')";
	 	mysql_query($sql) or die ("cannot initialize Round");

	 	//Initialize Distances Table for this battle

	 	//for each unit in army in battle, create row 

	 	//get every unit in army

	 	$sql = "SELECT unit.id as unitid , player.id as player, unit.def_size as size FROM unit, army, army_in_battle, battle, player WHERE unit.FK_Army = army.id AND army_in_battle.FK_Battle = battle.id AND army_in_battle.FK_Army = army.id AND army_in_battle.FK_Player = player.ID AND battle.ID = ".$battleID;

	 	$result = mysql_query($sql);

	 		//for every unit link it to his owner in Unit_In_Battle and initialize table

 		while($row = mysql_fetch_array($result)){

 			$query = "INSERT INTO unit_in_battle (FK_Battle, FK_Unit, FK_Player, Size, Ran, In_Combat_With, Save_Request, Save_Result) VALUES ('".$battleID."', '".$row['unitid']."', '".$row['player']."', '".$row['size']."', '0', NULL, NULL, NULL)";


 			echo "<br>BattleID: ".$battleID;
 			echo "<br>Unit: ".$row['unitid'];
 			echo "<br>Player: ".$row['player'];
 			echo "<br>Size: ".$row['size'];
 			
 			mysql_query($query) or die ("could not initialize unit in battle. Errno: ".mysql_errno()." + Error: ".mysql_error());
	
		}

		//For every unit in battle, initialize Distances Table


		//Get all id and player of units in game

		$sql= "SELECT unit.ID as ID, player.ID as player FROM army_in_battle, unit, player, battle, army, unit_type WHERE army_in_battle.FK_Battle = battle.ID AND army_in_battle.FK_Army = army.ID AND army_in_battle.FK_Player = player.ID AND unit.FK_Army = army.ID AND unit.FK_Unit_Type = unit_type.ID AND battle.ID = ".$battleID;

		$result = mysql_query($sql);
		
		while($row = mysql_fetch_array($result)){

			$query = "SELECT unit.ID as ID, player.ID as player FROM army_in_battle, unit, player, battle, army, unit_type WHERE army_in_battle.FK_Battle = battle.ID AND army_in_battle.FK_Army = army.ID AND army_in_battle.FK_Player = player.ID AND unit.FK_Army = army.ID AND unit.FK_Unit_Type = unit_type.ID AND battle.ID = ".$battleID;

			$secondResult = mysql_query($query);

			while($datas = mysql_fetch_array($secondResult)){

				if($row['ID'] != $datas['ID']){

					$sql = "INSERT INTO distances (FK_Battle, FK_Player, FK_Source, FK_Target, Distance) VALUES ('".$battleID."','".$row['player']."','".$row['ID']."','".$datas['ID']."','0')";
					mysql_query($sql) or die("no va beh questa query era impossibile<br>".mysql_errno(). " --- ".mysql_error());

				}


			}

		}

			


		
	}

/*=========================================================================
CONTROL FOR ANY NON AUTHORIZED ACCESS
===========================================================================*/




/*=========================================================================
GET PLAYERS LIST
===========================================================================*/

	$sql = "SELECT player.ID as player from player, army_in_battle, battle WHERE army_in_battle.FK_Player = player.ID AND army_in_battle.FK_Battle = battle.ID AND battle.ID = ".$battleID." order by player.ID = ".$ownerID." desc";

	$result = mysql_query($sql);
	$players = array();
	while ($row = mysql_fetch_array($result)) {
	 
		$players = $row;
	}

	$players = array_values($players);

	$lastPlayer = end($players);

/*=========================================================================
GET LAST ROUND FOR THIS BATTLE
===========================================================================*/

	$sql = "SELECT round.ID as round, turn.FK_Phase as phase, turn.FK_Player as player, phase_type.type as type from battle, round, turn, phase_type, player
WHERE round.FK_Battle = battle.ID AND round.FK_Turn = turn.ID AND turn.FK_Phase = phase_type.ID AND turn.FK_Player = player.ID AND battle.ID = '".$battleID."' ORDER BY round.FK_Turn DESC LIMIT 1";

	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);

	$phase = $row['phase'];
	$currentPlayer = $row['player'];
	$round = $row['round'];
	$typeForRedirect = $row['type'];


	//echo "<br>vero current player: ".$currentPlayer;

/*=========================================================================
UPDATE ROUND
===========================================================================*/	 
	

	//if button pressed


		if ($permission == 1) {

	 
		 	//If phase is less Battleshock, increase phase and update table
		 	//if($phase < 5 ){
			if($phase < 4 ){

				$phase++;

		 	} else {

		 		$phase = 1;
		 		//update current player
		 		//find index of current player in $players[] and get the next one and update turn

		 		$index = array_search($currentPlayer, $players); 
		 		
		 		if($currentPlayer != $lastPlayer){

		 			$currentPlayer = $players[$index++];

		 		} else {

		 			//if last player has played in this turn, increment $round and set current player to owner

		 			$currentPlayer = $ownerID;
		 			$round++;
		 			
		 		}

		 		header("Refresh: 0");

		 		
		 	}





		 	//Update Turn
		 	$sql = "INSERT INTO turn (ID, FK_Phase, FK_Player) VALUES (NULL, '".$phase."', '".$currentPlayer."')";

		 	mysql_query($sql) or die ("Cannot update turn");

		 	echo "updated turn";
		 	
		 	//Update Round
		 	$sql = "INSERT INTO round (ID, FK_Battle, FK_Turn) VALUES ('".$round."', '".$battleID."', '".mysql_insert_id()."')";
		 	mysql_query($sql) or die ("Cannot update round");

		 	echo "updated round";


	 	}
	 


/*=========================================================================
GET UPDATED ROUND DATAS
===========================================================================*/
	
	$sql = "Select round.ID as round, turn.FK_Phase as phase, turn.FK_Player as player, phase_type.type as type from battle, round, turn, phase_type, player
WHERE round.FK_Battle = battle.ID AND round.FK_Turn = turn.ID AND turn.FK_Phase = phase_type.ID AND turn.FK_Player = player.ID AND battle.ID = ".$battleID." ORDER BY round.FK_Turn DESC LIMIT 1";

	$result = mysql_query($sql);


	while($row = mysql_fetch_array($result)){

	$phase = $row['phase'];
	//echo $phase;
	$currentPlayer = $row['player'];
	//echo $currentPlayer;
	$round = $row['round'];
	//echo $round;
	$typeForRedirect = $row['type'];
	//echo $typeForRedirect;

	}

/*=========================================================================
REDIRECT TO CORRECT PAGE IF YOU ARE THE PLAYER ELSE REDIRECT TO WAIT ROOM
===========================================================================*/	 
/*
echo "<br>Current Player:";
echo $currentPlayer;
echo "<br> SESSIONE: ";
echo $_SESSION['ID'];
*/

if($currentPlayer == $_SESSION['ID']){

	//echo "dioporco";
	header("Location: ".strtolower($typeForRedirect).".php?id=".$battleID);

} else {

	
	header("Location: wait.php?id=".$battleID);

}

/*=========================================================================
DEBUG
===========================================================================*/
	 /*
	echo "<br>Current Player:";
	echo $row['player'];
	echo "<br>Current player test: ";
	echo $currentPlayertest;
	echo "<br>Last Player in Round:";
	echo $lastPlayer;
	echo "<br>Round:";
	echo $row['round'];
	echo "<br>PhaseID:";
	echo $row['phase'];
	echo "<br>Type:";
	echo $row['type'];
*/

?>
