<!DOCTYPE html>
<html>

<head>
    <title>Rejoindre un Projet</title>
    <link rel="stylesheet" href="../style2.css">
</head>

<body>
    <h2>Rejoindre un Projet</h2>

    <?php
    include("../connexion.php");
    $connexion = connect();

    if (!$connexion) {
        echo "Problème de connexion à la base";
        exit;
    }

    // Vérifier si un partenaire souhaite rejoindre un projet
    if (isset($_POST['id_projet']) && isset($_POST['id_partenaire'])) {
        $id_projet = $_POST['id_projet'];
        $id_partenaire = $_POST['id_partenaire'];

        // Vérifier si le projet est dans un état valide (1 ou 2)
        $requete = "SELECT numetat FROM projet WHERE numpro = $id_projet";
        $resultat = pg_query($connexion, $requete);

        if ($resultat) {
            $projet = pg_fetch_assoc($resultat);

            // Vérifier si l'état du projet est 1 ou 2
            if ($projet['numetat'] != 1 && $projet['numetat'] != 2) {
                echo "Ce projet n'est pas dans un état valide pour rejoindre.";
            } else {
                // Vérifier si le partenaire fait déjà partie du projet
                $requete_verification = "SELECT COUNT(*) FROM partenaire_participant WHERE numpa = $id_partenaire AND numpro = $id_projet";
                $resultat_verification = pg_query($connexion, $requete_verification);
                $verification = pg_fetch_row($resultat_verification);

                if ($verification[0] > 0) {
                    echo "Vous faites déjà partie de ce projet.";
                } else {
                    // Ajouter le partenaire au projet
                    $requete_insertion = "INSERT INTO partenaire_participant (numpa, numpro) VALUES ($id_partenaire, $id_projet)";
                    if (pg_query($connexion, $requete_insertion)) {
                        echo "Vous avez rejoint le projet avec succès.";
                    } else {
                        echo "Erreur lors de l'ajout du partenaire au projet.";
                    }
                }
            }
        } else {
            echo "Le projet sélectionné n'existe pas.";
        }
    }

    // Récupérer les projets avec les états 1 ou 2
    $requete_projets = "SELECT * FROM projet WHERE numetat IN (1, 2)";
    $projets = pg_query($connexion, $requete_projets);

    // Récupérer les partenaires pour le formulaire
    $requete_partenaires = "SELECT numpa, nom FROM partenaire";
    $partenaires = pg_query($connexion, $requete_partenaires);
    ?>

    <form method="POST" action="rejoindre_projet.php">
        <!-- Sélectionner un partenaire -->
        <label for="id_partenaire">Sélectionnez votre Partenaire :</label><br>
        <select name="id_partenaire" id="id_partenaire" required>
            <option value="">Sélectionnez un partenaire</option>
            <?php while ($partenaire = pg_fetch_assoc($partenaires)): ?>
                <option value="<?= $partenaire['numpa'] ?>"><?= ($partenaire['nom']) ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <!-- Sélectionner un projet -->
        <label for="id_projet">Choisir un projet :</label><br>
        <select name="id_projet" id="id_projet" required>
            <option value="">Sélectionnez un projet</option>
            <?php while ($projet = pg_fetch_assoc($projets)): ?>
                <option value="<?= $projet['numpro'] ?>"><?= ($projet['nom']) ?> (<?= $projet['acronyme'] ?>)</option>
            <?php endwhile; ?>
        </select><br><br>

        <input type="submit" value="Valider"></input>

        <div class="center-container">
            <a href="../index.php" class="back-button">Retour à la page d'accueil</a>

        </div>
    </form>

</body>

</html>
