<?php
include "conf.php";

$bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
if (!$bdd) {
    die("Erreur de connexion à la base de données");
}

$sql = "CREATE TABLE IF NOT EXISTS compte_rendu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    num_utilisateur INT NOT NULL,
    date_cr DATE NOT NULL,
    descriptif LONGTEXT NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_date (num_utilisateur, date_cr),
    KEY idx_utilisateur (num_utilisateur),
    KEY idx_date (date_cr)
)";

if (mysqli_query($bdd, $sql)) {
    echo "✅ Table compte_rendu créée ou vérifiée avec succès!";
} else {
    echo "❌ Erreur lors de la création/vérification de la table: " . mysqli_error($bdd);
}

mysqli_close($bdd);
?>
