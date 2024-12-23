<!DOCTYPE html>
<html>
<head>
    <title>Détails du partenaire</title>
    <link rel="stylesheet" type="text/css" href="../style0.css">
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
    if (!isset($_GET['numpa'])) {
        echo "Aucun partenaire sélectionné.";
        exit;
    }

    $numpa = $_GET['numpa'];

    // Requête pour récupérer les détails du partenaire
    $sql = "SELECT
                partenaire.nom AS part_nom,
                partenaire.description AS part_description,
                partenaire.adresse AS part_adresse,
                personne.nom AS contact_nom,
                personne.prenom AS contact_prenom,
                personne.matricule AS contact_matricule
            FROM partenaire
            LEFT JOIN personne ON partenaire.contact = personne.matricule
            LEFT JOIN typepartenaire ON typepartenaire.numtype = partenaire.numtype
            WHERE partenaire.numpa = $numpa";

    $resultat = pg_query($con, $sql);

    if (!$resultat || pg_num_rows($resultat) == 0) {
        echo "Aucun détail trouvé pour ce partenaire.";
        exit;
    }

    $row = pg_fetch_array($resultat);

    // Afficher les détails du partenaire
    echo "<h2>Détails du partenaire</h2>";
    echo "<div class='info'><strong>Nom du partenaire : </strong>" . $row['part_nom'] . "<br>";
    echo "<strong>Description : </strong>" . $row['part_description'] . "<br>";
    echo "<strong>Adresse : </strong>" . $row['part_adresse'] . "<br>";
    echo "<strong>Contact : </strong>";
    if (!empty($row['contact_nom'])) {
        echo "<a href='details_personne.php?matricule=" . $row['contact_matricule'] . "'>" . $row['contact_nom'] . " " . $row['contact_prenom'] . "</a>";
    } else {
        echo "Aucun contact défini.";
    }
    echo "</div><br><br>";
    ?>
    <div class="center-container">
        <a href="modifier_partenaire.php?numpa=<?php echo $numpa; ?>">Modifier le partenaire</a><br>
        <a href="creer_personne.php">Ajouter une personne</a><br><br>
    </div>

    <!-- Tableau des personnes associées -->
    <h3>Personnes associées au partenaire</h3>
    <table border="1">
        <tr>
            <td>Nom et Prénom</td>
            <td>Numéro de téléphone</td>
            <td>Adresse mail</td>
            <td>Suppression</td>
        </tr>
        <?php
        // Requête pour récupérer les personnes associées au partenaire
        $sql_p = "SELECT
                    personne.nom AS pers_nom,
                    personne.prenom AS pers_prenom,
                    personne.telephone AS pers_tel,
                    personne.email AS pers_email,
                    personne.matricule AS pers_matricule
                FROM personne
                WHERE personne.numpa = $numpa";

        $resultat_p = pg_query($con, $sql_p);

        while ($row_p = pg_fetch_array($resultat_p)) {
            echo "<tr>";
            // Colonne pour le nom et prénom de la personne
            echo "<td><a href='details_personne.php?matricule=" . $row_p['pers_matricule'] . "'>" . $row_p['pers_nom'] . " " . $row_p['pers_prenom'] . "</a></td>";
            // Colonne pour le numéro de téléphone
            echo "<td>" . $row_p['pers_tel'] . "</td>";
            // Colonne pour l'adresse mail
            echo "<td>" . $row_p['pers_email'] . "</td>";

            // Colonne pour la suppression
            echo "<td>
                    <form method='post' action='details_partenaire.php?numpa=$numpa' style='margin: 0;'>
                        <input type='hidden' name='pers_matricule_to_delete' value='" . $row_p['pers_matricule'] . "'>
                        <button type='submit' name='delete_single_person' class='delete-btn'>Supprimer</button>
                    </form>
                  </td>";
            echo "</tr>";
        }
        ?>
    </table>

    <?php
    // Traitement de la suppression d'une personne associée
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_single_person'])) {
        $pers_matricule_to_delete = $_POST['pers_matricule_to_delete'];

        // Supprimer la personne associée au partenaire
        $deletePersonQuery = "DELETE FROM personne WHERE matricule = $pers_matricule_to_delete";
        $result_delete = pg_query($con, $deletePersonQuery);

        if ($result_delete) {
            echo "<p>La personne a été supprimée avec succès.</p>";

            // Rafraîchir la page avec le paramètre numpa pour conserver le contexte
            echo "<meta http-equiv='refresh' content='0;url=details_partenaire.php?numpa=$numpa'>";
        } else {
            echo "<p>Erreur lors de la suppression de la personne : " . pg_last_error($con) . "</p>";
        }
    }
    ?>

    <!-- Boutons de navigation -->
    <div class="center-container">
        <a href="../index.php" class="back-button">Retour à la page d'accueil</a>
        <button onclick="window.history.back();" class="back-button">Retour</button>
    </div>
</body>
</html>
