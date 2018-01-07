<?php


$username=$usernameErr=$password=$passwordErr=$dbErr="";
$err=false;
//verifico se post già inviata
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   //connessione al db
   
    include_once("db_connection.php");

   //recupero dati post ed effettuo protezione da caratteri speciali
   if (empty($_POST["username"])) {
      $usernameErr = "Il campo Username e' obbligatorio";
      $err=true;
   } else {
        
      $username = $_POST["username"];
      
         
   }
 
   if (empty($_POST["password"])) {
      $passwordErr = "Il campo Password e' obbligatorio";
      $err=true;
   } else {
      $password = $_POST["password"];
   }
   
   //se non ci sono errori, procedo con inserimento nel db
   if(!$err){
      
      /* lettura della tabella utenti */
      $query="SELECT ID, Name FROM player WHERE Name='".$username."' and Password='".$password."'";
      $result=mysql_query($query)  or die ("problemino con la query");
        if($result){
         $count=mysql_num_rows($result);
            if($count!=0){
            session_start();
                $row=mysql_fetch_array($result);
                $_SESSION['session'] = 1;
                $_SESSION['ID'] = $row['ID'];
                $_SESSION['Username'] = $username;
                header("Location: index.php");  //ridirigo utente ad home page
            exit;
         }else{
               $dbErr="Identificazione non riuscita: nome utente o password errati ";
            }
        }else{
         $dbErr="Errore nel login: ".mysql_error();
         
      }
   }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">
  <title>ConnecTables - Login</title>
  <!-- Bootstrap core CSS-->
  <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom fonts for this template-->
  <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
  <!-- Custom styles for this template-->
  <link href="css/sb-admin.css" rel="stylesheet">
</head>

<body class="bg-dark">
  <div class="container">
    <div class="card card-login mx-auto mt-5">
      <div class="card-header">Login</div>
      <div class="card-body">
        <form method="POST">
          <div class="form-group">
            <label for="exampleInputEmail1">Username</label>
            <input class="form-control" id="exampleInputEmail1" name="username" type="text" aria-describedby="emailHelp" placeholder="Enter Username"> 
          </div>
          <div class="form-group">
            <label for="exampleInputPassword1">Password</label>
            <input class="form-control" id="exampleInputPassword1" name="password" type="password" placeholder="Password">
          </div>
          <div class="form-group">
            <div class="form-check">
              <label class="form-check-label">
                <input class="form-check-input" type="checkbox"> Remember Password</label>
            </div>
          </div>
          <button type="submit" class="btn btn-primary btn-block" name="submit">Login</button>
        </form>
        <div class="text-center">
          <a class="d-block small mt-3" href="register.php">Register an Account</a>
          <a class="d-block small" href="forgot-password.html">Forgot Password?</a>
        </div>
      </div>
    </div>
  </div>
  <!-- Bootstrap core JavaScript-->
  <script src="vendor/jquery/jquery.min.js"></script>
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- Core plugin JavaScript-->
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
</body>

</html>
