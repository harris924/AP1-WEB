<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Comptes Rendus</title>
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
$message_error = "";
$crs_list = array();

$query = "SELECT id, date_cr, descriptif, date_creation, date_modification FROM compte_rendu WHERE num_utilisateur = '$user_id' ORDER BY date_cr DESC";
$result = mysqli_query($bdd, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $crs_list[] = $row;
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'delete_cr') {
    $cr_id = isset($_POST['cr_id']) ? intval($_POST['cr_id']) : 0;
    
    if ($cr_id > 0) {
        $check_query = "SELECT id FROM compte_rendu WHERE id = '$cr_id' AND num_utilisateur = '$user_id'";
        $check_result = mysqli_query($bdd, $check_query);
        
        if ($check_result && mysqli_num_rows($check_result) > 0) {
            $delete_query = "DELETE FROM compte_rendu WHERE id = '$cr_id'";
            
            if (mysqli_query($bdd, $delete_query)) {
                header("Location: CR.php");
                exit;
            } else {
                $message_error = "❌ Erreur lors de la suppression.";
            }
        }
    }
}

mysqli_close($bdd);
?>

<div class="cr-list-container">
    <div class="cr-list-box">
        <h1>📋 Mes Comptes Rendus</h1>
        
        <?php if ($message_error): ?>
            <div class="error-message">
                <?php echo $message_error; ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($crs_list)): ?>
            <div class="no-cr-message">
                <p>Aucun compte rendu trouvé. <a href="NEW_CR.php">Créer un nouveau CR</a></p>
            </div>
        <?php else: ?>
            <div class="cr-items-container">
                <?php foreach ($crs_list as $cr): ?>
                    <div class="cr-item">
                        <div class="cr-header">
                            <h3><?php echo htmlspecialchars(date('d/m/Y', strtotime($cr['date_cr']))); ?></h3>
                            <div class="cr-actions">
                                <a href="EDIT_CR.php?id=<?php echo urlencode($cr['id']); ?>" class="btn-edit">✏️ Éditer</a>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce CR ?');">
                                    <input type="hidden" name="action" value="delete_cr">
                                    <input type="hidden" name="cr_id" value="<?php echo $cr['id']; ?>">
                                    <button type="submit" class="btn-delete">🗑️ Supprimer</button>
                                </form>
                            </div>
                        </div>
                        <div class="cr-content">
                            <p><?php echo nl2br(htmlspecialchars(substr($cr['descriptif'], 0, 200))); ?><?php echo strlen($cr['descriptif']) > 200 ? '...' : ''; ?></p>
                        </div>
                        <div class="cr-footer">
                            <small>Créé le : <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($cr['date_creation']))); ?></small>
                            <?php if ($cr['date_modification'] != $cr['date_creation']): ?>
                                <small style="margin-left: 1rem;">Modifié le : <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($cr['date_modification']))); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 2rem;">
            <a href="NEW_CR.php" class="btn">Créer un nouveau CR</a>
        </div>
    </div>
</div>

<style>
.cr-list-container {
    display: flex;
    justify-content: center;
    padding: 2rem;
}

.cr-list-box {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    width: 100%;
    max-width: 900px;
}

.cr-items-container {
    display: grid;
    gap: 1.5rem;
    margin: 2rem 0;
}

.cr-item {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    border-left: 4px solid #667eea;
}

.cr-item:hover {
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    border-left-color: #764ba2;
}

.cr-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.cr-header h3 {
    margin: 0;
    color: #667eea;
    font-size: 1.2rem;
}

.cr-actions {
    display: flex;
    gap: 0.5rem;
}

.btn-edit, .btn-delete {
    display: inline-block;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    text-decoration: none;
    font-family: inherit;
}

.btn-edit {
    background: #667eea;
    color: white;
}

.btn-edit:hover {
    background: #764ba2;
    transform: translateY(-1px);
}

.btn-delete {
    background: #f5576c;
    color: white;
}

.btn-delete:hover {
    background: #d63447;
    transform: translateY(-1px);
}

.cr-content {
    margin: 1rem 0;
    color: #555;
    line-height: 1.6;
}

.cr-footer {
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.cr-footer small {
    color: #999;
    font-size: 0.85rem;
}

.no-cr-message {
    text-align: center;
    padding: 3rem 1rem;
    color: #999;
}

.no-cr-message a {
    color: #667eea;
    font-weight: 600;
}
</style>

</body>
</html>
