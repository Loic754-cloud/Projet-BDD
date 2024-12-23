<!DOCTYPE html>
<html>
<head>
    <title>Menu des partenaires</title>
    <link rel="stylesheet" type="text/css" href="../style0.css">
</head>

<body>
    <h2>Menu des partenaires</h2>

    <div class="center-container">
        <a href="creer_partenaire.php">Ajouter des partenaires</a><br><br>
        <a href="../projet/rejoindre_projet.php">Attribuer des projets</a><br><br>
        <a href="creer_personne.php">Ajouter des personnes</a><br><br>
        <a href="../index.php" class="back-button">Retour</a>
    </div>

    <!-- Tableau des partenaires -->
    <table border="1">
        <tr>
            <td>Noms du partenaire</td>
            <td>Type de partenaire</td>
            <td>Description</td>
            <td>Action</td>
        </tr>

        <?php
        include("../connexion.php");
        $con = connect();

        // Requête pour récupérer les partenaires
        $sql = "SELECT partenaire.numpa, partenaire.nom, partenaire.description, typepartenaire.titre
                FROM partenaire
                LEFT JOIN typepartenaire ON partenaire.numtype = typepartenaire.numtype
                ORDER BY partenaire.nom";
        $resultat = pg_query($con, $sql);

        // Affichage des partenaires
        while ($ligne = pg_fetch_array($resultat)) {
            echo "<tr>";
            echo "<td><a href='details_partenaire.php?numpa=" . $ligne['numpa'] . "'>" . $ligne['nom'] . "</a></td>";
            echo "<td>" . $ligne['titre'] . "</td>";
            echo "<td>" . $ligne['description'] . "</td>";
            echo "<td>
                    <form method='post' action='partenaire.php' style='margin: 0;'>
                        <input type='hidden' name='numpa_to_delete' value='" . $ligne['numpa'] . "'>
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
        $numpa_to_delete = $_POST['numpa_to_delete'];

        // Récupérer le nom du partenaire avant suppression
        $queryGetName = "SELECT nom FROM partenaire WHERE numpa = $numpa_to_delete";
        $resultName = pg_query($con, $queryGetName);
        $partnerName = pg_fetch_array($resultName)['nom'];

        // Supprimer le partenaire
        $deletePartner = "DELETE FROM partenaire WHERE numpa = $numpa_to_delete";
        pg_query($con, $deletePartner);

        echo "<p>Le partenaire '$partnerName' a été supprimé avec succès.</p>";
    }
    ?>

    <!-- Formulaire pour actualiser -->
    <div class="center-container">
        <a href="partenaire.php">Actualiser</a><br><br>
    </div>
</body>
</html>
