<!DOCTYPE html>
<html>

<head>
    <title>Menu des projets</title>
    <link rel="stylesheet" type="text/css" href="../style0.css">
</head>

<body>
    <h2>Menu des projets</h2>
    <div class="center-container">
    <a href="creer_projet.php">Créer un projet</a><br><br>
    <a href="rejoindre_projet.php">Attribuer des projets</a><br><br>
    <a href="../index.php" class="back-button">Retour</a>
    </div>
 <!-- Tableau des projets -->
    <table border="1">
        <tr>
            <td>Acronyme des projets</td>
            <td>Nom du projet</td>
            <td>Nom du responsable</td>
            <td>Suppression</td>
        </tr>

        <?php
        include("../connexion.php");
        $con = connect();

        // Requête pour récupérer les projets avec le responsable
        $sql = "SELECT pro.numpro, pro.acronyme, per.nom, per.prenom, pro.nom as nompro, per.matricule
                FROM projet pro
                JOIN personne per ON per.matricule = pro.responsable";
        $resultat = pg_query($con, $sql);

        // Affichage des projets
        while ($ligne = pg_fetch_array($resultat)) {
            echo "<tr>";
            echo "<td>" . $ligne['acronyme'] . "</td>";
            echo "<td><a href='details_projet.php?numpro=" . $ligne['numpro'] . "'>" . $ligne['nompro'] . "</a></td>";
            echo "<td><a href='../partenaire/details_personne.php?matricule=" . $ligne['matricule'] . "'>" . $ligne['nom'] . " " . $ligne['prenom'] . "</a></td>";


            echo "<td>
                    <form method='post' action='projet.php' style='margin: 0;'>
                        <input type='hidden' name='numpro_to_delete' value='" . $ligne['numpro'] . "'>
                        <button type='submit' name='delete_single' class='delete-btn'>Supprimer</button>
                    </form>
                  </td>";
            echo "</tr>";
        }
        ?>
    </table>

    <?php
    // Traitement de la suppression si un bouton est soumis
    if (isset($_POST['delete_single'])) {
        $numpro_to_delete = $_POST['numpro_to_delete'];

        // Supprimer les tâches associées au projet
        $deleteTasks = "DELETE FROM tache WHERE numpro = $numpro_to_delete";
        pg_query($con, $deleteTasks);

        // Supprimer le projet lui-même
        $deleteProject = "DELETE FROM projet WHERE numpro = $numpro_to_delete";

        $result_delete = pg_query($con, $deleteProject);

    if ($result_delete) {
        echo "<p>Projet supprimé avec succès.</p>";

        // Rafraîchir la page avec le paramètre numpro pour conserver le contexte
        echo "<meta http-equiv='refresh' content='0;url=projet.php'>";
    } else {
        echo "<p>Erreur lors de la suppression dprojet : " . pg_last_error($con) . "</p>";
    }
    }
    ?>

    <!-- Formulaire pour voir les projets en retard -->

    <div class="center-container">
    <form method="post" action="../retard.php">
        <input type="submit" name="submit" value="Voir les projets en retard"><br><br>
    </form>
    </div>
</body>

</html>
