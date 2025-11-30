<?php
session_start();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Élèves</title>
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

if (!isset($_SESSION['type']) || $_SESSION['type'] != 1) {
    echo '<div class="error-message">Accès refusé. Cette page est réservée aux professeurs.</div>';
    echo '<a href="acceuil.php" class="btn">Retour à l\'accueil</a>';
    exit;
}

require "menu_prof.php";

$bdd = mysqli_connect($serveurBDD, $userBDD, $mdpBDD, $nomBDD);
if (!$bdd) {
    die("Erreur de connexion à la base de données");
}

$eleves_list = array();

$query = "SELECT id, login FROM utilisateur WHERE type = 0 ORDER BY login ASC";
$result = mysqli_query($bdd, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['nb_crs'] = 0;
        
        $cr_query = "SELECT COUNT(id) as count FROM compte_rendu WHERE num_utilisateur = '" . $row['id'] . "'";
        $cr_result = mysqli_query($bdd, $cr_query);
        
        if ($cr_result) {
            $cr_row = mysqli_fetch_assoc($cr_result);
            $row['nb_crs'] = $cr_row['count'];
        }
        
        $eleves_list[] = $row;
    }
}

mysqli_close($bdd);
?>

<div class="eleves-container">
    <div class="eleves-box">
        <h1>👥 Liste des Élèves</h1>
        
        <div class="stats-section">
            <p class="stats-text">Total d'élèves : <strong><?php echo count($eleves_list); ?></strong></p>
        </div>
        
        <?php if (empty($eleves_list)): ?>
            <div class="no-eleves-message">
                <p>Aucun élève trouvé dans le système.</p>
            </div>
        <?php else: ?>
            <div class="eleves-table-container">
                <table class="eleves-table">
                    <thead>
                        <tr>
                            <th>Login</th>
                            <th>Nombre de CR</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eleves_list as $eleve): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($eleve['login']); ?></td>
                                <td>
                                    <span class="badge">
                                        <?php echo $eleve['nb_crs']; ?> CR
                                    </span>
                                </td>
                                <td>
                                    <a href="tous_les_crs.php?eleve=<?php echo urlencode($eleve['id']); ?>" class="btn-view">👁️ Voir CRs</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.eleves-container {
    display: flex;
    justify-content: center;
    padding: 2rem;
}

.eleves-box {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    width: 100%;
    max-width: 1000px;
}

.eleves-box h1 {
    text-align: center;
    color: #667eea;
    margin-bottom: 2rem;
}

.stats-section {
    background: #f0f4ff;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    text-align: center;
}

.stats-text {
    margin: 0;
    color: #667eea;
    font-weight: 600;
    font-size: 1.1rem;
}

.eleves-table-container {
    overflow-x: auto;
}

.eleves-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1.5rem;
}

.eleves-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.eleves-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
}

.eleves-table td {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.eleves-table tbody tr:hover {
    background: #f9fafb;
}

.badge {
    display: inline-block;
    background: #e5ffe5;
    color: #2d5016;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.btn-view {
    display: inline-block;
    padding: 0.5rem 1rem;
    background: #667eea;
    color: white;
    border-radius: 6px;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    font-weight: 500;
}

.btn-view:hover {
    background: #764ba2;
    transform: translateY(-1px);
}

.no-eleves-message {
    text-align: center;
    padding: 3rem 1rem;
    color: #999;
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

[data-theme="dark"] .eleves-container {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 50%, #1e293b 100%);
}

[data-theme="dark"] .eleves-box {
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
}

[data-theme="dark"] .eleves-box h1 {
    color: var(--primary-color);
}

[data-theme="dark"] .stats-section {
    background: rgba(15, 23, 42, 0.6);
}

[data-theme="dark"] .stats-text {
    color: var(--primary-color);
}

[data-theme="dark"] .eleves-table th {
    background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
}

[data-theme="dark"] .eleves-table td {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

[data-theme="dark"] .eleves-table tbody tr:hover {
    background: rgba(15, 23, 42, 0.4);
}

[data-theme="dark"] .badge {
    background: rgba(16, 185, 129, 0.3);
    color: #4ade80;
}

[data-theme="dark"] .btn-view {
    background: var(--primary-color);
}

[data-theme="dark"] .btn-view:hover {
    background: var(--secondary-color);
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
    color: #333;
}

.theme-toggle:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: scale(1.1);
}

[data-theme="dark"] .theme-toggle {
    color: #f1f5f9;
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
