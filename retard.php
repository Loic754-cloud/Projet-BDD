<!DOCTYPE html>
<html>
<head>
    <title>Tâches en retard</title>
    <link rel="stylesheet" type="text/css" href="style0.css">
</head>

<body>
    <h2>Tâches en retard</h2>

    <!-- Section des actions -->
    <div class="center-container">
        <a href="index.php" class="back-button">Retour au menu principal</a>
        <button onclick="window.history.back();" class="back-button">Retour</button>
    </div>

    <!-- Tableau des tâches en retard -->
    <table border="1">
        <tr>
            <td>Nom du projet</td>
            <td>Nom de la tâche</td>
            <td>Nombre de jours en retard</td>
        </tr>

        <?php
        include("connexion.php");
        $con = connect();
        $today = date("Y-m-d H:i:s");
        $today_date = date_create($today);

        // Requête pour récupérer les tâches et les projets associés
        $sql = "SELECT t.nomta, t.numpro, e.numetat, pro.nom, t.fin
                FROM tache t
                JOIN personne p ON p.matricule = t.responsable
                JOIN etat e ON e.numetat = t.numetat
                JOIN projet pro ON pro.numpro = t.numpro";
        $resultat = pg_query($con, $sql);

        // Affichage des tâches en retard
        while ($ligne = pg_fetch_array($resultat)) {
            if ($today > $ligne['fin'] && $ligne['numetat'] != 3 && $ligne['numetat'] != 4) {
                $fin = date_create($ligne['fin']);
                $retard = date_diff($today_date, $fin);
                $retard = $retard->format('%d jour(s)');
                echo "<tr>";
                echo "<a href='projet/details_projet.php?numpro=" . $ligne['numpro'] . "'>" . $ligne['nom'] . "</a>";
                echo "<a href='projet/details_tache.php?numta=" . $ligne['numta'] . "'>" . $ligne['nomta'] . "</a>";
                echo "<td>" . $retard . "</td>";
                echo "</tr>";
            }
        }
        ?>
    </table>
</body>
</html>
