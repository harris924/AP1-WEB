<?php
session_start();
include "conf.php";

if(isset($_POST["send_deco"]))
{
    session_destroy();
    echo "Déconnexion réussie";
}

if (isset($_POST['send_con']))
{
    $bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
    
    $login = trim(mysqli_real_escape_string($bdd, $_POST['login']));
    $mdp = trim($_POST['mdp']);
    $mdpHash = md5($mdp);

    $requete = "SELECT * FROM utilisateur WHERE login='$login' AND motdepasse='$mdpHash'";
    $resultat = mysqli_query($bdd, $requete);
    $trouve = 0;
    
    while($donnees = mysqli_fetch_assoc($resultat))
    {
        if ($mdpHash === $donnees['motdepasse']) {
            $trouve = 1;
            $id_key = isset($donnees['num']) ? 'num' : (isset($donnees['id']) ? 'id' : null);
            if ($id_key) {
                $_SESSION['id'] = $donnees[$id_key];
            }
            $_SESSION['login'] = $donnees['login'];
            $_SESSION['type'] = isset($donnees['type']) ? $donnees['type'] : 0;
            header('Location: acceuil.php');
            exit;
        }
    }

    if ($trouve == 0)
    {
        $erreur_connexion = "ERREUR DE CONNEXION : login/mdp introuvable";
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SIOSLAM</title>
    <link rel="stylesheet" href="monstyle.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>CONNEXION</h1>
            <?php if (isset($erreur_connexion)): ?>
                <div class="error-message"><?php echo $erreur_connexion; ?></div>
            <?php endif; ?>
            <form method="post">
                <label for="login">Identifiant</label>
                <input type="text" id="login" name="login" required placeholder="Entrez votre identifiant">
                
                <label for="mdp">Mot de passe</label>
                <input type="password" id="mdp" name="mdp" required placeholder="Entrez votre mot de passe">
                
                <input type="submit" name="send_con" value="Connexion">
            </form>
            <div class="forgot-password">
                <a href="oubli.php">Mot de passe oublié ?</a>
            </div>
        </div>
    </div>
</body>
</html>