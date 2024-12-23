<!DOCTYPE html>
<html>


<head>

    <title>Rechercher des partenaires</title>
    <link rel="stylesheet" type="text/css" href="style5.css">

</head>



<body>
    <h2>Rechercher des partenaires, des personnes, des projets...</h2>
    <form method="POST">
        <input type="text" name="query" placeholder="Entrez un mot clé..." required>
        <button type="submit">Rechercher</button>
    </form>

    <div class="center-container">
     <a href="index.php" class="back-button">Retour à la page d'accueil</a>
    <button onclick="window.history.back();" class="back-button">Retour</button>
    </div>

<?php

    include("connexion.php");
    $con = connect();
    if (!$con) {
        echo "Problème connexion à la base";
        exit;
    }
    extract($_POST);

    // Vérifier si une requête de recherche a été soumise
    if (!isset($_POST['query'])) exit();

    $search = trim($_POST['query']); // Nettoyer l'entrée utilisateur

    // Requête SQL : Rechercher dans plusieurs colonnes
    $sql_part = "SELECT partenaire.numpa AS part_id,
            partenaire.description AS part_description,
            partenaire.nom AS part_nom,
            partenaire.adresse AS part_adresse,
            personne.matricule AS pers_id,
            personne.nom AS contact_nom,
            personne.prenom AS contact_prenom,
            personne.telephone AS contact_tel,
            typepartenaire.titre AS part_type
            FROM partenaire
            JOIN personne ON partenaire.contact=personne.matricule
            JOIN typepartenaire ON partenaire.numtype=typepartenaire.numtype
            WHERE partenaire.description ILIKE '%$search%'
            OR partenaire.nom ILIKE '%$search%'
            OR typepartenaire.titre ILIKE '%$search%'
            OR partenaire.adresse ILIKE '%$search%'";

    $sql_pers = "SELECT personne.matricule AS pers_id,
             personne.nom AS pers_nom,
             personne.prenom AS pers_prenom,
             personne.telephone AS pers_tel,
             personne.email AS pers_email,
             partenaire.nom AS part_nom,
             partenaire.numpa AS part_id
             FROM personne
             JOIN partenaire ON partenaire.numpa=personne.numpa
             WHERE personne.nom ILIKE '%$search%'
             OR personne.prenom ILIKE '%$search%'";

    $sql_proj = "SELECT projet.numpro AS projet_id,
            projet.description AS projet_description,
            projet.nom AS projet_nom,
            projet.acronyme AS projet_acronyme,
            etat.nometat AS projet_etat
            FROM projet
            LEFT JOIN etat ON etat.numetat=projet.numetat
            WHERE projet.description ILIKE '%$search%'
            OR projet.nom ILIKE '%$search%'
            OR projet.acronyme ILIKE '%$search%'";

    $sql_role = "SELECT personne.matricule AS pers_id,
             role.description AS role_desc,
             role.nomro AS role_nom,
             projet.numpro AS projet_id,
             projet.nom AS projet_nom,
             projet.acronyme AS projet_acronyme,
             tache.nomta AS tache_nom,
             tache.description AS tache_desc,
             etat.nometat AS tache_etat,
             personne.nom AS pers_nom,
             personne.prenom AS pers_prenom,
             personne.matricule AS pers_id
            FROM projet
            LEFT JOIN role ON role.numpro=projet.numpro
            LEFT JOIN tache ON role.numta = tache.numta
            LEFT JOIN personne ON personne.matricule=role.matricule
            LEFT JOIN etat ON tache.numetat=etat.numetat
            WHERE role.nomro ILIKE '%$search%'
            OR role.description ILIKE '%$search%'";

    $sql_tache = "SELECT projet.numpro AS projet_id,
             projet.nom AS projet_nom,
             projet.acronyme AS projet_acronyme,
             tache.nomta AS tache_nom,
             tache.numta AS tache_id,
             tache.description AS tache_desc,
             etat.nometat AS tache_etat,
             tache.numta AS tache_id
             FROM projet
             LEFT JOIN tache ON tache.numpro=projet.numpro
             LEFT JOIN etat ON tache.numetat = etat.numetat

             WHERE tache.nomta ILIKE '%$search%'
             OR tache.description ILIKE '%$search%'";


    // Récupérer les résultats
    $resultat_part = pg_query($sql_part);
    $resultat_pers = pg_query($sql_pers);
    $resultat_proj = pg_query($sql_proj);
    $resultat_role = pg_query($sql_role);
    $resultat_tache = pg_query($sql_tache);

    // Afficher les résultats
    echo "<h2>Résultats pour : " . $search . "</h2>";
    if ($resultat_part) {
        echo "<ul>";
        while ($row = pg_fetch_array($resultat_part)) {
              echo "<li>";

        // Créer un lien cliquable vers une page de détails avec l'ID du partenaire
        if (!empty($row['part_nom'])) {
            echo "<h4><strong>Nom du partenaire : </strong>";
            echo "<a href='partenaire/details_partenaire.php?numpa=" . $row['part_id'] . "'>";
            echo $row['part_nom'] . "</a></h4>";

        }
        if (!empty($row['part_type'])) echo "<strong>Type : </strong>" . $row['part_type'] . "<br>";
        if (!empty($row['part_description'])) echo "<strong>Description : </strong>" . $row['part_description'] . "<br>";
        if (!empty($row['part_adresse'])) echo "<strong>Adresse : </strong>" . $row['part_adresse'] . "<br>";
        if (!empty($row['contact_nom'])) {
            echo "<strong>Contact : </strong>";
            echo "<a href='partenaire/details_personne.php?matricule=" . $row['pers_id'] . "'>";
            echo $row['contact_prenom'] . " " . $row['contact_nom'] . "</a><br>";
        }
        if (!empty($row['contact_tel'])) echo "<strong>Numéro de téléphone : </strong>" . $row['contact_tel'] . "<br>";

        echo "<a href='partenaire/modifier_partenaire.php?numpa=" . $row['part_id'] . "' class='button'>Modifier</a>";
        echo "</li>";
        }
        echo "</ul>";
    }

    if ($resultat_proj){
        echo "<ul>";
        while ($row = pg_fetch_array($resultat_proj)) {
              echo "<li>";

        // Créer un lien cliquable vers une page de détails avec l'ID du projet
        if (!empty($row['projet_nom'])) {
            echo "<h4><strong>Nom du projet : </strong>";
            echo "<a href='projet/details_projet.php?numpro=" . $row['projet_id'] . "'>";
            echo $row['projet_nom'] . "</a></h4>";

        }
        if (!empty($row['projet_acronyme'])) echo "<strong>Acronyme : </strong>" . $row['projet_acronyme'] . "<br>";
        if (!empty($row['projet_description'])) echo "<strong>Description : </strong>" . $row['projet_description'] . "<br>";
        if (!empty($row['projet_etat'])) echo "<strong> Etat du projet : </strong>" . $row['projet_etat'] . "<br>";
        echo "<a href='projet/modifier_projet.php?numpro=" . $row['projet_id'] . "' class='button'>Modifier</a>";


        echo "</li>";
        }
        echo "</ul>";
    }

    if($resultat_pers){
        echo "<ul>";
        while ($row = pg_fetch_array($resultat_pers)) {
              echo "<li>";


        if (!empty($row['pers_nom'])) {
            echo "<h4><strong>Prénom et Nom de la personne : </strong>";
            echo "<a href='partenaire/details_personne.php?matricule=" . $row['pers_id'] . "'>";
            echo $row['pers_prenom'] . " " . $row['pers_nom'] . "</a></h4>";
            echo "<strong>Numéro de téléphone : </strong>" . $row['pers_tel'] . "<br>";
            echo "<strong>Adresse email : </strong>" . $row['pers_email'] . "<br>";
        }


        if (!empty($row['part_nom'])) {
            echo "<strong> Nom du partenaire : </strong>";
            echo "<a href='partenaire/details_partenaire.php?numpa=" . $row['part_id'] . "'>";
            echo $row['part_nom'] . "</a><br>";
        }

        echo "<a href='partenaire/modifier_personne.php?matricule=" . $row['pers_id'] . "' class='button'>Modifier</a>";
        echo "</li>";
        }
        echo "</ul>";
    }

    if($resultat_role){
        echo "<ul>";
        while ($row = pg_fetch_array($resultat_role)) {
              echo "<li>";
        if (!empty($row['role_nom'])) echo "<h4><strong>Nom du rôle : </strong>" . $row['role_nom'] . "</h4>";
        if (!empty($row['role_desc'])) echo "<strong>Description du rôle : </strong>" . $row['role_desc'] . "<br>";

        if (!empty($row['tache_nom'])) {
            echo "<strong>Nom de la tache : </strong>";
            echo "<a href='projet/details_tache.php?numta=" . $row['tache_id'] . "'>";
            echo $row['tache_nom'] . "</a><br>";
        }
        if (!empty($row['tache_desc'])) echo "<strong>Description de la tache : </strong>" . $row['tache_desc'] . "<br>";
        if (!empty($row['pers_nom'])) {
            echo "<strong> Prénom et nom : </strong>";
            echo "<a href='partenaire/details_personne.php?numpers=" . $row['pers_id'] . "'>";
            echo $row['pers_prenom'] . $row['pers_nom'] . "</a><br>";
        }

        if (!empty($row['projet_nom'])) {
            echo "<strong>Nom du projet : </strong>";
            echo "<a href='projet/details_projet.php?numpro=" . $row['projet_id'] . "'>";
            echo $row['projet_nom'] . "</a><br>";
        }

        if (!empty($row['part_nom'])) {
            echo "<strong>Nom du partenaire : </strong>";
            echo "<a href='partenaire/details_partenaire.php?numpa=" . $row['part_id'] . "'>";
            echo $row['part_nom'] . "</a><br>";

        }

        echo "<a href='projet/rejoindre_tache.php?matricule=" . $row['pers_id'] . "' class='button'>Modifier</a>";
        echo "</li>";
        }
        echo "</ul>";
    }

    if($resultat_tache){
        echo "<ul>";
        while ($row = pg_fetch_array($resultat_tache)) {
              echo "<li>";

        if (!empty($row['tache_nom'])) {
            echo "<h4><strong>Nom de la tache : </strong>";
            echo "<a href='projet/details_tache.php?numta=" . $row['tache_id'] . "'>";
            echo $row['tache_nom'] . "</a></h4>";
        }
        if (!empty($row['tache_desc'])) echo "<strong>Description de la tache : </strong>" . $row['tache_desc'] . "<br>";
        if (!empty($row['tache_etat'])) echo "<strong>Etat de la tache : </strong>" . $row['tache_etat'] . "<br>";

        if (!empty($row['projet_nom'])) {
            echo "<strong>Nom du projet : </strong>";
            echo "<a href='projet/details_projet.php?numpro=" . $row['projet_id'] . "'>";
            echo $row['projet_nom'] . "</a><br>";
        }


        echo "<a href='projet/modifier_tache.php?numta=" . $row['tache_id'] . "' class='button'>Modifier</a>";
        echo "</li>";
        }
        echo "</ul>";
    }


    if(!$resultat_part && !$resultat_pers && !$resultat_proj && !$resultat_tache) {
        echo "<div class='no-results'>Aucun résultat trouvé.</div>";
    }

?>

<input type="submit" name='submit' value="valider le formulaire">
<input type="reset" value="recommencer">

</body>

</html>



