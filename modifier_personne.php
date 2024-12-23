<!DOCTYPE html>
<html>
<head>
    <title>Modifier une personne</title>
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

// Vérifier si l'identifiant de la personne est présent dans l'URL
if (!isset($_GET['matricule']) || !ctype_digit($_GET['matricule'])) {
    echo "Erreur : identifiant personne invalide.";
    exit;
}
$matricule = $_GET['matricule'];

// Si le formulaire a été soumis pour la suppression
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['supprimer'])) {
    // Supprimer la personne de la base de données
    $sql_delete = "DELETE FROM personne WHERE matricule = $matricule";
    $result_delete = pg_query($con, $sql_delete);
    if ($result_delete) {
        echo "<p>La personne a été supprimée avec succès.</p>";
        exit;
    } else {
        echo "<p>Erreur lors de la suppression de la personne : " . pg_last_error($con) . "</p>";
    }
}

// Récupérer les données de la personne
$sql = "SELECT personne.nom AS nompers,
               partenaire.nom AS nompa,
               personne.prenom AS pers_prenom,
               personne.email AS email,
               personne.telephone AS telephone,
               partenaire.numpa
        FROM personne
        JOIN partenaire ON partenaire.numpa=personne.numpa
        WHERE personne.matricule = $matricule";

$resultat = pg_query($con, $sql);
if (!$resultat || pg_num_rows($resultat) == 0) {
    echo "Aucune personne trouvée avec cet identifiant.";
    exit;
}

$row = pg_fetch_array($resultat);

// Récupérer tous les partenaires existants
$sql_partenaires = "SELECT numpa, nom AS nompa FROM partenaire";
$resultat_partenaires = pg_query($con, $sql_partenaires);
?>

<h2>Modifier la personne</h2>
<form method="POST" action="">
    <input type="hidden" name="matricule" value="<?php echo $matricule; ?>">

    <label>Nom de la personne :</label><br>
    <input type="text" name="nompers" value="<?php echo $row['nompers']; ?>" required><br><br>

    <label>Prénom de la personne :</label><br>
    <input type="text" name="prenompers" value="<?php echo $row['pers_prenom']; ?>" required><br><br>

    <label>Numéro de téléphone :</label><br>
    <input type="text" name="telephone" value="<?php echo $row['telephone']; ?>" required><br><br>

    <label>Adresse mail :</label><br>
    <input type="text" name="email" value="<?php echo $row['email']; ?>" required><br><br>

    <label>Nom du partenaire :</label><br>
    <select name="numpa" required>
        <?php
        while ($partenaire = pg_fetch_array($resultat_partenaires)) {
            $selected = $partenaire['numpa'] == $row['numpa'] ? 'selected' : '';
            echo "<option value='{$partenaire['numpa']}' $selected>{$partenaire['nompa']}</option>";
        }
        ?>
    </select><br><br>

    <button type="submit">Enregistrer</button><br><br>

    <!-- Bouton de suppression -->
    <button type="submit" name="supprimer" style="background-color: red; color: white;">Supprimer la personne</button><br><br>

    <div class="center-container">
        <a href="../index.php" class="back-button">Retour à la page d'accueil</a>
    </div>
</form>

</body>
</html>
