<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Informations Personnelles</title>
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

$user_id = $_SESSION['id'];
$message_success = "";
$message_error = "";
$user_data = null;

$query = "SELECT id, login FROM utilisateur WHERE id = '$user_id'";
$result = mysqli_query($bdd, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_assoc($result);
} else {
    echo '<div class="error-message">Erreur : Informations utilisateur non trouvées.</div>';
    mysqli_close($bdd);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $new_login = isset($_POST['login']) ? trim($_POST['login']) : '';
    
    if (empty($new_login)) {
        $message_error = "Le login ne peut pas être vide.";
    } else {
        $check_login = mysqli_query($bdd, "SELECT id FROM utilisateur WHERE login = '$new_login' AND id != '$user_id'");
        
        if ($check_login && mysqli_num_rows($check_login) > 0) {
            $message_error = "Ce login est déjà utilisé.";
        } else {
            $new_login_safe = mysqli_real_escape_string($bdd, $new_login);
            
            $update_query = "UPDATE utilisateur SET login = '$new_login_safe' WHERE id = '$user_id'";
            
            if (mysqli_query($bdd, $update_query)) {
                $_SESSION['login'] = $new_login;
                $user_data['login'] = $new_login;
                $message_success = "✅ Login mis à jour avec succès !";
            } else {
                $message_error = "❌ Erreur lors de la mise à jour : " . mysqli_error($bdd);
            }
        }
    }
}

if (isset($_POST['action']) && $_POST['action'] === 'update_password') {
    $current_password = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    if (empty($current_password)) {
        $message_error = "Veuillez entrer votre mot de passe actuel.";
    } elseif (empty($new_password)) {
        $message_error = "Veuillez entrer un nouveau mot de passe.";
    } elseif ($new_password !== $confirm_password) {
        $message_error = "Les mots de passe ne correspondent pas.";
    } else {
        $get_password = mysqli_query($bdd, "SELECT motdepasse FROM utilisateur WHERE id = '$user_id'");
        $pwd_row = mysqli_fetch_assoc($get_password);
        
        $current_password_hash = md5(trim($current_password));
        
        if ($current_password_hash === $pwd_row['motdepasse']) {
            $hashed_password = md5(trim($new_password));
            $hashed_password_safe = mysqli_real_escape_string($bdd, $hashed_password);
            $pwd_query = "UPDATE utilisateur SET motdepasse = '$hashed_password_safe' WHERE id = '$user_id'";
            
            if (mysqli_query($bdd, $pwd_query)) {
                $message_success = "✅ Mot de passe changé avec succès !";
            } else {
                $message_error = "❌ Erreur lors du changement du mot de passe : " . mysqli_error($bdd);
            }
        } else {
            $message_error = "❌ Mot de passe actuel incorrect.";
        }
    }
}

mysqli_close($bdd);
?>

<div class="profile-container">
    <div class="profile-box">
        <h1>📋 Mes Informations Personnelles</h1>
        
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
        
        <div class="profile-section">
            <h2>Modifier mon login</h2>
            <form method="post" class="profile-form">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-group">
                    <label for="login">Login</label>
                    <input 
                        type="text" 
                        id="login" 
                        name="login" 
                        value="<?php echo htmlspecialchars($user_data['login']); ?>" 
                        required
                    >
                </div>
                
                <button type="submit" class="btn">Enregistrer les modifications</button>
            </form>
        </div>
        
        <hr>
        
        <div class="profile-section">
            <h2>Changer mon mot de passe</h2>
            <form method="post" class="profile-form">
                <input type="hidden" name="action" value="update_password">
                
                <div class="form-group">
                    <label for="current_password">Mot de passe actuel</label>
                    <input 
                        type="password" 
                        id="current_password" 
                        name="current_password" 
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe</label>
                    <input 
                        type="password" 
                        id="new_password" 
                        name="new_password" 
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le nouveau mot de passe</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required
                    >
                </div>
                
                <button type="submit" class="btn">Changer le mot de passe</button>
            </form>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <a href="profil.php" class="btn-secondary">← Retour au profil</a>
        </div>
    </div>
</div>

<style>
:root {
    --primary-gradient: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
    --primary-color: #2563eb;
    --secondary-color: #0ea5e9;
    --text-dark: #1e293b;
    --text-light: #64748b;
    --glass-bg: rgba(255, 255, 255, 0.1);
    --glass-border: rgba(255, 255, 255, 0.2);
    --bg-light: #f8fafc;
    --success-bg: rgba(16, 185, 129, 0.1);
    --success-text: #047857;
    --success-border: #10b981;
    --error-bg: rgba(239, 68, 68, 0.1);
    --error-text: #dc2626;
    --error-border: #ef4444;
    --radius: 24px;
    --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
    --shadow-sm: 0 4px 15px rgba(37, 99, 235, 0.15);
}

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

* {
    box-sizing: border-box;
}

