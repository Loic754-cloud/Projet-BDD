<!DOCTYPE html>
<html>

<head>
    <title>Détails de la personne</title>
    <link rel="stylesheet" type="text/css" href="../style0.css">
</head>

<body>
    <?php
    include("../connexion.php");
    $connexion = connect();
    if (!$connexion) {
        echo "Problème de connexion à la base";
        exit;
    }

    // Vérifier si le matricule de la personne est présent dans l'URL
    if (!isset($_GET['matricule'])) {
        echo "Aucune personne sélectionnée.";
        exit;
    }

    $matricule = $_GET['matricule'];

    // Requête pour récupérer les détails de la personne
    $requete_personne = "SELECT
                            personne.nom AS nom_personne,
                            personne.prenom AS prenom_personne,
                            personne.telephone AS telephone_personne,
                            personne.email AS email_personne
                        FROM personne
                        WHERE personne.matricule = $matricule";

    $resultat_personne = pg_query($connexion, $requete_personne);

    if (!$resultat_personne || pg_num_rows($resultat_personne) == 0) {
        echo "Aucun détail trouvé pour cette personne.";
        exit;
    }

    $row_personne = pg_fetch_array($resultat_personne);

    // Afficher les détails de la personne
    echo "<h2>Détails de la personne</h2>";
    echo "<div class='info'><strong>Nom : </strong>" . $row_personne['nom_personne'] . "<br>";
    echo "<strong>Prénom : </strong>" . $row_personne['prenom_personne'] . "<br>";
    echo "<strong>Téléphone : </strong>" . $row_personne['telephone_personne'] . "<br>";
    echo "<strong>Email : </strong>" . $row_personne['email_personne'] . "<br></div><br><br>";

    // Afficher les projets et tâches auxquels la personne participe
    echo "<h3>Projets et tâches auxquels la personne participe</h3>";
    echo "<table border='1'>
            <tr>
                <td>Nom du Projet</td>
                <td>Nom de la Tâche</td>
                <td>Rôle</td>
                <td>Supprimer</td>
            </tr>";

    ?>
    <div class="center-container">
        <a href="modifier_partenaire.php?numpa=<?php echo $numpa; ?>">Modifier le partenaire</a><br>
        <a href="creer_personne.php">Ajouter une personne</a><br><br>
    </div>
    <?php

    $requete_projets = "SELECT
                            projet.nom AS nom_projet,
                            projet.numpro,
                            tache.numta,
                            tache.nomta AS nom_tache,
                            role.nomro AS nom_role,
                            tache.numta AS id_tache
                        FROM role
                        JOIN tache ON role.numta = tache.numta
                        JOIN projet ON tache.numpro = projet.numpro
                        WHERE role.matricule = $matricule";

    $resultat_projets = pg_query($connexion, $requete_projets);

    while ($row_projet = pg_fetch_array($resultat_projets)) {
        echo "<tr>
                <td><a href='../projet/details_projet.php?numpro=" . $row_project['numpro'] . "'>" . $row_projet['nom_projet'] . "</a></td>
                <td><a href='../projet/details_tache.php?numta=" . $row_project['numta'] . "'>" . $row_projet['nom_tache'] . "</a></td>
                <td>" . $row_projet['nom_role'] . "</td>
                <td>
                    <form method='post' action='details_personne.php?matricule=$matricule' style='margin: 0;'>
                        <input type='hidden' name='id_tache' value='" . $row_projet['id_tache'] . "'>
                        <button type='submit' name='supprimer_tache' class='delete-btn'>Supprimer</button>
                    </form>
                </td>
              </tr>";
    }

    echo "</table><br><br>";

    // Afficher les tâches ou projets dont la personne est responsable
    echo "<h3>Tâches ou projets dont la personne est responsable</h3>";
    echo "<table border='1'>
            <tr>
                <td>Nom du Projet</td>
                <td>Nom de la Tâche</td>
            </tr>";

    $requete_responsable = "SELECT
                               projet.nom AS nom_projet,
                               tache.nomta AS nom_tache
                            FROM tache
                            JOIN projet ON tache.numpro = projet.numpro
                            WHERE tache.responsable = $matricule";

    $resultat_responsable = pg_query($connexion, $requete_responsable);

    while ($row_responsable = pg_fetch_array($resultat_responsable)) {
        echo "<tr>
                <td>" . $row_responsable['nom_projet'] . "</td>
                <td>" . $row_responsable['nom_tache'] . "</td>
              </tr>";
    }

    echo "</table><br><br>";

    // Traitement de la suppression d'une tâche
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_tache'])) {
        $id_tache = $_POST['id_tache'];

        // Supprimer la personne de la tâche
        $requete_suppression_tache = "DELETE FROM role WHERE numta = $id_tache AND matricule = $matricule";
        $resultat_suppression = pg_query($connexion, $requete_suppression_tache);

        if ($resultat_suppression) {
            echo "<p>La tâche a été supprimée avec succès.</p>";

            // Rafraîchir la page avec le paramètre matricule pour conserver le contexte
            echo "<meta http-equiv='refresh' content='0;url=details_personne.php?matricule=$matricule'>";
        } else {
            echo "<p>Erreur lors de la suppression de la tâche : " . pg_last_error($connexion) . "</p>";
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
