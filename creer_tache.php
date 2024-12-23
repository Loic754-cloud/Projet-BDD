<!DOCTYPE html>
<html>
<head>
    <title>Créer une tâche</title>
    <link rel="stylesheet" type="text/css" href="../style2.css">
</head>
<body>
<h2>Créer une tache</h2>
    <form method="post" action="creer_tache.php">

        Nom :<br>
        <input type="text" name="nomta" size="12" maxlength="25"><br><br>

        Description :<br>
        <textarea name="description" rows="4" cols="50" maxlength="200"></textarea><br><br>

        Date début :<br>
        <input type="date" name="deb" size="12"><br><br>

        Date fin :<br>
        <input type="date" name="fin" size="12"><br><br>

        Projet associé :<br>
        <select name="numpro">
            <?php
            include("../connexion.php");
            $con = connect();

            if (!$con) {
                echo "Problème de connexion à la base";
                exit;
            }

            // Requête pour récupérer les projets disponibles
            $sql_projets = "SELECT numpro, nom FROM projet ORDER BY nom";
            $result_projets = pg_query($con, $sql_projets);

            // Remplir les options du menu déroulant
            while ($row_proj = pg_fetch_assoc($result_projets)) {
                echo "<option value='" . $row_proj['numpro'] . "'>" . $row_proj['nom'] . "</option>";
            }
            ?>
        </select><br><br>

        Responsable :<br>
        <select name="responsable" id="responsable">
            <?php
            // Requête pour récupérer toutes les personnes (responsables)
            $sql_responsable = "SELECT matricule, nom, prenom FROM personne ORDER BY nom";
            $result_responsable = pg_query($con, $sql_responsable);

            // Remplir les options du menu déroulant avec les personnes
            while ($row_res = pg_fetch_assoc($result_responsable)) {
                echo "<option value='" . $row_res['matricule'] . "'>" . $row_res['nom'] . " " . $row_res['prenom'] . "</option>";
            }
            ?>
        </select><br><br>

        État :<br>
        <select name="numetat">
            <?php
            // Requête pour récupérer les deux premiers états
            $sql_etats = "SELECT numetat, nometat FROM etat ORDER BY numetat ASC LIMIT 2";
            $result_etats = pg_query($con, $sql_etats);

            // Remplir les options du menu déroulant
            while ($row_etat = pg_fetch_assoc($result_etats)) {
                echo "<option value='" . $row_etat['numetat'] . "'>" . $row_etat['nometat'] . "</option>";
            }
            ?>
        </select><br><br>

        <input type="submit" value="Valider le formulaire"><br><br>
        <input type="reset" value="Recommencer"><br><br>
        <a href="projet.php" class="back-button">Retour</a><br>
    </form>

    <a href="tache.php">Retour</a><br>

    <?php
    // Vérification si les indices existent avant d'utiliser les données POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Utilisation de isset() pour vérifier si chaque champ existe avant de l'utiliser
        $nomta = isset($_POST['nomta']) ? $_POST['nomta'] : '';
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        $deb = isset($_POST['deb']) ? $_POST['deb'] : '';
        $fin = isset($_POST['fin']) ? $_POST['fin'] : '';
        $numpro = isset($_POST['numpro']) ? $_POST['numpro'] : '';
        $responsable = isset($_POST['responsable']) ? $_POST['responsable'] : '';
        $numetat = isset($_POST['numetat']) ? $_POST['numetat'] : '';

        // Vérification des champs obligatoires
        if (!empty($nomta) && !empty($description) && !empty($deb) && !empty($fin) && !empty($numpro) && !empty($responsable) && !empty($numetat)) {
            // Vérifier que les dates de la tâche sont bien comprises dans les dates du projet
            $sql_dates_projet = "SELECT deb, fin FROM projet WHERE numpro = $numpro";
            $result_dates_projet = pg_query($con, $sql_dates_projet);

            if ($row_proj_dates = pg_fetch_assoc($result_dates_projet)) {
                $deb_proj = strtotime($row_proj_dates['deb']);
                $fin_proj = strtotime($row_proj_dates['fin']);
                $deb_tache = strtotime($deb);
                $fin_tache = strtotime($fin);

                if ($deb_tache < $deb_proj || $fin_tache > $fin_proj) {
                    echo "Erreur : Les dates de la tâche doivent être comprises entre " . $row_proj_dates['deb'] . " et " . $row_proj_dates['fin'] . " (dates du projet sélectionné).<br>";
                } elseif ($deb_tache > $fin_tache) {
                    echo "Erreur : La date de fin de la tâche ne peut pas être antérieure à la date de début.<br>";
                } else {
                    // Insérer la tâche dans la table `tache`
                    $sql_tache = "INSERT INTO tache (nomta, description, deb, fin, numpro, responsable, numetat)
                                  VALUES ('$nomta', '$description', '$deb', '$fin', $numpro, $responsable, $numetat)";
                    $result_tache = pg_query($con, $sql_tache);

                    if ($result_tache) {
                        // Récupérer le numéro de la tâche créée
                        $sql_get_numta = "SELECT numta FROM tache WHERE tache.nomta ='$nomta' AND tache.numpro=$numpro AND tache.description='$description'";
                        $resultat_numta = pg_query($con, $sql_get_numta);

                        if ($resultat_numta) {
                            $row_numta = pg_fetch_assoc($resultat_numta);
                            $numta = $row_numta['numta'];
                            echo "Tâche ajoutée avec succès. Le numéro de la tâche est : $numta <br>";
                        } else {
                            echo "Erreur : Impossible de récupérer le numéro de la tâche.<br>";
                        }
                    } else {
                        echo "Erreur lors de la création de la tâche : " . pg_last_error($con);
                    }
                }
            } else {
                echo "Erreur : Projet non trouvé.<br>";
            }
        } else {
            echo "Toutes les valeurs n'ont pas été entrées.<br>";
        }
    }
    ?>
</body>
</html>
