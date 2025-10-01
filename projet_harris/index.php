<?php
include "conf.php";
/*
if ($bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD)) 
{
    echo "Connexion réussi";
}
else
{
    echo "ERREUR DE CONNEXION BDD";
}
*/
?>
<form method = "post">
login <input type = "text" name = "login">
mot de passe <input type = "password" name = "login">
<input  type = "submit" value = "OK">
</form>
<a href = "oubli.php">Mot de passe oublié</a>