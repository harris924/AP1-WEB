<?php 
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="monstyle.css">
</head>
<body>

<?php
include "conf.php";

if (isset($_SESSION['id']))
{
    if( $_SESSION['type']==1)
    {
        require "menu_prof.php";
    }
    else 
    {
        require "menu_eleve.php";
    }
    
    echo '<div class="profile-content">';
    echo '<h1>Mon Profil</h1>';
    echo '<p>Connecté en tant que : <strong>' . htmlspecialchars($_SESSION['login']) . '</strong></p>';
    echo '<p>Type de compte : <strong>' . ($_SESSION['type']==1 ? 'Professeur' : 'Élève') . '</strong></p>';
    echo '<br><a href="perso.php" class="btn">✏️ Modifier mes informations</a>';
    echo '</div>';
}
else
{
	echo '<div class="error-message">La connexion est perdue. <a href="index.php">Cliquez ici pour vous reconnecter</a>.</div>';
}

?>
