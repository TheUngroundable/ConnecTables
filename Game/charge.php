<?php

/*=========================================================================
SESSION CONTROL
===========================================================================*/
  
  session_start();

    include_once("../db_connection.php");

    include("dicesTool.php");

    if(!isset($_SESSION['session'])){ 

        header("Location: ../login.php");  

    }


/*=========================================================================
GETTERS $_GET[];
===========================================================================*/

  $battleID = $_GET['id'];
    
/*=========================================================================
GET OWNER BY ID IN BATTLE
===========================================================================*/

  $sql = "SELECT player.ID as owner from player, battle WHERE battle.FK_Player = player.ID AND battle.ID = ".$battleID;
  $result = mysql_query($sql);
  $temp = mysql_fetch_assoc($result);
  $ownerID = $temp['owner'];

/*=========================================================================
GET LAST ROUND FOR THIS BATTLE
===========================================================================*/

  $sql = "Select round.ID as round, turn.FK_Phase as phase, turn.FK_Player as player, phase_type.type as type from battle, round, turn, phase_type, player
WHERE round.FK_Battle = battle.ID AND round.FK_Turn = turn.ID AND turn.FK_Phase = phase_type.ID AND turn.FK_Player = player.ID AND battle.ID = ".$battleID." ORDER BY round.FK_Turn DESC LIMIT 1";

  $result = mysql_query($sql);
  $row = mysql_fetch_assoc($result);
  $typeForRedirect = $row['type'];

  $phase = $row['phase'];
  $currentPlayer = $row['player'];
  $round = $row['round'];


/*=========================================================================
 REFRESH IF NOT CURRENT PLAYER (TEST)
===========================================================================*/  
  
  $uri = basename($_SERVER['PHP_SELF']);

  
  //If you press for next turn
  if(isset($_POST['submit'])){

  header("Location: manager.php?id=".$battleID."&permission=1");  

  }

  //If you press for hitting
 if(isset($_POST['charge'])){

    $source = array_values($_POST['source']);
    $target = array_values($_POST['target']);


    $dicesResult = array();

      for($i = 0; $i<count($target); $i++){

        $chargeResult = charge();
        
        array_push($dicesResult, $chargeResult);
      }


      for($i = 0; $i<count($dicesResult); $i++){

          if($target[$i] != '0'){

            //get the distance between source and target. if geq charge roll, you charged else failed charge

            $sql ="SELECT Distance from distances WHERE FK_Battle=".$battleID." AND FK_Player=".$_SESSION['ID']." AND FK_Source =".$source[$i]." AND FK_Target =".$target[$i];

            $result = mysql_query($sql);
            $distanceForCheck = mysql_result($result,0);

            if($dicesResult[$i] >= $distanceForCheck){

              //hook in combat with source

              $sql = "UPDATE unit_in_battle SET In_Combat_With = ".$source[$i]." WHERE FK_Battle = ".$battleID." AND FK_Unit = ".$target[$i];
              mysql_query($sql) or die ("Could not hook in combat with source");


              //hook in combat with target

              $sql = "UPDATE unit_in_battle SET In_Combat_With = ".$target[$i]." WHERE FK_Battle = ".$battleID." AND FK_Unit = ".$source[$i];
              mysql_query($sql) or die ("Could not update hook in combat with target");

            }


            
            
          }

          
        }

        $succeded = true;
      
  }

  


?>


<!DOCTYPE html>
<html lang="en">

<head>
  <script type="text/javascript">
          
          var bleep = new Audio();
          bleep.src = "../Sounds/Click_04.mp3";
          var dice = new Audio();
          dice.src = "../Sounds/dice-12.wav"

  </script>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>Charge Phase</title>
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
        <li class="breadcrumb-item active">Charge</li>
      </ol>
      <div class="row">
        <div class="col-12">
          <h1>Charge Phase - Round: <?php echo $round; ?></h1>

          

