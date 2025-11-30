<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Compte Rendu</title>
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
$form_date = date('Y-m-d');
$form_descriptif = "";

if (isset($_POST['action']) && $_POST['action'] === 'save_cr') {
    $cr_date = isset($_POST['cr_date']) ? $_POST['cr_date'] : '';
    $descriptif = isset($_POST['descriptif']) ? trim($_POST['descriptif']) : '';
    
    if (empty($cr_date)) {
        $message_error = "Veuillez sélectionner une date.";
    } elseif (empty($descriptif)) {
        $message_error = "Veuillez entrer un descriptif.";
    } else {
        $cr_date_safe = mysqli_real_escape_string($bdd, $cr_date);
        $descriptif_safe = mysqli_real_escape_string($bdd, $descriptif);
        
        $query_check = "SELECT id FROM compte_rendu WHERE num_utilisateur = '$user_id' AND date_cr = '$cr_date_safe'";
        $result_check = mysqli_query($bdd, $query_check);
        
        if ($result_check && mysqli_num_rows($result_check) > 0) {
            $cr_row = mysqli_fetch_assoc($result_check);
            $cr_id = $cr_row['id'];
            $query_update = "UPDATE compte_rendu SET descriptif = '$descriptif_safe', date_modification = NOW() WHERE id = '$cr_id'";
            
            if (mysqli_query($bdd, $query_update)) {
                $message_success = "✅ Compte rendu mis à jour avec succès !";
                $form_date = $cr_date;
                $form_descriptif = $descriptif;
            } else {
                $message_error = "❌ Erreur lors de la mise à jour : " . mysqli_error($bdd);
            }
        } else {
            $query_insert = "INSERT INTO compte_rendu (num_utilisateur, date_cr, descriptif, date_creation) VALUES ('$user_id', '$cr_date_safe', '$descriptif_safe', NOW())";
            
            if (mysqli_query($bdd, $query_insert)) {
                $message_success = "✅ Compte rendu ajouté avec succès !";
                $form_date = $cr_date;
                $form_descriptif = $descriptif;
            } else {
                $message_error = "❌ Erreur lors de l'insertion : " . mysqli_error($bdd);
            }
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'load_cr') {
    $cr_date = isset($_POST['cr_date']) ? $_POST['cr_date'] : '';
    
    if (!empty($cr_date)) {
        $cr_date_safe = mysqli_real_escape_string($bdd, $cr_date);
        $query = "SELECT descriptif FROM compte_rendu WHERE num_utilisateur = '$user_id' AND date_cr = '$cr_date_safe'";
        $result = mysqli_query($bdd, $query);
        
        if ($result && mysqli_num_rows($result) > 0) {
            $cr_data = mysqli_fetch_assoc($result);
            $form_date = $cr_date;
            $form_descriptif = $cr_data['descriptif'];
        } else {
            $form_date = $cr_date;
            $form_descriptif = "";
        }
    }
}

if (!isset($_POST['action'])) {
    $query_check = "SELECT descriptif FROM compte_rendu WHERE num_utilisateur = '$user_id' AND date_cr = '$form_date'";
    $result_check = mysqli_query($bdd, $query_check);
    if ($result_check && mysqli_num_rows($result_check) > 0) {
        $cr_data = mysqli_fetch_assoc($result_check);
        $form_descriptif = $cr_data['descriptif'];
    }
}

mysqli_close($bdd);
?>

<div class="cr-container">
    <div class="cr-box">
        <h1>Ajouter / Modifier un Compte Rendu</h1>
        
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
        
        <form method="post" class="cr-form">
            <input type="hidden" name="action" value="save_cr">
            
            <div class="form-group">
                <label for="cr_date">Date du compte rendu</label>
                <input 
                    type="date" 
                    id="cr_date" 
                    name="cr_date" 
                    value="<?php echo htmlspecialchars($form_date); ?>" 
                    required
                    onchange="loadCR()"
                >
            </div>
            
            <div class="form-group">
                <label for="descriptif">Descriptif</label>
                <textarea 
                    id="descriptif" 
                    name="descriptif" 
                    rows="8"
                    placeholder="Décrivez votre compte rendu ici..."
                    required
                ><?php echo htmlspecialchars($form_descriptif); ?></textarea>
            </div>
            
            <button type="submit" class="btn-insert">INSÉRER</button>
        </form>
        
        <div class="cr-info">
            <p><?php echo $cr_data ? '📝 Compte rendu existant - Modification' : '📄 Nouveau compte rendu'; ?></p>
        </div>
    </div>
</div>

<script>
function loadCR() {
    const dateInput = document.getElementById('cr_date').value;
    const descriptifArea = document.getElementById('descriptif');
    const crInfo = document.querySelector('.cr-info p');
    
    if (!dateInput) {
        descriptifArea.value = '';
        crInfo.textContent = '📄 Nouveau compte rendu';
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'load_cr');
    formData.append('cr_date', dateInput);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const textarea = doc.querySelector('#descriptif');
        const crInfoNew = doc.querySelector('.cr-info p');
        
        if (textarea) {
            const content = textarea.value;
            descriptifArea.value = content;
            
            if (content.trim()) {
                crInfo.textContent = '📝 Compte rendu existant - Modification';
                descriptifArea.style.borderColor = '#667eea';
            } else {
                crInfo.textContent = '📄 Nouveau compte rendu';
                descriptifArea.style.borderColor = '#e0e0e0';
            }
        }
    })
    .catch(error => console.error('Erreur:', error));
}

document.addEventListener('DOMContentLoaded', function() {
    loadCR();
    
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
