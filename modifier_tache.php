<!DOCTYPE html>
<html>
<head>
    <title>Modifier tâche</title>
    <link rel="stylesheet" type="text/css" href="../style2.css">
</head>
<body>

<?php
include("../connexion.php");
$con = connect();
if (!$con) {
    echo "Problème de connexion à la base";
    exit;
}

// Vérifier si l'identifiant de la tâche est présent dans l'URL
if (!isset($_GET['numta'])) {
    echo "Aucune tâche sélectionnée.";
    exit;
}

$numta = $_GET['numta'];

// Si le formulaire a été soumis pour la modification
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire
    $nom = $_POST['nomta'];
    $description = $_POST['description'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $numetat = $_POST['numetat'];
    $responsable = $_POST['responsable'];

    // Mettre à jour la tâche dans la base de données
    $sql_update = "UPDATE tache SET
                   nomta = '$nom',
                   description = '$description',
                   deb = '$date_debut',
                   fin = '$date_fin',
                   numetat = '$numetat',
                   responsable = '$responsable'
                   WHERE numta = $numta";

    $result_update = pg_query($con, $sql_update);
    if ($result_update) {
        echo "<p>La tâche a été modifiée avec succès.</p>";
    } else {
        echo "<p>Erreur lors de la modification de la tâche : " . pg_last_error($con) . "</p>";
    }
}

// Récupérer les détails de la tâche
$sql = "SELECT
        tache.nomta AS tache_nom,
        tache.description AS tache_description,
        tache.deb AS date_debut,
        tache.fin AS date_fin,
        tache.numetat AS tache_numetat,
        tache.responsable AS responsable_matricule
        FROM tache
        WHERE tache.numta = $numta";

$resultat = pg_query($con, $sql);
if (!$resultat || pg_num_rows($resultat) == 0) {
    echo "Aucune tâche trouvée avec cet identifiant.";
    exit;
}

$row = pg_fetch_array($resultat);

// Récupérer les états disponibles
$sql_etats = "SELECT numetat, nometat FROM etat";
$result_etats = pg_query($con, $sql_etats);

// Récupérer les responsables disponibles (personnes)
$sql_responsables = "SELECT matricule, nom, prenom FROM personne";
$result_responsables = pg_query($con, $sql_responsables);


?>

<h2>Modifier la tâche</h2>
<form method="POST" action="">
    <input type="hidden" name="numta" value="<?php echo $numta; ?>">

    <label>Nom de la tâche :</label><br>
    <input type="text" name="nomta" value="<?php echo ($row['tache_nom']); ?>" required><br><br>

    <label>Description :</label><br>
    <textarea name="description" rows="5" cols="50"><?php echo ($row['tache_description']); ?></textarea><br><br>

    <label>Date de début :</label><br>
    <input type="date" name="date_debut" value="<?php echo $row['date_debut']; ?>"><br><br>

    <label>Date de fin :</label><br>
    <input type="date" name="date_fin" value="<?php echo $row['date_fin']; ?>"><br><br>

    <!-- Liste déroulante pour l'état de la tâche -->
    <label>État de la tâche :</label><br>
    <select name="numetat" required>
        <?php
        while ($etat = pg_fetch_array($result_etats)) {
            $selected = ($etat['numetat'] == $row['tache_numetat']) ? 'selected' : '';
            echo "<option value='" . $etat['numetat'] . "' $selected>" . $etat['nometat'] . "</option>";
        }
        ?>
    </select><br><br>

    <!-- Liste déroulante pour le responsable de la tâche -->
    <label>Responsable de la tâche :</label><br>
    <select name="responsable" required>
        <?php
        while ($responsable = pg_fetch_array($result_responsables)) {
            $selected = ($responsable['matricule'] == $row['responsable_matricule']) ? 'selected' : '';
            echo "<option value='" . $responsable['matricule'] . "' $selected>" . $responsable['nom'] . " " . $responsable['prenom'] . "</option>";
        }
        ?>
    </select><br><br>


    <input type="submit" value="Valider le formulaire"><br><br>
    <input type="reset" value="Recommencer"><br><br>

    <div class="center-container">
        <a href="../index.php" class="back-button">Retour à la page d'accueil</a>
    </div>
</form>

</body>
</html>
