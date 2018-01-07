<?php

    include_once("../db_connection.php");


    $battleID = $_GET['id'];

     if (empty($_GET['psw'])) {
           
       $password = null;
   
     } else {
             
       $password = $_GET['psw']; 
              
     }


     /*=========================================================================
CHECK IF ANY BATTLE EXISTS WITH THIS ID
===========================================================================*/   
     $sql = "SELECT * from battle where battle.id = ".$battleID;
     $result = mysql_query($sql) or die ("Cannot find matches with this id");
   
     if(!mysql_num_rows($result)){
   
      $matchError = true;

     }

/*=========================================================================
CHECK IF PASSWORD MATCHES
===========================================================================*/

      $sql = "SELECT * from battle where battle.id = '".$battleID."' AND battle.password = '".$password."'";
      $result = mysql_query($sql) or die ("Cannot test if password is correct");

      if(!mysql_num_rows($result)){

        $matchError = true;

      }


/*=========================================================================
IF EVERYTHING IS OK
===========================================================================*/

      if(!$matchError){

        echo "OK";


      } else {

      	echo "ERROR";

      }
   
   
   
   


?>