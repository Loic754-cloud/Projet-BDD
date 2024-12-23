<!DOCTYPE html>
<html>

<head>

    <title>Rejoindre une tâche</title>
    <link rel="stylesheet" href="../style2.css">
</head>

<body>
    <h2>Ajouter une personne à une tâche avec un rôle</h2>

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
        $matricule = isset($_POST['matricule']) ? $_POST['matricule'] : '';
        $numta = isset($_POST['numta']) ? $_POST['numta'] : '';
        $numpro = isset($_POST['numpro']) ? $_POST['numpro'] : '';
        $nomro = isset($_POST['nomro']) ? $_POST['nomro'] : ''; // Le nom du rôle
        $description = isset($_POST['description']) ? $_POST['description'] : ''; // La description du rôle

        // Vérification des champs obligatoires
        if (!empty($matricule) && !empty($numta) && !empty($numpro) && !empty($nomro) && !empty($description)) {
            // Ajouter la personne à la tâche avec le rôle
            $sql_role = "INSERT INTO role (matricule, numta, numpro, nomro, description)
                         VALUES ($matricule, $numta, $numpro, '$nomro', '$description')";
            $resultat_role = pg_query($con, $sql_role);

            if ($resultat_role) {
                // Récupérer le partenaire correspondant à la personne
                $sql_partenaire = "SELECT p.numpa FROM personne pe
                                   JOIN partenaire p ON pe.matricule = p.matricule
                                   WHERE pe.matricule = $matricule";

                $resultat_partenaire = pg_query($con, $sql_partenaire);

                if ($resultat_partenaire && pg_num_rows($resultat_partenaire) > 0) {
                    $row_partenaire = pg_fetch_assoc($resultat_partenaire);
                    $numpa = $row_partenaire['numpa'];

                    // Ajouter à la table partenaire_participant
                    $sql_participant = "INSERT INTO partenaire_participant (numpa, numpro)
                                        VALUES ($numpa, $numpro)";

                    $resultat_participant = pg_query($con, $sql_participant);

                    if ($resultat_participant) {
                        echo "La personne a été ajoutée avec succès à la tâche, et le partenaire a été lié au projet.";
                    } else {
                        echo "Erreur lors de l'ajout du partenaire au projet.";
                    }
                } else {
                    echo "Erreur : Aucune correspondance de partenaire trouvée pour cette personne.";
                }
            } else {
                echo "Erreur lors de l'ajout de la personne au rôle de la tâche : " . pg_last_error($con);
            }
        } else {
            $error_message = "Veuillez remplir tous les champs obligatoires.";
        }
    }
    ?>

    <form method="post" action="rejoindre_tache.php">
        <!-- Sélectionner une personne -->
        <label for="matricule">Personne :</label><br>
        <select name="matricule" id="matricule">
            <option value="">--Sélectionner une personne--</option>
            <?php
            // Récupérer les personnes disponibles
            $sql_personnes = "SELECT matricule, nom, prenom FROM personne ORDER BY nom, prenom";
            $result_personnes = pg_query($con, $sql_personnes);

            while ($row_personne = pg_fetch_assoc($result_personnes)) {
                echo "<option value='" . $row_personne['matricule'] . "'>" . $row_personne['nom'] . " " . $row_personne['prenom'] . "</option>";
            }
            ?>
        </select><br><br>

        <!-- Sélectionner un projet -->
        <label for="numpro">Projet associé :</label><br>
        <select name="numpro" id="numpro" onchange="this.form.submit()">
            <option value="">--Sélectionner un projet--</option>
            <?php
            // Récupérer les projets disponibles
            $sql_projets = "SELECT numpro, nom FROM projet WHERE numetat IN (1, 2) ORDER BY nom";
            $resultat_projets = pg_query($con, $sql_projets);

            while ($row_proj = pg_fetch_assoc($resultat_projets)) {
                $selected = (isset($_POST['numpro']) && $_POST['numpro'] == $row_proj['numpro']) ? 'selected' : '';
                echo "<option value='" . $row_proj['numpro'] . "' $selected>" . $row_proj['nom'] . "</option>";
            }
            ?>
        </select><br><br>

        <!-- Sélectionner une tâche -->
        <label for="numta">Nom de la tâche :</label><br>
        <select name="numta" id="numta">
            <?php
            // Si un projet est sélectionné, récupérer les tâches associées à ce projet
            if (isset($_POST['numpro']) && !empty($_POST['numpro'])) {
                $numpro = $_POST['numpro'];
                $sql_taches = "SELECT numta, nomta FROM tache WHERE numpro = $numpro AND numetat != 3 AND numetat != 4 ORDER BY nomta";
                $result_taches = pg_query($con, $sql_taches);

                if (pg_num_rows($result_taches) > 0) {
                    while ($row_ta = pg_fetch_assoc($result_taches)) {
                        echo "<option value='" . $row_ta['numta'] . "'>" . $row_ta['nomta'] . "</option>";
                    }
                } else {
                    echo "<option value=''>Aucune tâche disponible pour ce projet</option>";
                }
            } else {
                echo "<option value=''>Sélectionner un projet d'abord</option>";
            }
            ?>
        </select><br><br>

        <!-- Nom du rôle -->
        <label for="nomro">Nom du rôle :</label><br>
        <input type="text" name="nomro" size="12" maxlength="25"><br><br>

        <!-- Description du rôle -->
        <label for="description">Description du rôle :</label><br>
        <textarea name="description" rows="4" cols="50" maxlength="255"></textarea><br><br>

        <!-- Affichage du message d'erreur s'il y en a -->
        <?php
        if (isset($error_message)) {
            echo "<div style='color: red;'>" . $error_message . "</div><br>";
        }
        ?>

        <!-- Boutons à la fin -->
        <input type="submit" value="Valider"><br><br>

        <div class="center-container">
            <a href="../index.php" class="back-button">Retour à la page d'accueil</a>

        </div>
    </form>
</body>

</html>
