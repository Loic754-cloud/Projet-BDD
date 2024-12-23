<!DOCTYPE html>
<html>

<head>
    <title>Créer un partenaire et un contact</title>
    <link rel="stylesheet" type="text/css" href="../style2.css">
</head>

<body>

    <h2>Créer un partenaire</h2>

    <?php
    include("../connexion.php");
    $con = connect();

    if (!$con) {
        echo "Problème de connexion à la base";
        exit;
    }

    // Récupérer les types de partenaires depuis la base de données
    $sql_types = "SELECT numtype, titre FROM typepartenaire";  // Sélectionner les types de partenaires
    $result_types = pg_query($con, $sql_types);

    if (!$result_types) {
        echo "Erreur de récupération des types : " . pg_last_error($con);
        exit;
    }

    // Vérification si le formulaire est soumis
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Récupérer les données du formulaire
        $numpa = isset($_POST['numpa']) ? $_POST['numpa'] : '';
        $nompa = isset($_POST['nompa']) ? $_POST['nompa'] : '';
        $adressepa = isset($_POST['adressepa']) ? $_POST['adressepa'] : '';
        $descriptionpa = isset($_POST['descriptionpa']) ? $_POST['descriptionpa'] : '';
        $numtype = isset($_POST['typepa']) ? $_POST['typepa'] : '';
        $matricule = isset($_POST['matricule']) ? $_POST['matricule'] : '';
        $nompers = isset($_POST['nompers']) ? $_POST['nompers'] : '';
        $prenompers = isset($_POST['prenompers']) ? $_POST['prenompers'] : '';
        $telephonepers = isset($_POST['telephonepers']) ? $_POST['telephonepers'] : '';
        $emailpers = isset($_POST['emailpers']) ? $_POST['emailpers'] : '';

        // Vérification des champs obligatoires
        if (!empty($numpa) && !empty($nompa) && !empty($adressepa) && !empty($descriptionpa) && !empty($numtype)
            && !empty($matricule) && !empty($nompers) && !empty($prenompers) && !empty($telephonepers) && !empty($emailpers)) {

            // Insérer le partenaire
            $sql_partenaire = "INSERT INTO partenaire (numpa, nom, adresse, description, numtype)
                               VALUES ('$numpa', '$nompa', '$adressepa', '$descriptionpa', '$numtype')";
            $result_partenaire = pg_query($con, $sql_partenaire);

            if ($result_partenaire) {
                echo "Partenaire ajouté avec succès.<br>";

                // Insérer le contact (personne)
                $sql_personne = "INSERT INTO personne (matricule, nom, prenom, telephone, email, numpa)
                                 VALUES ('$matricule', '$nompers', '$prenompers', '$telephonepers', '$emailpers', '$numpa')";
                $result_personne = pg_query($con, $sql_personne);

                if ($result_personne) {
                    echo "Contact ajouté avec succès.<br>";
                } else {
                    echo "Erreur lors de l'ajout du contact : " . pg_last_error($con) . "<br>";
                }
            } else {
                echo "Erreur lors de l'ajout du partenaire : " . pg_last_error($con) . "<br>";
            }

        } else {
            $error_message = "Veuillez remplir tous les champs obligatoires.";
        }
    }
    ?>

    <form method="post" action="creer_partenaire.php">
        <!-- Identifiant du partenaire -->
        <label for="numpa">Identifiant :</label><br>
        <input type="text" name="numpa" size="12" maxlength="25" value="<?php echo isset($numpa) ? $numpa : ''; ?>"><br><br>

        <!-- Nom du partenaire -->
        <label for="nompa">Nom :</label><br>
        <input type="text" name="nompa" size="12" maxlength="25" value="<?php echo isset($nompa) ? $nompa : ''; ?>"><br><br>

        <!-- Adresse du partenaire -->
        <label for="adressepa">Adresse :</label><br>
        <input type="text" name="adressepa" size="12" maxlength="100" value="<?php echo isset($adressepa) ? $adressepa : ''; ?>"><br><br>

        <!-- Description du partenaire -->
        <label for="description">Description :</label><br>
        <textarea name="description" rows="4" cols="50" maxlength="200"><?php echo isset($description) ? $description : ''; ?></textarea><br><br>

        <!-- Type du partenaire (Menu déroulant dynamique) -->
        <label for="typepa">Type de partenaire :</label><br>
        <select name="typepa">
            <option value="">Sélectionnez un type</option> <!-- Option vide pour forcer la sélection -->
            <?php
            // Afficher les options récupérées de la base de données
            while ($row = pg_fetch_assoc($result_types)) {
                $selected = (isset($numtype) && $numtype == $row['numtype']) ? 'selected' : '';
                echo "<option value='" . $row['numtype'] . "' $selected>" . $row['titre'] . "</option>";
            }
            ?>
        </select><br><br>

        <h3>Créer un contact pour le partenaire</h3>

        <!-- Nom du contact -->
        <label for="nompers">Nom :</label><br>
        <input type="text" name="nompers" size="12" maxlength="25" value="<?php echo isset($nompers) ? $nompers : ''; ?>"><br><br>

        <!-- Prénom du contact -->
        <label for="prenompers">Prénom :</label><br>
        <input type="text" name="prenompers" size="12" maxlength="25" value="<?php echo isset($prenompers) ? $prenompers : ''; ?>"><br><br>

        <!-- Téléphone du contact -->
        <label for="telephonepers">Numéro de téléphone :</label><br>
        <input type="text" name="telephonepers" size="12" maxlength="15" value="<?php echo isset($telephonepers) ? $telephonepers : ''; ?>"><br><br>

        <!-- Email du contact -->
        <label for="emailpers">Adresse mail :</label><br>
        <input type="text" name="emailpers" size="12" maxlength="50" value="<?php echo isset($emailpers) ? $emailpers : ''; ?>"><br><br>

        <!-- Affichage du message d'erreur s'il y en a -->
        <?php
        if (isset($error_message)) {
            echo "<div style='color: red;'>" . $error_message . "</div><br>";
        }
        ?>

        <!-- Boutons -->
        <input type="submit" value="Valider le formulaire"><br><br>
        <input type="reset" value="Recommencer"><br><br>

        <div class="center-container">
            <a href="partenaire.php" class="back-button">Retour</a><br>
        </div>
    </form>

</body>

</html>
