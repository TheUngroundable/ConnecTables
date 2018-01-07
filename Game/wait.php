<?php

/*=========================================================================
SESSION CONTROL
===========================================================================*/
  
  session_start();

    include_once("../db_connection.php");
    include("dicesTool.php");

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

  //echo "ownerID: ".$ownerID;

/*=========================================================================
GET LAST ROUND FOR THIS BATTLE
===========================================================================*/

  $sql = "SELECT round.ID as round, turn.FK_Phase as phase, turn.FK_Player as player, phase_type.type as type from battle, round, turn, phase_type, player
WHERE round.FK_Battle = battle.ID AND round.FK_Turn = turn.ID AND turn.FK_Phase = phase_type.ID AND turn.FK_Player = player.ID AND battle.ID = ".$battleID." ORDER BY round.FK_Turn DESC LIMIT 1";

  $result = mysql_query($sql);
  $row = mysql_fetch_assoc($result);

  $phase = $row['phase'];
  $currentPlayer = $row['player'];
  $round = $row['round'];




$totalInsuccess = NULL;

if(isset($_POST['save'])){

  $sql = "SELECT unit.ID as target, unit_in_battle.In_Combat_With as combat_with, unit_in_battle.Save_Request as saveRequest, unit_in_battle.Save_Result as rend, unit_in_battle.Damage as damage, unit_in_battle.Shot as shot  FROM unit_in_battle, battle, player, unit WHERE unit_in_battle.FK_Unit = unit.ID AND unit_in_battle.FK_Player = player.ID AND unit_in_battle.FK_Battle = battle.ID AND battle.ID = ".$battleID." AND player.ID = ".$_SESSION['ID']." AND unit_in_battle.In_Combat_With IS NOT NULL";

    $result = mysql_query($sql);

    while($tosave = mysql_fetch_array($result)){

      //i need the save roll for this unit

      $sql = "SELECT unit.Save FROM unit WHERE unit.id=".$tosave['target'];
    
      $tempSaveRoll = mysql_query($sql);
      $targetSaveRoll = mysql_result($tempSaveRoll,0);  

      $numSaves = $tosave['saveRequest'];
      $rend = $tosave['rend'];

      $roll  = rollDices($numSaves, $targetSaveRoll + $rend);

      $damage = $tosave['damage'];

      //for every insuccess update death log with target, source and number of deaths

      $insuccess = $numSaves - $roll;
      $insuccess *= $damage;
      //i need the round ffs
      $totalInsuccess+=$insuccess;

      $saved = false;

      if($insuccess <= 0){

        $saved = true;

        //$sql = "UPDATE unit_in_battle SET In_Combat_With = NULL , Save_Request = NULL, Save_Result = NULL, Damage = NULL, Shot = NULL WHERE FK_Battle = ".$battleID." AND FK_Unit = ".$tosave['target'];
            //mysql_query($sql) or die ("could not reset unit_in_battle");

        $sql = "INSERT INTO death_log (FK_Battle, FK_Round, FK_Source, FK_Target, Slain, Done) VALUES ('".$battleID."', '".$round."', '".$tosave['combat_with']."', '".$tosave['target']."', '0', '0')";

       mysql_query($sql) or die ("Could not update Death Log! Values: ".$battleID." - ".$round." - ".$tosave['combat_with']." - ".$tosave['target']." - ".$insuccess." - ".mysql_error()." - ".mysql_errno());

       if(is_null($toSave['shot'])){

            $sql = "UPDATE unit_in_battle SET Save_Request = NULL, Save_Result = NULL, Damage = NULL, Shot = NULL WHERE FK_Battle = ".$battleID." AND FK_Unit = ".$tosave['target'];;
            mysql_query($sql) or die ("could not reset unit_in_battle");



       } else {

        $sql = "UPDATE unit_in_battle SET In_Combat_With = NULL , Save_Request = NULL, Save_Result = NULL, Damage = NULL, Shot = NULL WHERE FK_Battle = ".$battleID." AND FK_Unit = ".$tosave['target'];
            mysql_query($sql) or die ("could not reset unit_in_battle");


       }


      }

      if(!$saved){

      
       //Update Death log

       $sql = "INSERT INTO death_log (FK_Battle, FK_Round, FK_Source, FK_Target, Slain, Done) VALUES ('".$battleID."', '".$round."', '".$tosave['combat_with']."', '".$tosave['target']."', '".$insuccess."', '0')";

       mysql_query($sql) or die ("Could not update Death Log! Values: ".$battleID." - ".$round." - ".$tosave['combat_with']." - ".$tosave['target']." - ".$insuccess." - ".mysql_error()." - ".mysql_errno());

      //update unit_in_battle removing deaths and reset variables


       //get current unit size in unit_in_battle

       $sql = "SELECT size, shot from unit_in_battle WHERE FK_Battle =".$battleID." AND FK_Player =".$_SESSION['ID']." AND FK_Unit =".$tosave['target'];

       $resultSize = mysql_query($sql);
       $resultSize = mysql_fetch_array($resultSize);
       $size = $resultSize['size'];
       $shot = $resultSize['shot'];

       $currentSize = $size - $insuccess; 

       //If you lost every model, you remove target and source from combat

       if($currentSize <= 0){

          $sql = "UPDATE unit_in_battle SET In_Combat_With = NULL WHERE FK_Battle=".$battleID." AND FK_Unit =".$tosave['target'];
            mysql_query($sql) or die ("could not reset unit_in_battle In_Combat_With when dead unit - ERR:".mysql_error()." - ERRNO: ".mysql_errno());

             $sql = "UPDATE unit_in_battle SET In_Combat_With = NULL WHERE FK_Battle=".$battleID." AND FK_Unit =".$tosave['combat_with'];

            mysql_query($sql) or die ("could not reset unit_in_battle In_Combat_With when dead unit - ERR:".mysql_error()." - ERRNO: ".mysql_errno());


       }

       if(is_null($shot)){

            $sql = "UPDATE unit_in_battle SET Save_Request = NULL, Save_Result = NULL, Size = ".$currentSize.", Damage = NULL WHERE FK_Battle = ".$battleID." AND FK_Unit = ".$tosave['target'];
            mysql_query($sql) or die ("could not reset unit_in_battle");

       } else {

        $sql = "UPDATE unit_in_battle SET In_Combat_With = NULL, Save_Request = NULL, Save_Result = NULL, Size = ".$currentSize.", Damage = NULL, Shot = NULL WHERE FK_Battle = ".$battleID." AND FK_Unit = ".$tosave['target'];
            mysql_query($sql) or die ("could not reset unit_in_battle");


       }

       //TODO REMOVE UNITS FROM UNIT IN BATTLE FFS
            
      }
    }

}

