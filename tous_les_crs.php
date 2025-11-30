<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous les Comptes Rendus</title>
    <link rel="stylesheet" href="monstyle.css">

</head>
<body>

<?php
include "conf.php";

if (!isset($_SESSION['id'])) {
    echo '<div class="error-message">Vous devez être connecté pour accéder à cette page.</div>';
    echo '<a href="index.php" class="btn">Revenir à la connexion</a>';
    exit;
}

if (isset($_SESSION['type'])) {
    if ($_SESSION['type'] == 1) {
        require "menu_prof.php";
    } else {
        require "menu_eleve.php";
    }
}

$bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
if (!$bdd) {
    die("Erreur de connexion à la base de données");
}

$sql_check = "CREATE TABLE IF NOT EXISTS commentaire_cr (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_cr INT NOT NULL,
    id_prof INT NOT NULL,
    contenu LONGTEXT NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_cr (id_cr),
    KEY idx_prof (id_prof),
    FOREIGN KEY (id_cr) REFERENCES compte_rendu(id) ON DELETE CASCADE,
    FOREIGN KEY (id_prof) REFERENCES utilisateur(id) ON DELETE CASCADE
)";
mysqli_query($bdd, $sql_check);

$user_id = $_SESSION['id'];
$user_type = isset($_SESSION['type']) ? $_SESSION['type'] : 0;
$crs_list = array();
$filter_eleve = null;
$eleve_login = null;
$message_success = "";
$message_error = "";

if (isset($_POST['action']) && $_POST['action'] === 'add_comment' && $user_type == 1) {
    $cr_id = isset($_POST['cr_id']) ? intval($_POST['cr_id']) : 0;
    $contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';
    
    if ($cr_id <= 0) {
        $message_error = "ID du compte rendu invalide.";
    } elseif (empty($contenu)) {
        $message_error = "Le commentaire ne peut pas être vide.";
    } else {
        $contenu_safe = mysqli_real_escape_string($bdd, $contenu);
        
        $check_existing = mysqli_query($bdd, "SELECT id FROM commentaire_cr WHERE id_cr = '$cr_id' AND id_prof = '$user_id'");
        
        if ($check_existing && mysqli_num_rows($check_existing) > 0) {
            $comment_row = mysqli_fetch_assoc($check_existing);
            $comment_id = $comment_row['id'];
            $update_query = "UPDATE commentaire_cr SET contenu = '$contenu_safe', date_modification = NOW() WHERE id = '$comment_id'";
            
            if (mysqli_query($bdd, $update_query)) {
                $message_success = "✅ Commentaire mis à jour avec succès !";
            } else {
                $message_error = "❌ Erreur lors de la mise à jour : " . mysqli_error($bdd);
            }
        } else {
            $insert_query = "INSERT INTO commentaire_cr (id_cr, id_prof, contenu, date_creation) VALUES ('$cr_id', '$user_id', '$contenu_safe', NOW())";
            
            if (mysqli_query($bdd, $insert_query)) {
                $message_success = "✅ Commentaire ajouté avec succès !";
            } else {
                $message_error = "❌ Erreur lors de l'ajout : " . mysqli_error($bdd);
            }
        }
    }
}

$eleve_filter = isset($_GET['eleve']) ? intval($_GET['eleve']) : null;

if ($user_type == 1) {
    $query = "SELECT cr.id, cr.num_utilisateur, cr.date_cr, cr.descriptif, cr.date_creation, cr.date_modification, u.login FROM compte_rendu cr JOIN utilisateur u ON cr.num_utilisateur = u.id";
    
    if ($eleve_filter) {
        $query .= " WHERE cr.num_utilisateur = '$eleve_filter'";
        
        $eleve_check = mysqli_query($bdd, "SELECT login FROM utilisateur WHERE id = '$eleve_filter' AND type = 0");
        if ($eleve_check && mysqli_num_rows($eleve_check) > 0) {
            $eleve_row = mysqli_fetch_assoc($eleve_check);
            $eleve_login = $eleve_row['login'];
        }
    }
    
    $query .= " ORDER BY cr.date_cr DESC";
    
} else {
    $query = "SELECT cr.id, cr.num_utilisateur, cr.date_cr, cr.descriptif, cr.date_creation, cr.date_modification, u.login FROM compte_rendu cr JOIN utilisateur u ON cr.num_utilisateur = u.id WHERE cr.num_utilisateur = '$user_id' ORDER BY cr.date_cr DESC";
}

$result = mysqli_query($bdd, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $comment_query = "SELECT id, contenu, date_modification FROM commentaire_cr WHERE id_cr = '" . $row['id'] . "' AND id_prof = '$user_id' LIMIT 1";
        $comment_result = mysqli_query($bdd, $comment_query);
        
        $row['commentaire'] = null;
        if ($comment_result && mysqli_num_rows($comment_result) > 0) {
            $row['commentaire'] = mysqli_fetch_assoc($comment_result);
        }
        
        $crs_list[] = $row;
    }
}

