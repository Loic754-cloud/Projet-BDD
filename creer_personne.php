<!DOCTYPE html>
<html>

<head>
    <title>Créer une personne</title>
    <link rel="stylesheet" type="text/css" href="../style2.css">
</head>

<body>
    <h2>Créer une personne</h2>

    <?php
    include("../connexion.php");
    $con = connect();

    if (!$con) {
        echo "Problème de connexion à la base";
        exit;
    }

    // Vérification si le formulaire est soumis
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['valider'])) {
        // Récupérer les données du formulaire
        $matricule = isset($_POST['matricule']) ? $_POST['matricule'] : '';
        $nompers = isset($_POST['nompers']) ? $_POST['nompers'] : '';
        $prenompers = isset($_POST['prenompers']) ? $_POST['prenompers'] : '';
        $telephonepers = isset($_POST['telephonepers']) ? $_POST['telephonepers'] : '';
        $emailpers = isset($_POST['emailpers']) ? $_POST['emailpers'] : '';
        $numpa = isset($_POST['numpa']) ? $_POST['numpa'] : '';

        // Vérification des champs obligatoires
        if (!empty($nompers) && !empty($prenompers) && !empty($telephonepers) && !empty($emailpers) && !empty($numpa)) {
            // Vérification si l'identifiant existe déjà

                // Insérer la nouvelle personne dans la table `personne`
                $sql_personne = "INSERT INTO personne ( nom, prenom, telephone, email, numpa)
                                 VALUES ( '$nompers', '$prenompers', '$telephonepers', '$emailpers', '$numpa')";
                $resultat_personne = pg_query($con, $sql_personne);

                if (!$resultat_personne) {
                    $error_message = "Problème lors de l'ajout de la personne.";
                } else {
                    echo "Personne créée avec succès.<br>";
                }
            }
            else {
            $error_message = "Veuillez remplir tous les champs obligatoires.";
        }
    }
    ?>

    <form method="post" action="creer_personne.php">
        <input type="hidden" name="valider">


        <!-- Nom -->
        <label for="nompers">Nom :</label><br>
        <input type="text" name="nompers" value="<?php echo isset($nompers) ? $nompers : ''; ?>" required><br><br>

        <!-- Prénom -->
        <label for="prenompers">Prénom :</label><br>
        <input type="text" name="prenompers" value="<?php echo isset($prenompers) ? $prenompers : ''; ?>" required><br><br>

        <!-- Numéro de téléphone -->
        <label for="telephonepers">Numéro de téléphone :</label><br>
        <input type="text" name="telephonepers" value="<?php echo isset($telephonepers) ? $telephonepers : ''; ?>" required><br><br>

        <!-- Adresse mail -->
        <label for="emailpers">Adresse mail :</label><br>
        <input type="text" name="emailpers" value="<?php echo isset($emailpers) ? $emailpers : ''; ?>" required><br><br>

        <!-- Partenaire -->
        <label for="numpa">Partenaire :</label><br>
        <select name="numpa" required>
            <option value="">Sélectionnez un partenaire</option>
            <?php
            // Récupérer les partenaires existants
            $sql_partenaire = "SELECT numpa, nom FROM partenaire";
            $resultat_partenaire = pg_query($con, $sql_partenaire);

            if (!$resultat_partenaire) {
                echo "Erreur lors de la récupération des partenaires.";
            } else {
                while ($row = pg_fetch_array($resultat_partenaire)) {
                    $selected = (isset($numpa) && $numpa == $row['numpa']) ? 'selected' : '';
                    echo "<option value='" . $row['numpa'] . "' $selected>" . $row['nom'] . "</option>";
                }
            }
            ?>
        </select><br><br>

        <!-- Affichage du message d'erreur s'il y en a -->
        <?php
        if (isset($error_message)) {
            echo "<div style='color: red;'>" . $error_message . "</div><br>";
        }
        ?>

        <!-- Boutons de soumission -->
        <input type="submit" value="Valider le formulaire"><br><br>
        <input type="reset" value="Recommencer"><br><br>

        <div class="center-container">
            <a href="../index.php" class="back-button">Retour au menu principal</a><br>
        </div>
    </form>
</body>

</html>
