<!DOCTYPE html>
<html>
<head>
    <title>Détails de la tâche</title>
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

    // Vérifier si l'identifiant de la tâche est présent dans l'URL
    if (!isset($_GET['numta'])) {
        echo "Aucune tâche sélectionnée.";
        exit;
    }

    $numta = $_GET['numta'];

    // Requête pour récupérer les détails de la tâche
    $sql = "SELECT
                tache.nomta AS tache_nom,
                tache.description AS tache_description,
                tache.deb AS tache_date_debut,
                tache.fin AS tache_date_fin,
                personne.nom AS responsable_nom,
                personne.prenom AS responsable_prenom,
                projet.nom AS projet_nom
            FROM tache
            LEFT JOIN personne ON tache.responsable = personne.matricule
            LEFT JOIN projet ON tache.numpro = projet.numpro
            WHERE tache.numta = $numta";

    $resultat = pg_query($con, $sql);

    if (!$resultat || pg_num_rows($resultat) == 0) {
        echo "Aucun détail trouvé pour cette tâche.";
        exit;
    }

    $row = pg_fetch_array($resultat);

    // Afficher les détails de la tâche
    echo "<h2>Détails de la tâche</h2>";
    echo "<div class='info'><strong>Nom de la tâche : </strong>" . $row['tache_nom'] . "<br>";
    echo "<strong>Description : </strong>" . $row['tache_description'] . "<br>";
    echo "<strong>Date de début : </strong>" . $row['tache_date_debut'] . "<br>";
    echo "<strong>Date de fin : </strong>" . $row['tache_date_fin'] . "<br>";
    echo "<strong>Responsable : </strong>" . $row['responsable_nom'] . " " . $row['responsable_prenom'] . "<br>";
    echo "<strong>Projet : </strong>" . $row['projet_nom'] . "</div><br><br>";
    ?>

    <div class="center-container">
        <a href="modifier_tache.php?numta=<?php echo $numta; ?>">Modifier la tâche</a><br>
        <a href="rejoindre_tache.php">Ajouter une personne</a><br><br>
    </div>

    <!-- Tableau des rôles associés à cette tâche -->
    <h3>Rôles associés à la tâche</h3>
    <table border="1">
        <tr>
            <td>Nom du rôle</td>
            <td>Personne associée</td>
            <td>Suppression</td>
        </tr>
        <?php
        // Requête pour récupérer les rôles associés à cette tâche
        $sql_roles = "SELECT
                        role.nomro AS role_nom,
                        personne.nom AS personne_nom,
                        personne.prenom AS personne_prenom,
                        personne.matricule AS personne_id
                    FROM role
                    LEFT JOIN personne ON role.matricule = personne.matricule
                    WHERE role.numta = $numta
                    ORDER BY role_nom";

        $resultat_roles = pg_query($con, $sql_roles);

        while ($role = pg_fetch_array($resultat_roles)) {
            echo "<tr>";

            // Colonne pour le nom du rôle
            echo "<td>";
            echo $role['role_nom'];
            echo "</td>";

            // Colonne pour le responsable (personne associée au rôle)
            echo "<td>";
            if ($role['personne_nom']) {
                echo "<a href='../partenaire/details_personne.php?matricule=" . $role['personne_id'] . "'>" . $role['personne_nom'] . " " . $role['personne_prenom'] . "</a>";
            } else {
                echo "-";
            }
            echo "</td>";

            // Colonne pour la suppression
            echo "<td>
                    <form method='post' action='details_tache.php?numta=$numta' style='margin: 0;'>
                        <input type='hidden' name='role_to_delete' value='" . $role['role_id'] . "'>
                        <button type='submit' name='delete_role' class='delete-btn'>Supprimer</button>
                    </form>
                  </td>";
            echo "</tr>";
        }
        ?>
    </table>

    <?php
    // Traitement de la suppression d'un rôle si un bouton est soumis
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_role'])) {
        $role_to_delete = $_POST['role_to_delete'];

        // Supprimer le rôle spécifié
        $deleteRole = "DELETE FROM role WHERE matricule = $matricule AND numpro=$numpro";
        $result_delete = pg_query($con, $deleteRole);

        if ($result_delete) {
            echo "<p>Rôle supprimé avec succès.</p>";

            // Rafraîchir la page avec le paramètre numta pour conserver le contexte
            echo "<meta http-equiv='refresh' content='0;url=details_tache.php?numta=$numta'>";
        } else {
            echo "<p>Erreur lors de la suppression du rôle : " . pg_last_error($con) . "</p>";
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
