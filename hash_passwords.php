<?php
include "conf.php";

$bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
if (!$bdd) {
    die("Erreur de connexion à la base de données");
}

$query = "SELECT id, motdepasse FROM utilisateur WHERE motdepasse IS NOT NULL AND motdepasse != ''";
$result = mysqli_query($bdd, $query);

$updated = 0;
$skipped = 0;

if ($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $password = $row['motdepasse'];
        
        if (strlen($password) === 60 && strpos($password, '$2') === 0) {
            $skipped++;
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $hashed_safe = mysqli_real_escape_string($bdd, $hashed);
            
            $update_query = "UPDATE utilisateur SET motdepasse = '$hashed_safe' WHERE id = '{$row['id']}'";
            
            if (mysqli_query($bdd, $update_query)) {
                $updated++;
                echo "✅ Utilisateur ID {$row['id']} hashé<br>";
            } else {
                echo "❌ Erreur pour ID {$row['id']}: " . mysqli_error($bdd) . "<br>";
            }
        }
    }
}

echo "<br>========== RÉSUMÉ ==========<br>";
echo "✅ Mots de passe hachés: $updated<br>";
echo "⏭️ Déjà hachés: $skipped<br>";

mysqli_close($bdd);
?>