/*=========================================================================
 REFRESH IF NOT CURRENT PLAYER (TEST)
===========================================================================*/  
  
if(!($currentPlayer == $_SESSION['ID'])){

  header("Refresh: 5"); 

} else {

  header("Location: manager.php?id=".$battleID);

}




?>


<!DOCTYPE html>
<html lang="en">

<head>
  <script type="text/javascript">
  
  	var dice = new Audio();
  	dice.src = "../Sounds/dice-1.wav"

  </script>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Wait For Your Turn</title>
  <!-- Bootstrap core CSS-->
  <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom fonts for this template-->
  <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
  <!-- Custom styles for this template-->
  <link href="../css/sb-admin.css" rel="stylesheet">
</head>

<body class="fixed-nav sticky-footer bg-dark" id="page-top">
  <!-- Navigation-->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top" id="mainNav">
    <a class="navbar-brand" href="../index.php">ConnecTables</a>
    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarResponsive">
      <ul class="navbar-nav navbar-sidenav" id="exampleAccordion">
        <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Dashboard">
          <a class="nav-link" href="../index.php">
            <i class="fa fa-fw fa-dashboard"></i>
            <span class="nav-link-text">Dashboard</span>
          </a>
        </li>
        <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Charts">
          <a class="nav-link" href="charts.html">
            <i class="fa fa-fw fa-area-chart"></i>
            <span class="nav-link-text">Charts</span>
          </a>
        </li>
        <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Tables">
          <a class="nav-link" href="tables.html">
            <i class="fa fa-fw fa-table"></i>
            <span class="nav-link-text">Tables</span>
          </a>
        </li>
        <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Components">
          <a class="nav-link nav-link-collapse collapsed" data-toggle="collapse" href="#collapseComponents" data-parent="#exampleAccordion">
            <i class="fa fa-fw fa-wrench"></i>
            <span class="nav-link-text">Components</span>
          </a>
          <ul class="sidenav-second-level collapse" id="collapseComponents">
            <li>
              <a href="navbar.html">Navbar</a>
            </li>
            <li>
              <a href="cards.html">Cards</a>
            </li>
          </ul>
        </li>
        <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Example Pages">
          <a class="nav-link nav-link-collapse collapsed" data-toggle="collapse" href="#collapseExamplePages" data-parent="#exampleAccordion">
            <i class="fa fa-fw fa-file"></i>
            <span class="nav-link-text">Example Pages</span>
          </a>
          <ul class="sidenav-second-level collapse" id="collapseExamplePages">
            <li>
              <a href="login.html">Login Page</a>
            </li>
            <li>
              <a href="register.html">Registration Page</a>
            </li>
            <li>
              <a href="forgot-password.html">Forgot Password Page</a>
            </li>
            <li>
              <a href="blank.html">Blank Page</a>
            </li>
          </ul>
        </li>
        <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Menu Levels">
          <a class="nav-link nav-link-collapse collapsed" data-toggle="collapse" href="#collapseMulti" data-parent="#exampleAccordion">
            <i class="fa fa-fw fa-sitemap"></i>
            <span class="nav-link-text">Menu Levels</span>
          </a>
          <ul class="sidenav-second-level collapse" id="collapseMulti">
            <li>
              <a href="#">Second Level Item</a>
            </li>
            <li>
              <a href="#">Second Level Item</a>
            </li>
            <li>
              <a href="#">Second Level Item</a>
            </li>
            <li>
              <a class="nav-link-collapse collapsed" data-toggle="collapse" href="#collapseMulti2">Third Level</a>
              <ul class="sidenav-third-level collapse" id="collapseMulti2">
                <li>
                  <a href="#">Third Level Item</a>
                </li>
                <li>
                  <a href="#">Third Level Item</a>
                </li>
                <li>
                  <a href="#">Third Level Item</a>
                </li>
              </ul>
            </li>
          </ul>
        </li>
        <li class="nav-item" data-toggle="tooltip" data-placement="right" title="Link">
          <a class="nav-link" href="#">
            <i class="fa fa-fw fa-link"></i>
            <span class="nav-link-text">Link</span>
          </a>
        </li>
      </ul>
      <ul class="navbar-nav sidenav-toggler">
        <li class="nav-item">
          <a class="nav-link text-center" id="sidenavToggler">
            <i class="fa fa-fw fa-angle-left"></i>
          </a>
        </li>
      </ul>
      <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle mr-lg-2" id="messagesDropdown" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-fw fa-envelope"></i>
            <span class="d-lg-none">Messages
              <span class="badge badge-pill badge-primary">12 New</span>
            </span>
            <span class="indicator text-primary d-none d-lg-block">
              <i class="fa fa-fw fa-circle"></i>
            </span>
          </a>
          <div class="dropdown-menu" aria-labelledby="messagesDropdown">
            <h6 class="dropdown-header">New Messages:</h6>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#">
              <strong>David Miller</strong>
              <span class="small float-right text-muted">11:21 AM</span>
              <div class="dropdown-message small">Hey there! This new version of SB Admin is pretty awesome! These messages clip off when they reach the end of the box so they don't overflow over to the sides!</div>
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#">
              <strong>Jane Smith</strong>
              <span class="small float-right text-muted">11:21 AM</span>
              <div class="dropdown-message small">I was wondering if you could meet for an appointment at 3:00 instead of 4:00. Thanks!</div>
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#">
              <strong>John Doe</strong>
              <span class="small float-right text-muted">11:21 AM</span>
              <div class="dropdown-message small">I've sent the final files over to you for review. When you're able to sign off of them let me know and we can discuss distribution.</div>
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item small" href="#">View all messages</a>
          </div>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle mr-lg-2" id="alertsDropdown" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-fw fa-bell"></i>
            <span class="d-lg-none">Alerts
              <span class="badge badge-pill badge-warning">6 New</span>
            </span>
            <span class="indicator text-warning d-none d-lg-block">
              <i class="fa fa-fw fa-circle"></i>
            </span>
          </a>
          <div class="dropdown-menu" aria-labelledby="alertsDropdown">
            <h6 class="dropdown-header">New Alerts:</h6>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#">
              <span class="text-success">
                <strong>
                  <i class="fa fa-long-arrow-up fa-fw"></i>Status Update</strong>
              </span>
              <span class="small float-right text-muted">11:21 AM</span>
              <div class="dropdown-message small">This is an automated server response message. All systems are online.</div>
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#">
              <span class="text-danger">
                <strong>
                  <i class="fa fa-long-arrow-down fa-fw"></i>Status Update</strong>
              </span>
              <span class="small float-right text-muted">11:21 AM</span>
              <div class="dropdown-message small">This is an automated server response message. All systems are online.</div>
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#">
              <span class="text-success">
                <strong>
                  <i class="fa fa-long-arrow-up fa-fw"></i>Status Update</strong>
              </span>
              <span class="small float-right text-muted">11:21 AM</span>
              <div class="dropdown-message small">This is an automated server response message. All systems are online.</div>
            </a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item small" href="#">View all alerts</a>
          </div>
        </li>
        <li class="nav-item">
          <form class="form-inline my-2 my-lg-0 mr-lg-2">
            <div class="input-group">
              <input class="form-control" type="text" placeholder="Search for...">
              <span class="input-group-btn">
                <button class="btn btn-primary" type="button">
                  <i class="fa fa-search"></i>
                </button>
              </span>
            </div>
          </form>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-toggle="modal" data-target="#exampleModal">
            <i class="fa fa-fw fa-sign-out"></i>Logout</a>
        </li>
      </ul>
    </div>
  </nav>
  <div class="content-wrapper">
    <div class="container-fluid">
      <!-- Breadcrumbs-->
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
          <a href="../index.php">Game</a>
        </li>
        <li class="breadcrumb-item active">Wait for your turn</li>
      </ol>
      <div class="row">
        <div class="col-12">


        <?php

          //Check if there is any save request to solve

        $sql = "SELECT unit.ID as target, unit_in_battle.In_Combat_With as combat_with, unit_in_battle.Save_Request as saveRequest, unit_in_battle.Save_Result as rend, unit_in_battle.Damage as damage FROM unit_in_battle, battle, player, unit WHERE unit_in_battle.FK_Unit = unit.ID AND unit_in_battle.FK_Player = player.ID AND unit_in_battle.FK_Battle = battle.ID AND battle.ID = ".$battleID." AND player.ID = ".$_SESSION['ID']." AND unit_in_battle.In_Combat_With IS NOT NULL AND unit_in_battle.Save_Request IS NOT NULL AND unit_in_battle.Save_Result IS NOT NULL";

        $result = mysql_query($sql);


        if(!mysql_num_rows($result)){

        ?>

          <h1>Wait for your turn</h1>


          <?php if($totalInsuccess>0){echo "You have lost ".$totalInsuccess." models";} else if(!is_null($totalInsuccess)) {

            echo "You successfully saved all your units";

          } ?>

          <?php
        } else {
          ?>

          <h1>Save your units</h1>

          <form method="POST">


            <?php

            while($row = mysql_fetch_assoc($result)){

              $sql = "SELECT name FROM unit where id = ".$row['target'];
              $resultTarget = mysql_query($sql);
              $targetName = mysql_result($resultTarget,0);

              if($row['combat_with']!=NULL){

            echo "<br>You have to save ".$row['saveRequest']." rolls on ".$targetName. " with ".$row['rend']." rend and ".$row['damage']." damage value";

              }
            }

            ?>

          <button type="submit" class="btn btn-primary btn-block" name="save" onmousedown="dice.play()"> Save 'Em! </button>


          </form>

        <?php
        }
        ?>

          </div>
      </div>
    </div>
    <!-- /.container-fluid-->
    <!-- /.content-wrapper-->
    <footer class="sticky-footer">
      <div class="container">
        <div class="text-center">
          <small>Copyright © connectables.altervista.org 2017</small>
        </div>
      </div>
    </footer>
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
      <i class="fa fa-angle-up"></i>
    </a>
    <!-- Logout Modal-->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">×</span>
            </button>
          </div>
          <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
          <div class="modal-footer">
            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
            <a class="btn btn-primary" href="login.html">Logout</a>
          </div>
        </div>
      </div>
    </div>
    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin.min.js"></script>
  </div>
</body>

</html>

<?php



?>