.profile-container {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    padding: 2rem 1rem;
    background: linear-gradient(135deg, #e0f2fe 0%, #bfdbfe 50%, #dbeafe 100%);
    transition: background 0.3s ease;
}

[data-theme="dark"] .profile-container {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #1e293b 100%);
}

.profile-box {
    background: var(--glass-bg);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    padding: 2.5rem;
    border-radius: 32px;
    box-shadow: var(--shadow);
    width: 100%;
    max-width: 600px;
    animation: slideUp 0.5s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.profile-box h1 {
    text-align: center;
    color: var(--primary-color);
    margin-bottom: 2.5rem;
    font-size: 1.8rem;
    font-weight: 700;
    letter-spacing: -0.5px;
}

.success-message,
.error-message {
    padding: 1rem 1.2rem;
    border-radius: var(--radius);
    margin-bottom: 1.5rem;
    font-weight: 500;
    animation: slideDown 0.3s ease-out;
    border-left: 4px solid;
    backdrop-filter: blur(10px);
    border: 1.5px solid;
}

.success-message {
    background: var(--success-bg);
    color: var(--success-text);
    border-color: rgba(16, 185, 129, 0.3);
    border-left-color: var(--success-border);
}

.error-message {
    background: var(--error-bg);
    color: var(--error-text);
    border-color: rgba(239, 68, 68, 0.3);
    border-left-color: var(--error-border);
}

.profile-section {
    margin: 2rem 0;
}

.profile-section h2 {
    color: var(--text-dark);
    font-size: 1.3rem;
    margin-bottom: 1.5rem;
    border-bottom: 3px solid var(--primary-color);
    padding-bottom: 0.7rem;
    font-weight: 600;
    letter-spacing: -0.3px;
}

.profile-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.6rem;
    color: var(--text-dark);
    font-size: 0.95rem;
}

.form-group input {
    padding: 0.85rem 1rem;
    border: 1.5px solid rgba(255, 255, 255, 0.4);
    border-radius: var(--radius);
    font-family: inherit;
    font-size: 1rem;
    transition: all 0.2s ease;
    background: rgba(255, 255, 255, 0.6);
    backdrop-filter: blur(10px);
    color: var(--text-dark);
}

[data-theme="dark"] .form-group input {
    background: rgba(15, 23, 42, 0.6);
    border-color: rgba(255, 255, 255, 0.2);
    color: var(--text-dark);
}

.form-group input::placeholder {
    color: var(--text-light);
}

.form-group input:hover {
    border-color: rgba(255, 255, 255, 0.6);
    background: rgba(255, 255, 255, 0.8);
}

[data-theme="dark"] .form-group input:hover {
    background: rgba(15, 23, 42, 0.8);
    border-color: rgba(255, 255, 255, 0.3);
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    background: rgba(255, 255, 255, 0.95);
}

[data-theme="dark"] .form-group input:focus {
    background: rgba(15, 23, 42, 0.9);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
}

.btn,
.btn-secondary {
    padding: 0.9rem 1.8rem;
    background: var(--primary-gradient);
    color: white;
    border: none;
    border-radius: var(--radius);
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 1rem;
    box-shadow: 0 8px 16px rgba(37, 99, 235, 0.3);
    text-decoration: none;
}

.btn-secondary {
    display: inline-block;
    font-size: 0.95rem;
    background: rgba(255, 255, 255, 0.4);
    backdrop-filter: blur(10px);
    border: 1.5px solid rgba(255, 255, 255, 0.5);
    color: var(--text-dark);
}

[data-theme="dark"] .btn-secondary {
    background: rgba(15, 23, 42, 0.6);
    border-color: rgba(255, 255, 255, 0.2);
}

.btn:hover,
.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(37, 99, 235, 0.4);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.7);
    box-shadow: 0 8px 16px rgba(37, 99, 235, 0.2);
}

[data-theme="dark"] .btn-secondary:hover {
    background: rgba(15, 23, 42, 0.8);
    box-shadow: 0 8px 16px rgba(59, 130, 246, 0.3);
}

.btn:active,
.btn-secondary:active {
    transform: translateY(0);
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
    color: var(--text-dark);
}

.theme-toggle:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: scale(1.1);
}

[data-theme="dark"] .theme-toggle {
    color: var(--text-dark);
}

hr {
    border: none;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--border-color), transparent);
    margin: 2.5rem 0;
}

@media (max-width: 768px) {
    .profile-container {
        padding: 1.5rem;
    }
    
    .profile-box {
        padding: 2rem;
        border-radius: 12px;
    }
    
    .profile-box h1 {
        font-size: 1.6rem;
        margin-bottom: 2rem;
    }
    
    .profile-section h2 {
        font-size: 1.15rem;
    }
    
    .profile-form {
        gap: 1.2rem;
    }
}

@media (max-width: 600px) {
    .profile-container {
        padding: 1rem;
    }
    
    .profile-box {
        padding: 1.5rem;
        border-radius: 10px;
    }
    
    .profile-box h1 {
        font-size: 1.4rem;
        margin-bottom: 1.5rem;
    }
    
    .profile-section h2 {
        font-size: 1rem;
        margin-bottom: 1.2rem;
    }
    
    .form-group input {
        padding: 0.8rem 0.9rem;
        font-size: 16px;
    }
    
    .btn,
    .btn-secondary {
        width: 100%;
        padding: 0.8rem 1rem;
        font-size: 0.95rem;
    }
    
    .profile-form {
        gap: 1rem;
    }
}
</style>

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