<form method="POST">

        <div class="form-group">

        <?php

        $sql = "SELECT unit.ID as Sourced, unit.Name as unitName, weapons.range as ranged, unit_in_battle.size FROM battle, player , army_in_battle, army, unit, hasweapons, weapons, weapon_type, unit_in_battle WHERE unit_in_battle.FK_Battle = battle.ID AND unit_in_battle.FK_Player = player.ID AND unit_in_battle.FK_Unit = unit.ID AND army_in_battle.FK_Battle = battle.ID AND army_in_battle.FK_Army = army.ID AND army_in_battle.FK_Player = player.ID AND unit.FK_Army = army.ID AND hasweapons.FK_Unit = unit.ID AND hasweapons.FK_Weapon = weapons.ID AND weapons.FK_Type = weapon_type.ID AND battle.ID = ".$battleID." AND player.ID =".$_SESSION['ID']." AND weapon_type.ID = 2 AND unit_in_battle.ran = 0 AND unit_in_battle.size > 0 AND unit_in_battle.In_Combat_With IS NULL";

        //$sql= "SELECT unit.ID as Sourced,unit.Name as unitName, weapons.range as ranged , unit_in_battle.size as size from unit_in_battle, unit, hasweapons, weapons, weapon_type, player, battle WHERE unit_in_battle.FK_Battle = battle.ID AND unit_in_battle.FK_Player = player.ID AND unit_in_battle.FK_Unit = unit.ID AND hasweapons.FK_Unit = unit.ID AND hasweapons.FK_Weapon = weapons.ID and weapons.FK_Type = weapon_type.ID AND battle.ID = ".$battleID." AND player.ID =".$_SESSION['ID']." AND weapon_type.ID= 1 AND unit_in_battle.ran = 0 AND unit_in_battle.size > 0";

        $resultSources = mysql_query($sql);


        if(!mysql_num_rows($resultSources)){

          $chargingUnits = false;

        } else {

          $chargingUnits = true;

        } 

        if(!$chargingUnits){


        ?>


      <h3 for="ranBoxes">You don't have available charging Units</h3>



         
        <?php 

        } else {

        ?>

 <h3 for="ranBoxes">Select charging Units:</h3>

        
        <?php

        

        while($row = mysql_fetch_array($resultSources)){

          ?>
    
          <input class="form-control" type="hidden" name="source[]" value=<?php echo "'".$row['Sourced']."'"; ?> readonly><label> <?php echo $row['size'].", ".$row['unitName']; ?></label>

          <?

          //$sql ="SELECT unit.Name as Target, unit.ID as TargetID FROM distances, unit where FK_Target = unit.ID AND FK_Source = ".$row['Sourced']." AND FK_Battle = ".$battleID." AND FK_Player = ".$_SESSION['ID']." AND Distance < ".$row['ranged'];

          $sql = "SELECT DISTINCT unit.Name as Target, unit.ID as TargetID FROM distances, unit, unit_in_battle, battle,player WHERE distances.FK_Battle = battle.ID AND distances.FK_Player = player.ID AND unit_in_battle.FK_Battle = battle.ID AND unit_in_battle.FK_Unit = unit.ID AND unit_in_battle.FK_Player = player.ID AND battle.ID =".$battleID." AND unit_in_battle.size > 0 AND player.ID !=".$_SESSION['ID']." AND distances.Distance < 12 AND unit_in_battle.In_Combat_With IS NULL";

          //$sql = "SELECT unit.Name as Target, unit.ID as TargetID FROM `distances`, battle, unit, player WHERE distances.FK_Player = player.ID AND distances.FK_Target = unit.ID AND distances.FK_Battle = battle.ID AND distances.FK_Source = ".$row['Sourced']." AND player.ID <> ".$_SESSION['ID']." AND battle.ID = ".$battleID." AND distances.Distance < ".$row['ranged'];

          $result = mysql_query($sql);
          

          ?>
        <select class="form-control" id="exampleInputEmail1" name="target[]" aria-describedby="emailHelp" <?php if($succeded){ echo "disabled";} ?>>
          <option value="0">Target</option>

          <?php

          while($datas = mysql_fetch_array($result)){

            $sql ="SELECT Distance from distances WHERE FK_Battle=".$battleID." AND FK_Player=".$_SESSION['ID']." AND FK_Source =".$row['Sourced']." AND FK_Target =".$datas['TargetID'];

            $distanceResult = mysql_query($sql);
            $distanced = mysql_result($distanceResult,0);

            ?>

            <option value=<?php echo "'".$datas['TargetID']."'"; ?>><?php echo $datas['Target'].", ".$distanced." Inches"; ?></option>

            <?php

          }

          ?>


        </select>

        

        <?php

        }
        
        

        ?> 
        </div>

        <button type="submit" class="btn btn-primary btn-block" name="charge" onmousedown="dice.play()" <?php if($succeded){ echo "disabled";} ?>> Charge 'em! </button>

        <?php

    }
        /*foreach ($dicesResult as $key => $value) {
          echo "<br>value: ".$value;
        }

/*        echo $temp_Hit;
        echo "<br>".$temp_Wound;

        /

        foreach ($source as $key => $value) {
          echo "source: ".$value;
        }*/

        for($i = 0; $i<count($dicesResult); $i++){

          if($target[$i] != '0'){

            //get the distance between source and target. if geq charge roll, you charged else failed charge

            $sql ="SELECT Distance from distances WHERE FK_Battle=".$battleID." AND FK_Player=".$_SESSION['ID']." AND FK_Source =".$source[$i]." AND FK_Target =".$target[$i];

            $result = mysql_query($sql);
            $distanceForCheck = mysql_result($result,0);


            $sql = "SELECT name FROM unit where id = ".$target[$i];
            $result = mysql_query($sql);
            $targetName = mysql_result($result,0);

            $sql = "SELECT name FROM unit where id = ".$source[$i];
            $result = mysql_query($sql);
            $sourceName = mysql_result($result,0);


            if($dicesResult[$i] >= $distanceForCheck){

              echo "<br> You successfully charged ".$dicesResult[$i]." inches on ".$targetName." with: ".$sourceName;


            } else {

              echo "<br> You failed charged ".$dicesResult[$i]." inches on ".$targetName." with: ".$sourceName;


            }


          }

          
        }

        ?>

        <button type="submit" class="btn btn-primary btn-block" name="submit" onmousedown="bleep.play()">Next Phase</button>

          </form>

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
