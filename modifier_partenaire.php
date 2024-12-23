<!DOCTYPE html>
<html>
<head>
    <title>Modifier partenaire</title>
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

// Vérifier si l'identifiant du partenaire est présent dans l'URL
if (!isset($_GET['numpa']) )) {
    echo "Erreur : identifiant partenaire invalide.";
    exit;
}
$numpa = $_GET['numpa'];

// Si le formulaire a été soumis, on traite les données
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les valeurs du formulaire
    $nom = $_POST['nom']);
    $description =$_POST['description']);
    $adresse =  $_POST['adresse']);
    $numtype = $_POST['numtype'];
    $contact = $_POST['contact'];

    // Requête pour mettre à jour le partenaire
    $sql_update = "UPDATE partenaire
                   SET nom = '$nom', description = '$description', adresse = '$adresse', numtype = $numtype, contact = $contact
                   WHERE numpa = $numpa";

    $result_update = pg_query($con, $sql_update);
    if ($result_update) {
        echo "<p>Les informations du partenaire ont été mises à jour avec succès.</p>";
    } else {
        echo "<p>Erreur lors de la mise à jour du partenaire : " . pg_last_error($con) . "</p>";
    }
}

// Récupérer les détails du partenaire
$sql = "SELECT partenaire.description AS part_description,
               partenaire.nom AS part_nom,
               partenaire.adresse AS part_adresse,
               partenaire.contact AS contact_id,
               partenaire.numtype AS type_id,
               personne.nom AS contact_nom,
               personne.prenom AS contact_prenom,
               personne.telephone AS contact_tel,
               typepartenaire.titre AS part_type
        FROM partenaire
        JOIN personne ON partenaire.contact=personne.matricule
        JOIN typepartenaire ON partenaire.numtype=typepartenaire.numtype
        WHERE partenaire.numpa = $numpa";

$resultat = pg_query($con, $sql);
if (!$resultat || pg_num_rows($resultat) == 0) {
    echo "Aucun partenaire trouvé avec cet identifiant.";
    exit;
}

$row = pg_fetch_array($resultat);

// Récupérer tous les types existants
$sql_types = "SELECT numtype, titre FROM typepartenaire";
$resultat_types = pg_query($con, $sql_types);

// Récupérer tous les contacts (personnes) associés
$sql_contacts = "SELECT matricule, nom, prenom FROM personne WHERE numpa = $numpa";
$resultat_contacts = pg_query($con, $sql_contacts);
?>

<h2>Modifier le partenaire</h2>
<form method="POST" action="">
    <input type="hidden" name="numpa" value="<?php echo $numpa; ?>">

    <label>Nom du partenaire :</label><br>
    <input type="text" name="nom" value="<?php echo $row['part_nom']; ?>" required><br><br>

    <label>Description :</label><br>
    <textarea name="description" rows="5" cols="50"><?php echo $row['part_description']; ?></textarea><br><br>

    <label>Adresse :</label><br>
    <input type="text" name="adresse" value="<?php echo $row['part_adresse']; ?>" required><br><br>

    <label>Type de partenaire :</label><br>
    <select name="numtype" required>
        <?php
        while ($type = pg_fetch_array($resultat_types)) {
            $selected = $type['numtype'] == $row['type_id'] ? 'selected' : '';
            echo "<option value='{$type['numtype']}' $selected>{$type['titre']}</option>";
        }
        ?>
    </select><br><br>

    <label>Contact :</label><br>
    <select name="contact" required>
        <?php
        while ($contact = pg_fetch_array($resultat_contacts)) {
            $selected = $contact['matricule'] == $row['contact_id'] ? 'selected' : '';
            echo "<option value='{$contact['matricule']}' $selected>{$contact['nom']} {$contact['prenom']}</option>";
        }
        ?>
    </select><br><br>

    <input type="submit" value="Enregistrer"></input>
    <div class="center-container">
        <a href="../index.php" class="back-button">Retour à la page d'accueil</a>

    </div>
</form>

</body>
</html>
