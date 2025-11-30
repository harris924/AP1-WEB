<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/phpmailer/Exception.php';
require __DIR__ . '/phpmailer/PHPMailer.php';
require __DIR__ . '/phpmailer/SMTP.php';

$mail = new PHPMailer(true);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récupérer mot de passe</title>
    <link rel="stylesheet" href="monstyle.css">
</head>
<body>
    <div class="recovery-container">
        <div class="recovery-box">
            <h1>Récupérer votre mot de passe</h1>

<?php 
include  "conf.php"; 

function chaineAleatoireSpeciale(int $longueur = 10): string {
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+[]{};:,.?<>/|`~\'"\\';
    $maxIndex = strlen($alphabet) - 1;
    $res = '';

    for ($i = 0; $i < $longueur; $i++) {
        $res .= $alphabet[random_int(0, $maxIndex)];
    }
    return $res;
}


// si j'ai envoyé un login 
if (isset($_POST['login']))
{
    $login=$_POST['login'];

    // je me connecte a la BDD 
    $bdd=mysqli_connect($serveurBDD,$userBDD,$mdpBDD,$nomBDD);
    

    // je selectionne l'utilisateur qui a son login et je recupere son mdp  
    $requete="Select * from utilisateur where login='".mysqli_real_escape_string($bdd, $login)."'";
    $resultat = mysqli_query($bdd, $requete);
    $mdp=0;
    while($donnees = mysqli_fetch_assoc($resultat))
    {
            $mdp = $donnees['motdepasse'];
    }
    if($mdp==0) {
        echo '<div class="error-message">Erreur : Ce login n\'existe pas dans notre système.</div>';
    }
    else {
        $newmdp = chaineAleatoireSpeciale(10);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'contact@siolapie.com';
            $mail->Password   = 'EmailL@pie25';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port       = 587;

            $mail->setFrom('contact@siolapie.com', 'CONTACT SIOSLAM');
            $mail->addAddress($login, 'SIOSLAM User');

            $mail->isHTML(true);
            $mail->Subject = 'Réinitialisation de mot de passe - SIOSLAM';
            $mail->Body    = '<p>Bonjour,</p><p>Votre nouveau mot de passe est : <strong>'.$newmdp.'</strong></p><p>Pensez à le changer immédiatement sur votre compte pour plus de sécurité.</p>';
            $mail->AltBody = 'Votre nouveau mot de passe est : '.$newmdp;
            
            $mail->send();
            
            $mdphash = md5($newmdp);
            $requete2="UPDATE `utilisateur` SET `motdepasse` = '".mysqli_real_escape_string($bdd, $mdphash)."' WHERE `utilisateur`.`login` = '".mysqli_real_escape_string($bdd, $login)."'";
            if (!mysqli_query($bdd,$requete2)) {
                echo '<div class="error-message">Erreur : '.mysqli_error($bdd).'</div>';
            }
            
            echo '<div class="success-message">✅ Email envoyé avec succès ! Vérifiez votre boîte mail.</div>';
        } catch (Exception $e) {
            echo '<div class="error-message">❌ Erreur d\'envoi : '.$mail->ErrorInfo.'</div>';
        }
    }
}
else {
?>
            <form method="post">
                <label for="login">Login</label>
                <input type="text" id="login" name="login" required placeholder="Entrez votre login">
                <input type="submit" value="Valider">
            </form>
<?php
}
?>
            <div class="back-link">
                <a href="index.php">← Retour à la connexion</a>
            </div>
        </div>
    </div>
</body>
</html>   