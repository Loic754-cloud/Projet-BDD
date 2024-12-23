<!DOCTYPE html>
<html>
<head>
    <title>Détails du projet</title>
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

    // Vérifier si l'identifiant du projet est présent dans l'URL
    if (!isset($_GET['numpro'])) {
        echo "Aucun projet sélectionné.";
        exit;
    }

    $numpro = $_GET['numpro'];

    // Requête pour récupérer les détails du projet
    $sql = "SELECT
                projet.nom AS projet_nom,
                projet.acronyme AS projet_acronyme,
                projet.description AS projet_description,
                projet.deb AS date_debut,
                projet.fin AS date_fin,
                personne.nom AS responsable_nom,
                personne.prenom AS responsable_prenom,
                etat.nometat AS nometat
            FROM projet
            LEFT JOIN personne ON projet.responsable = personne.matricule
            LEFT JOIN etat ON projet.numetat=etat.numetat
            WHERE projet.numpro = $numpro";

    $resultat = pg_query($con, $sql);

    if (!$resultat || pg_num_rows($resultat) == 0) {
        echo "Aucun détail trouvé pour ce projet.";
        exit;
    }

    $row = pg_fetch_array($resultat);

    // Afficher les détails du projet
    echo "<h2>Détails du projet</h2>";
    echo "<div class='info'><strong>Nom du projet : </strong>" . $row['projet_nom'] . "<br>";
    echo "<strong>Acronyme : </strong>" . $row['projet_acronyme'] . "<br>";
    echo "<strong>Description : </strong>" . $row['projet_description'] . "<br>";
    echo "<strong>Etat : </strong>" . $row['nometat'] . "<br>";
    echo "<strong>Date de début : </strong>" . $row['date_debut'] . "<br>";
    echo "<strong>Date de fin : </strong>" . $row['date_fin'] . "<br>";
    echo "<strong>Responsable : </strong>" . $row['responsable_nom'] . " " . $row['responsable_prenom'] . "</div><br><br>";
    ?>
    <div class="center-container">
        <a href="modifier_projet.php?numpro=<?php echo $numpro; ?>">Modifier le projet</a><br>
        <a href="creer_tache.php">Créer une tâche</a><br><br>
    </div>

    <!-- Tableau des tâches -->
    <h3>Taches du projet</h3>
   <table border="1">
    <tr>
        <td>Nom de la tâche</td>
        <td>Responsable associé</td>
        <td>Suppression</td>
    </tr>
    <?php

    $sql_taches = "SELECT
                      pe.nom AS personne_nom,
                      pe.prenom AS personne_prenom,
                      pe.matricule AS personne_id,
                      t.nomta AS tache_nom,
                      t.numta AS tache_id
                  FROM personne pe
                  LEFT JOIN tache t ON t.responsable = pe.matricule
                  WHERE t.numpro = $numpro
                  ORDER BY tache_nom";
    $result_taches = pg_query($con, $sql_taches);

    while ($tache = pg_fetch_array($result_taches)) {
        echo "<tr>";

        // Colonne pour le nom de la tâche
        echo "<td>";
        if ($tache['tache_nom']) {
            echo "<a href='details_tache.php?numta=" . $tache['tache_id'] . "'>" . $tache['tache_nom'] . "</a>";
        } else {
            echo "-";
        }
        echo "</td>";

        // Colonne pour le responsable
        echo "<td>";
        if ($tache['personne_nom']) {
            echo "<a href='../partenaire/details_personne.php?matricule=" . $tache['personne_id'] . "'>" . $tache['personne_nom'] . " " . $tache['personne_prenom'] . "</a>";
        } else {
            echo "-";
        }
        echo "</td>";

        // Colonne pour la suppression
        echo "<td>
                <form method='post' action='details_projet.php?numpro=$numpro' style='margin: 0;'>
                    <input type='hidden' name='numta_to_delete' value='" . $tache['tache_id'] . "'>
                    <button type='submit' name='delete_single' class='delete-btn'>Supprimer</button>
                </form>
              </td>";
        echo "</tr>";
    }
    ?>
</table>

    <?php
// Traitement de la suppression si un bouton est soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_single'])) {
    $numta_to_delete = $_POST['numta_to_delete'];

    // Supprimer la tâche spécifiée
    $deleteTasks = "DELETE FROM tache WHERE numta = $numta_to_delete";
    $result_delete = pg_query($con, $deleteTasks);

    if ($result_delete) {
        echo "<p>Tâche supprimée avec succès.</p>";

        // Rafraîchir la page avec le paramètre numpro pour conserver le contexte
        echo "<meta http-equiv='refresh' content='0;url=details_projet.php?numpro=$numpro'>";
    } else {
        echo "<p>Erreur lors de la suppression de la tâche : " . pg_last_error($con) . "</p>";
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
