<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';

$mail = new PHPMailer(true);
?>



Retrouver votre mot de passe

<?php 
include "conf_web.php";
include "conf.php";

// si j'ai envoyé un email 
if (isset($_POST['email']))
{
    $lemail=$_POST['email'];

    // je me connecte a la BDD 
    $bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
    

    // je selectionne l'utilisateur qui a son email et je recupere son mdp
    
    $requete="Select * from utilisateur where email='$lemail'";
    $resultat = mysqli_query($bdd, $requete);
    $mdp=0;
    while($donnees = mysqli_fetch_assoc($resultat))
    {
            $mdp = $donnees['motdepasse'];
    }
    if($mdp==0)//Afficher l'erreur de l'envoie si le mail n'existe pas 
    {
        echo "erreur d'envoie d'email";
    }
    else // si l'utilisateur existe = envoie de l'email 
    {
        echo "envoie de l'email";
        
        
        try {
            // Config SMTP Hostinger
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'contact@sioslam.fr';  // ⚠️ remplace par ton email Hostinger
            $mail->Password   = '&5&Y@*QHb';            // ⚠️ remplace par le mot de passe de cette boîte mail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
            $mail->Port       = 587;

            // Expéditeur
            $mail->setFrom('contact@sioslam.fr', 'CONTACT SIOSLAM');
            // Destinataire
            $mail->addAddress('EMAILACHANGER@email.fr', 'PSEUDOACHANGER'); // ⚠️ remplace par l'email à envoyer

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Mot de passe perdu sioslam CR stage'; // ⚠️ remplace par ton sujet
            $mail->Body    = 'Voici votre mot de passe : $mdp'; // ⚠️ remplace par ton message  
            $mail->AltBody = 'Mon message blablabla';

            $mail->send();
            echo "✅ Email envoyé avec succès !";
        } catch (Exception $e) {
            echo "❌ Erreur d'envoi : {$mail->ErrorInfo}";
        }
        
    }

    // si utilisateur existe > envoie de lemail 


    // sinon afficher erreur l'email nexiste pas 


}
else // sinon pas d'email = premier affichage 
{

?>
<form method = "post">
<input type = "email" name = "email">
<input type = "submit" value = "Confirmer">
</form>
<?php
}
?>