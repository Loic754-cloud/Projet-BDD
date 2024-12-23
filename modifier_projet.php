<!DOCTYPE html>
<html>
<head>
    <title>Modifier projet</title>
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

// Vérifier si l'identifiant du projet est présent dans l'URL
if (!isset($_GET['numpro'])) {
    echo "Aucun projet sélectionné.";
    exit;
}

$numpro = $_GET['numpro'];

// Si le formulaire a été soumis pour la modification
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire
    $nom = $_POST['nom'];
    $acronyme = $_POST['acronyme'];
    $description = $_POST['description'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $numetat = $_POST['numetat'];
    $responsable = $_POST['responsable'];

    // Mettre à jour le projet dans la base de données
    $sql_update = "UPDATE projet SET
                   nom = '$nom',
                   acronyme = '$acronyme',
                   description = '$description',
                   deb = '$date_debut',
                   fin = '$date_fin',
                   numetat = '$numetat',
                   responsable = '$responsable'
                   WHERE numpro = $numpro";

    $result_update = pg_query($con, $sql_update);
    if ($result_update) {
        echo "<p>Le projet a été modifié avec succès.</p>";

    } else {
        echo "<p>Erreur lors de la modification du projet : " . pg_last_error($con) . "</p>";
    }
}

// Récupérer les détails du projet
$sql = "SELECT
        projet.nom AS projet_nom,
        projet.acronyme AS projet_acronyme,
        projet.description AS projet_description,
        projet.deb AS date_debut,
        projet.fin AS date_fin,
        projet.responsable AS responsable_matricule,
        projet.numetat AS projet_numetat
        FROM projet
        WHERE projet.numpro = $numpro";

$resultat = pg_query($con, $sql);
if (!$resultat || pg_num_rows($resultat) == 0) {
    echo "Aucun projet trouvé avec cet identifiant.";
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

<h2>Modifier le projet</h2>
<form method="POST" action="">
    <input type="hidden" name="valider">
    <input type="hidden" name="numpro" value="<?php echo $numpro; ?>">

    <label>Nom du projet :</label><br>
    <input type="text" name="nom" value="<?php echo ($row['projet_nom']); ?>" required><br><br>

    <label>Acronyme :</label><br>
    <input type="text" name="acronyme" value="<?php echo ($row['projet_acronyme']); ?>" required><br><br>

    <label>Description :</label><br>
    <textarea name="description" rows="5" cols="50"><?php echo ($row['projet_description']); ?></textarea><br><br>

    <label>Date de début :</label><br>
    <input type="date" name="date_debut" value="<?php echo $row['date_debut']; ?>"><br><br>

    <label>Date de fin :</label><br>
    <input type="date" name="date_fin" value="<?php echo $row['date_fin']; ?>"><br><br>

    <!-- Liste déroulante pour l'état du projet -->
    <label>Etat du projet :</label><br>
    <select name="numetat" required>
        <?php
        while ($etat = pg_fetch_array($result_etats)) {
            $selected = ($etat['numetat'] == $row['projet_numetat']) ? 'selected' : '';
            echo "<option value='" . $etat['numetat'] . "' $selected>" . $etat['nometat'] . "</option>";
        }
        ?>
    </select><br><br>

    <!-- Liste déroulante pour le responsable du projet -->
    <label>Responsable du projet :</label><br>
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
