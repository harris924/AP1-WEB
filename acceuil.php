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
    <style>
/* Dark mode variables */
[data-theme="dark"] {
    --primary-gradient: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
    --primary-color: #3b82f6;
    --secondary-color: #06b6d4;
    --text-dark: #f1f5f9;
    --text-light: #94a3b8;
    --glass-bg: rgba(15, 23, 42, 0.8);
    --glass-border: rgba(255, 255, 255, 0.1);
    --bg-light: #0f172a;
    --success-bg: rgba(16, 185, 129, 0.2);
    --success-text: #4ade80;
    --success-border: #10b981;
    --error-bg: rgba(239, 68, 68, 0.2);
    --error-text: #f87171;
    --error-border: #ef4444;
    --shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.3);
    --shadow-sm: 0 4px 15px rgba(59, 130, 246, 0.2);
}

[data-theme="dark"] body {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #1e293b 100%);
    color: var(--text-dark);
}

[data-theme="dark"] a {
    color: var(--primary-color);
}

[data-theme="dark"] a:hover {
    color: var(--secondary-color);
}

/* Theme toggle button */
.theme-toggle {
    background: none;
    border: none;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 50%;
    transition: all 0.3s ease;
    color: #333;
}

.theme-toggle:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: scale(1.1);
}

[data-theme="dark"] .theme-toggle {
    color: #f1f5f9;
}
    </style>
</head>
<body>




<?php
// AJOUT : gestion du bouton Déconnexion simple dans ce même fichier
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php'); 
    exit;
}

include "conf.php";

if (isset($_POST['send_con']))
{
    $bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
    
    $login = mysqli_real_escape_string($bdd, $_POST['login']);
    $password = $_POST['mdp'];

    $requete = "SELECT * FROM utilisateur WHERE login='$login' AND motdepasse='$password'";
    $resultat = mysqli_query($bdd, $requete);
    $trouve = 0;
    
    while($donnees = mysqli_fetch_assoc($resultat))
    {
        $verificationOK = ($password === $donnees['motdepasse']);

        if ($verificationOK) {
            $trouve = 1;
            $_SESSION['id'] = $donnees['num'];
            $_SESSION['login'] = $donnees['login'];
            $_SESSION['type'] = isset($donnees['type']) ? $donnees['type'] : 0;
        }
    }

    if ($trouve == 0)
    {
        echo "ERREUR DE CONNEXION : login/mdp introuvable";  
    }
}

if (isset($_SESSION['id']))
{

    //AFFICHAGE DU MENU COMMUN

    if( $_SESSION['type']==1)
    {
        require "menu_prof.php";
        echo " <br>Vous êtes un prof. ";
        //AFFICHAGE DU MENU ELEVE
    }
    else 
    {
        require "menu_eleve.php";
        echo " <br>Vous êtes un éléve. ";
        //AFFICHAGE DU MENU ELEVE
    }
      echo "Vous êtes connecté en tant que " . $_SESSION['login']."<br>";  


    // AJOUT : lien vers infos perso + bouton déconnexion
    echo "<br><a href='perso.php'>Infos personnelles</a>";
    
}
else
{
	echo "La connexion est perdue, veuillez revenir à la <a href='index.php'>page d'index</a> pour vous reconnecter.";
}
?>

<script>
// Dark mode functionality
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    const html = document.documentElement;

    // Get saved theme from localStorage or default to light
    const savedTheme = localStorage.getItem('theme') || 'light';
    html.setAttribute('data-theme', savedTheme);
    updateToggleIcon(savedTheme);

    // Toggle theme on button click
    themeToggle.addEventListener('click', function() {
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateToggleIcon(newTheme);
    });

    function updateToggleIcon(theme) {
        themeToggle.textContent = theme === 'dark' ? '☀️' : '🌙';
        themeToggle.title = theme === 'dark' ? 'Passer en mode clair' : 'Basculer le mode sombre';
    }
});
</script>

</body>
</html>