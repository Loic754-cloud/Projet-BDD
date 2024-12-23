<!DOCTYPE html>
<html>

<head>
    <title>Créer un projet</title>
    <link rel="stylesheet" type="text/css" href="../style2.css">
</head>

<body>
    <h2>Créer un projet</h2>

    <?php
    include("../connexion.php");
    $con = connect();

    if (!$con) {
        echo "Problème de connexion à la base";
        exit;
    }

    // Vérification si le formulaire est soumis
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Récupérer les données du formulaire
        $acronyme = isset($_POST['acronyme']) ? $_POST['acronyme'] : '';
        $nom = isset($_POST['nom']) ? $_POST['nom'] : '';
        $description = isset($_POST['description']) ? $_POST['description'] : '';
        $deb = isset($_POST['deb']) ? $_POST['deb'] : '';
        $fin = isset($_POST['fin']) ? $_POST['fin'] : '';
        $numetat = isset($_POST['numetat']) ? $_POST['numetat'] : '';
        $numpa = isset($_POST['numpa']) ? $_POST['numpa'] : [];
        $responsable = isset($_POST['responsable']) ? $_POST['responsable'] : '';

        // Vérification des champs obligatoires
        if (!empty($acronyme) && !empty($nom) && !empty($description) && !empty($deb) && !empty($fin) && !empty($numpa) && !empty($numetat) && !empty($responsable)) {
            // Vérifier que la date de fin est postérieure à la date de début
            if (strtotime($deb) > strtotime($fin)) {
                $error_message = "Erreur : La date de fin ne peut pas être antérieure à la date de début.";
            } else {
                // Insérer le projet dans la table `projet`
                $sql_projet = "INSERT INTO projet (acronyme, nom, description, deb, fin, numetat, responsable)
                               VALUES ('$acronyme', '$nom', '$description', '$deb', '$fin', $numetat, $responsable)";
                $result_projet = pg_query($con, $sql_projet);

                if ($result_projet) {
                    echo "Projet ajouté avec succès.<br>";

                    // Récupérer le numéro du projet inséré
                    $sql_get_numpro = "SELECT numpro FROM projet WHERE projet.nom ='$nom' AND projet.description='$description' AND projet.acronyme='$acronyme'";
                    $resultat_numpro = pg_query($con, $sql_get_numpro);
                    $row_numpro = pg_fetch_assoc($resultat_numpro);
                    $numpro = $row_numpro['numpro'];

                    // Vérification de la valeur de $numpro
                    if (empty($numpro)) {
                        echo "Erreur : le numéro du projet n'a pas pu être récupéré.<br>";
                    } else {
                        echo "Le numéro du projet est : $numpro<br>";
                    }

                    // Ajouter les partenaires dans la table partenaire_participant
                    if (!empty($numpa)) {
                        foreach ($numpa as $partenaire_id) {
                            if (!empty($partenaire_id)) {
                                // Vérification supplémentaire pour éviter des partenaires vides
                                $sql_assoc = "INSERT INTO partenaire_participant (numpro, numpa)
                                              VALUES ($numpro, $partenaire_id)";
                                $result_assoc = pg_query($con, $sql_assoc);

                                if (!$result_assoc) {
                                    echo "Erreur lors de l'ajout du partenaire (ID: $partenaire_id) au projet.<br>";
                                }
                            }
                        }
                        echo "Les partenaires ont été associés au projet.<br>";
                    } else {
                        echo "Aucun partenaire sélectionné.<br>";
                    }

                } else {
                    echo "Erreur lors de la création du projet : " . pg_last_error($con);
                }
            }
        } else {
            $error_message = "Veuillez remplir tous les champs obligatoires.";
        }
    }
    ?>

    <form method="post" action="creer_projet.php">
        <!-- Acronyme -->
        <label for="acronyme">Acronyme :</label><br>
        <input type="text" name="acronyme" size="12" maxlength="25" value="<?php echo isset($acronyme) ? $acronyme : ''; ?>"><br><br>

        <!-- Nom -->
        <label for="nom">Nom :</label><br>
        <input type="text" name="nom" size="12" maxlength="25" value="<?php echo isset($nom) ? $nom : ''; ?>"><br><br>

        <!-- Description -->
        <label for="description">Description :</label><br>
        <textarea name="description" rows="4" cols="50" maxlength="200"><?php echo isset($description) ? $description : ''; ?></textarea><br><br>

        <!-- Date début -->
        <label for="deb">Date début :</label><br>
        <input type="date" name="deb" size="12" maxlength="25" value="<?php echo isset($deb) ? $deb : ''; ?>"><br><br>

        <!-- Date fin -->
        <label for="fin">Date fin :</label><br>
        <input type="date" name="fin" size="12" maxlength="25" value="<?php echo isset($fin) ? $fin : ''; ?>"><br><br>

        <!-- État -->
        <label for="numetat">État :</label><br>
        <select name="numetat">
            <?php
            $sql = "SELECT numetat, nometat FROM etat ORDER BY numetat ASC LIMIT 2";
            $result = pg_query($con, $sql);

            while ($row = pg_fetch_assoc($result)) {
                $selected = (isset($numetat) && $numetat == $row['numetat']) ? 'selected' : '';
                echo "<option value='" . $row['numetat'] . "' $selected>" . $row['nometat'] . "</option>";
            }
            ?>
        </select><br><br>

        <!-- Partenaires -->
        <label for="numpa">Partenaires :</label><br>
        <select name="numpa[]" id="numpa" multiple>
            <?php
            $sql_part = "SELECT numpa, nom AS nompa FROM partenaire ORDER BY numpa";
            $result_part = pg_query($con, $sql_part);

            while ($row_p = pg_fetch_assoc($result_part)) {
                $selected = (isset($numpa) && in_array($row_p['numpa'], $numpa)) ? 'selected' : '';
                echo "<option value='" . $row_p['numpa'] . "' $selected>" . $row_p['nompa'] . "</option>";
            }
            ?>
        </select><br><br>

        <!-- Responsable de projet -->
        <label for="responsable">Responsable de projet :</label><br>
        <select name="responsable" id="responsable">
            <?php
            $sql_responsable = "SELECT matricule, nom, prenom FROM personne ORDER BY nom";
            $result_responsable = pg_query($con, $sql_responsable);

            while ($row_res = pg_fetch_assoc($result_responsable)) {
                $selected = (isset($responsable) && $responsable == $row_res['matricule']) ? 'selected' : '';
                echo "<option value='" . $row_res['matricule'] . "' $selected>" . $row_res['nom'] . " " . $row_res['prenom'] . "</option>";
            }
            ?>
        </select><br><br>

        <!-- Affichage du message d'erreur s'il y en a -->
        <?php
        if (isset($error_message)) {
            echo "<div style='color: red;'>" . $error_message . "</div><br>";
        }
        ?>

        <!-- Boutons à la fin -->
        <input type="submit" value="Valider le formulaire"><br><br>
        <input type="reset" value="Recommencer"><br><br>

        <div class="center-container">
            <a href="projet.php" class="back-button">Retour</a><br>
        </div>
    </form>
</body>

</html>