$eleves_for_filter = array();
if ($user_type == 1) {
    $eleves_query = "SELECT id, login FROM utilisateur WHERE type = 0 ORDER BY login ASC";
    $eleves_result = mysqli_query($bdd, $eleves_query);
    
    if ($eleves_result && mysqli_num_rows($eleves_result) > 0) {
        while ($row = mysqli_fetch_assoc($eleves_result)) {
            $eleves_for_filter[] = $row;
        }
    }
}

mysqli_close($bdd);
?>

<div class="all-crs-container">
    <div class="all-crs-box">
        <h1>📚 <?php echo $user_type == 1 ? 'Tous les Comptes Rendus' : 'Mes Comptes Rendus'; ?></h1>
        
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
        
        <?php if ($user_type == 1): ?>
            <div class="filter-section">
                <form method="get" class="filter-form">
                    <label for="eleve">Filtrer par élève :</label>
                    <select id="eleve" name="eleve">
                        <option value="">-- Tous les élèves --</option>
                        <?php foreach ($eleves_for_filter as $eleve): ?>
                            <option value="<?php echo $eleve['id']; ?>" <?php echo ($eleve_filter == $eleve['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($eleve['login']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn">Filtrer</button>
                    <?php if ($eleve_filter): ?>
                        <a href="tous_les_crs.php" class="btn-reset">Réinitialiser</a>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>
        
        <?php if ($eleve_login): ?>
            <div class="filter-info">
                <p>Comptes rendus de <strong><?php echo htmlspecialchars($eleve_login); ?></strong> (<?php echo count($crs_list); ?> CR)</p>
            </div>
        <?php endif; ?>
        
        <?php if (empty($crs_list)): ?>
            <div class="no-cr-message">
                <p><?php echo $user_type == 1 ? 'Aucun compte rendu trouvé.' : 'Vous n\'avez pas encore créé de compte rendu.'; ?></p>
                <?php if ($user_type == 0): ?>
                    <p><a href="NEW_CR.php" class="link">Créer un nouveau CR</a></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="crs-items-container">
                <?php foreach ($crs_list as $cr): ?>
                    <div class="cr-item-prof">
                        <div class="cr-header-prof">
                            <div class="cr-title">
                                <h3><?php echo htmlspecialchars(date('d/m/Y', strtotime($cr['date_cr']))); ?></h3>
                                <?php if ($user_type == 1): ?>
                                    <span class="eleve-name">par <strong><?php echo htmlspecialchars($cr['login']); ?></strong></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="cr-content-prof">
                            <p><?php echo nl2br(htmlspecialchars($cr['descriptif'])); ?></p>
                        </div>
                        
                        <div class="cr-footer-prof">
                            <small>Créé le : <?php echo htmlspecialchars(date('d/m/Y à H:i', strtotime($cr['date_creation']))); ?></small>
                            <?php if ($cr['date_modification'] != $cr['date_creation']): ?>
                                <small style="margin-left: 1rem;">Modifié le : <?php echo htmlspecialchars(date('d/m/Y à H:i', strtotime($cr['date_modification']))); ?></small>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($user_type == 1): ?>
                            <div class="comment-section">
                                <button type="button" class="comment-toggle" onclick="toggleComment(this)">
                                    <div class="comment-header">
                                        <h4>💬 Commentaire</h4>
                                        <?php if ($cr['commentaire']): ?>
                                            <span class="comment-status commented">Commenté</span>
                                        <?php else: ?>
                                            <span class="comment-status">Sans commentaire</span>
                                        <?php endif; ?>
                                        <span class="toggle-icon">▼</span>
                                    </div>
                                </button>
                                
                                <div class="comment-content-wrapper">
                                    <?php if ($cr['commentaire']): ?>
                                        <div class="existing-comment">
                                            <div class="comment-content">
                                                <?php echo $cr['commentaire']['contenu']; ?>
                                            </div>
                                            <small class="comment-date">Modifié le : <?php echo htmlspecialchars(date('d/m/Y à H:i', strtotime($cr['commentaire']['date_modification']))); ?></small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <form method="post" class="comment-form">
                                        <input type="hidden" name="action" value="add_comment">
                                        <input type="hidden" name="cr_id" value="<?php echo $cr['id']; ?>">
                                        <textarea 
                                            id="comment_<?php echo $cr['id']; ?>" 
                                            name="contenu" 
                                            rows="6"
                                            placeholder="Ajoutez votre commentaire..."
                                            required
                                        ><?php echo $cr['commentaire'] ? htmlspecialchars($cr['commentaire']['contenu']) : ''; ?></textarea>
                                        <button type="submit" class="btn-comment">Enregistrer le commentaire</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.all-crs-container {
    display: flex;
    justify-content: center;
    padding: 2rem;
}

.all-crs-box {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    width: 100%;
    max-width: 1000px;
}

.all-crs-box h1 {
    text-align: center;
    color: #667eea;
    margin-bottom: 2rem;
}

.filter-section {
    background: #f0f4ff;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
}

.filter-form {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.filter-form label {
    font-weight: 600;
    color: #333;
}

.filter-form select {
    padding: 0.6rem 1rem;
    border: 2px solid #667eea;
    border-radius: 6px;
    font-family: inherit;
    font-size: 1rem;
    background: white;
    cursor: pointer;
}

.filter-form select:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-reset {
    display: inline-block;
    padding: 0.6rem 1rem;
    background: #999;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
}

.btn-reset:hover {
    background: #666;
}

.filter-info {
    background: #e5ffe5;
    border-left: 4px solid #2ecc71;
    color: #2d5016;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.filter-info p {
    margin: 0;
    font-weight: 500;
}

.crs-items-container {
    display: grid;
    gap: 1.5rem;
    margin: 2rem 0;
}

.cr-item-prof {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    transition: all 0.3s ease;
    border-left: 4px solid #667eea;
}

.cr-item-prof:hover {
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    border-left-color: #764ba2;
}

.cr-header-prof {
    margin-bottom: 1rem;
}

.cr-title {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.cr-title h3 {
    margin: 0;
    color: #667eea;
    font-size: 1.2rem;
}

.eleve-name {
    color: #666;
    font-size: 0.9rem;
}

.cr-content-prof {
    margin: 1rem 0;
    color: #555;
    line-height: 1.6;
    background: white;
    padding: 1rem;
    border-radius: 6px;
    max-height: 300px;
    overflow-y: auto;
}

.cr-footer-prof {
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.cr-footer-prof small {
    color: #999;
    font-size: 0.85rem;
}

.no-cr-message {
    text-align: center;
    padding: 3rem 1rem;
    color: #999;
}

.no-cr-message a.link {
    color: #667eea;
    font-weight: 600;
    text-decoration: none;
}

.no-cr-message a.link:hover {
    color: #764ba2;
}

.comment-section {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid #e0e0e0;
}

.comment-toggle {
    width: 100%;
    background: none;
    border: none;
    cursor: pointer;
    text-align: left;
    padding: 0;
    margin: 0;
}

.comment-toggle:hover {
    opacity: 0.8;
}

.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0;
}

.comment-header h4 {
    margin: 0;
    color: #333;
    font-size: 1rem;
}

.toggle-icon {
    font-size: 0.9rem;
    transition: transform 0.3s ease;
    color: #667eea;
}

.comment-toggle[data-open="false"] .toggle-icon {
    transform: rotate(-90deg);
}

.comment-content-wrapper {
    overflow: hidden;
    transition: all 0.3s ease;
    max-height: 1000px;
    padding-top: 1rem;
}

.comment-content-wrapper[data-open="false"] {
    max-height: 0;
    opacity: 0;
    padding-top: 0;
}

.comment-status {
    display: inline-block;
    padding: 0.3rem 0.8rem;
    background: #fee;
    color: #c33;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.comment-status.commented {
    background: #efe;
    color: #3c3;
}

.existing-comment {
    background: #f5f5f5;
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1rem;
}

.comment-content {
    color: #555;
    line-height: 1.6;
    margin-bottom: 0.5rem;
}

.comment-date {
    color: #999;
    font-size: 0.85rem;
}

.comment-form {
    display: flex;
    flex-direction: column;
    gap: 0.8rem;
}

.comment-form textarea {
    padding: 0.8rem;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-family: inherit;
    font-size: 1rem;
}

.comment-form textarea:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-comment {
    padding: 0.7rem 1.5rem;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.95rem;
}

.btn-comment:hover {
    background: #764ba2;
    transform: translateY(-2px);
}

.success-message {
    background: #efe;
    border-left: 4px solid #3c3;
    color: #2d5016;
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
    animation: fadeIn 0.3s ease;
}

.error-message {
    background: #fee;
    border-left: 4px solid #c33;
    color: #c33;
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-out {
    animation: fadeOut 0.5s ease forwards;
}

@keyframes fadeOut {
    to {
        opacity: 0;
        transform: translateY(-10px);
    }
}
</style>

<script>
function toggleComment(button) {
    const wrapper = button.nextElementSibling;
    const isOpen = wrapper.getAttribute('data-open') !== 'false';
    wrapper.setAttribute('data-open', isOpen ? 'false' : 'true');
    button.setAttribute('data-open', isOpen ? 'false' : 'true');
}

document.addEventListener('DOMContentLoaded', function() {
    const commentToggles = document.querySelectorAll('.comment-toggle');
    commentToggles.forEach(toggle => {
        toggle.setAttribute('data-open', 'false');
    });
    
    const wrappers = document.querySelectorAll('.comment-content-wrapper');
    wrappers.forEach(wrapper => {
        wrapper.setAttribute('data-open', 'false');
    });
    
    const successMsg = document.querySelector('.success-message');
    if (successMsg) {
        setTimeout(function() {
            successMsg.classList.add('fade-out');
            setTimeout(function() {
                successMsg.style.display = 'none';
            }, 500);
        }, 4000);
    }
});
</script>

</body>
</html>
