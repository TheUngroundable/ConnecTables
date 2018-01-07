<?php
session_start();
//cancello le variabili di sessione
session_unset();
//elimino la sessione
session_destroy();
//ridirigo utente ad home page
header("Location: login.php");	
exit;
?>