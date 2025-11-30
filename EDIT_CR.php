<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer un Compte Rendu</title>
    <link rel="stylesheet" href="monstyle.css">
</head>
<body>

<?php
include "conf.php";

$bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
if (!$bdd) {
    die("Erreur de connexion à la base de données");
}

$sql_check = "CREATE TABLE IF NOT EXISTS compte_rendu (
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
mysqli_query($bdd, $sql_check);

if (!isset($_SESSION['id'])) {
    echo '<div class="error-message">Vous devez être connecté pour accéder à cette page.</div>';
    echo '<a href="index.php" class="btn">Revenir à la connexion</a>';
    exit;
}

if (!isset($_SESSION['type']) || $_SESSION['type'] != 0) {
    echo '<div class="error-message">Accès refusé. Cette page est réservée aux élèves.</div>';
    echo '<a href="acceuil.php" class="btn">Retour à l\'accueil</a>';
    exit;
}

require "menu_eleve.php";

$user_id = $_SESSION['id'];
$message_success = "";
$message_error = "";
$cr_data = null;

$cr_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($cr_id <= 0) {
    $message_error = "ID du CR invalide.";
} else {
    $query = "SELECT id, date_cr, descriptif FROM compte_rendu WHERE id = '$cr_id' AND num_utilisateur = '$user_id'";
    $result = mysqli_query($bdd, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $cr_data = mysqli_fetch_assoc($result);
    } else {
        $message_error = "CR non trouvé ou accès non autorisé.";
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'update_cr' && $cr_data) {
    $descriptif = isset($_POST['descriptif']) ? trim($_POST['descriptif']) : '';
    
    if (empty($descriptif)) {
        $message_error = "Veuillez entrer un descriptif.";
    } else {
        $descriptif_safe = mysqli_real_escape_string($bdd, $descriptif);
        $query_update = "UPDATE compte_rendu SET descriptif = '$descriptif_safe', date_modification = NOW() WHERE id = '$cr_id'";
        
        if (mysqli_query($bdd, $query_update)) {
            $message_success = "✅ Compte rendu mis à jour avec succès !";
            $cr_data['descriptif'] = $descriptif;
        } else {
            $message_error = "❌ Erreur lors de la mise à jour : " . mysqli_error($bdd);
        }
    }
}

mysqli_close($bdd);
?>

<div class="cr-container">
    <div class="cr-box">
        <h1>Éditer un Compte Rendu</h1>
        
        <?php if ($message_success): ?>
            <div class="success-message">
                <?php echo $message_success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($message_error): ?>
            <div class="error-message">
                <?php echo $message_error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($cr_data): ?>
            <form method="post" class="cr-form">
                <input type="hidden" name="action" value="update_cr">
                
                <div class="form-group">
                    <label for="cr_date">Date du compte rendu</label>
                    <input 
                        type="date" 
                        id="cr_date" 
                        name="cr_date" 
                        value="<?php echo htmlspecialchars($cr_data['date_cr']); ?>" 
                        disabled
                    >
                    <small style="color: #999;">La date ne peut pas être modifiée</small>
                </div>
                
                <div class="form-group">
                    <label for="descriptif">Descriptif</label>
                    <textarea 
                        id="descriptif" 
                        name="descriptif" 
                        rows="12" 
                        placeholder="Décrivez votre compte rendu ici..."
                        required
                    ><?php echo htmlspecialchars($cr_data['descriptif']); ?></textarea>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn-insert">METTRE À JOUR</button>
                    <a href="CR.php" class="btn-cancel">ANNULER</a>
                </div>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 2rem;">
                <p>Impossible de charger le compte rendu.</p>
                <a href="CR.php" class="btn">Retour aux CR</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.btn-cancel {
    display: inline-block;
    padding: 0.7rem 1.5rem;
    background: #999;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    text-align: center;
    flex: 1;
}

.btn-cancel:hover {
    background: #777;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}
</style>

</body>
</html>
