<?php
//* index.php
//? http://localhost/POO/04-TP_mortal_kombat


//* tester l'autoload aussi
function chargerClasse($classname)
{
    require $classname . '.php';
}

spl_autoload_register('chargerClasse');

//* initier la session
session_start(); //todo On appelle session_start() APRÈS avoir enregistré l'autoload.

//* en cas de deconnexion, détruire la session
if (isset($_GET['deconnexion'])) {
    session_destroy();
    header('Location: .'); //* ' .' => renvoie à la racine
    die();
}

//* si la session existe tjrs on restaure l'objet
if (isset($_SESSION['perso'])) {
    $perso = $_SESSION['perso'];
}

//* créer une instance du PDO
require_once('./bdd.inc.php');

//* créer une instance du manager
$manager = new PersonnageManager($db);

//* si le joueur a cliqué sur Créer ce personnage. 
if (isset($_POST['creer']) && isset($_POST['nom'])) {


    //* création d'un objetPersonnage en passant au constructeur un tableau contenant une entrée (le nom du personnage). 
    $perso = new Personnage(['nom' => $_POST['nom']]);

    //* vérification de la validité du nom  
    if (!$perso->nomValide()) {
        $message = "Le nom choisi est invalide";
        unset($perso);
    }

    //* et qu'il n'existe pas déjà dans la BDD
    elseif ($manager->exists($perso->nom())) {
        $message = "Veuillez choisir un autre nom, " . $_POST['nom'] . " est déjà pris";
        unset($perso);
    } else {
        //* ajout du personnage dans la BDD
        $manager->add($perso);
    }
}

//* si le joueur a cliqué sur Utiliser ce personnage. 
elseif (isset($_POST['utiliser']) && isset($_POST['nom'])) {
    //* on vérifie si le personnage existe bien en BDD. 
    if (($manager->exists($_POST['nom']))) {
        //* Si c'est le cas, on le récupère de la BDD.
        $perso = $manager->get($_POST['nom']);
    } else {
        //* sinon...
        $message = "Le personnage " . $_POST['nom'] . " n'existe pas";
    }
}

//* si le joueur a cliqué sur un personnage pour le frapper
if (isset($_GET['frapper'])) {

    //* on vérifie que le personnage attaquant existe
    if (!isset($perso)) {
        $message = 'Merci de créer un personnage ou de vous identifier';
    } else {

        //* On vérifie que le personnage à frapper existe via son id
        if (!$manager->exists((int) $_GET['frapper'])) {
            $message = "Le personnage que vous voulez frapper n'existe pas";
        } else {
            //* On récupère le perso à frapper avec son id
            $persoAFrapper = $manager->get((int) $_GET['frapper']);

            //* On appelle la méthode frapper et on stock les éventuelles erreurs ou messages qu'elle renvoie
            $retour = $perso->frapper($persoAFrapper);

            switch ($retour) {
                case (Personnage::CEST_MOI):
                    $message = "Petit coquin de masochiste va !";

                    break;

                case (Personnage::PERSONNAGE_FRAPPE):
                    $message = $perso->nom() . " a frappé " . $persoAFrapper->nom() . " !";
                    $manager->update($perso);
                    $manager->update($persoAFrapper);
                    break;

                case (Personnage::PERSONNAGE_TUE):
                    $message = $perso->nom() . " a tué " . $persoAFrapper->nom() . " !";
                    $perso->levelUp();

                    $manager->update($perso);
                    $manager->delete($persoAFrapper);

                    break;

                case (Personnage::PERSONNAGE_REPOS):
                    //*afficher le temps restant avant de pouvoir frapper au format H:M:S
                    //le TMS du perso + 86400 - le TMS actuel => nbre de secondes restantes
                    $tempsRestant = $perso->getStaminaDate() + 86400 - time();
                    //pour avoir le nombre d'heure diviser par 3600
                    $heures = floor($tempsRestant/3600);
                    $reste = $tempsRestant - 3600*$heures;
                    //pour avoir le nombre de minutes diviser le reste par 60
                    $minutes = floor($reste / 60);
                    //le reste correspond au nombre de secondes
                    $secondes = $reste - 60*$minutes;
                    
                    $message = $perso->nom() . " a besoin de se reposer pendant encore ".$heures." heures ".$minutes." minutes et ".$secondes." secondes !";

                    break;
            }
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Mortal Kombat</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <p>Nombre de personnages créés : <?= $manager->count() ?>.</p>
    <?php
    //* s'il y a une erreur
    if (isset($message)) {
        //* on l'affiche
        echo "<p>$message</p>";
    }
    if (isset($perso)) {
        //todo afficher les informations du personnage choisi (nom et dégâts)
    ?>
        <p><a href="?deconnexion=1">Déconnexion</a></p>
        <fieldset>
            <legend>Mes informations</legend>
            <p>Personnage : <?= htmlspecialchars($perso->nom()) ?></p>
            <p>Dégâts : <?= $perso->degats() ?></p>
            <p>Niveau : <?= $perso->niveau() ?> </p>
            <p>Expérience : <?= $perso->experience() ?> </p>
            <p>Force : <?= $perso->strength() ?> </p>

            <?php


            $coupRestant = 3 - $perso->nbCoup();
            switch ($coupRestant) {
                case (0):
                    $cRestant = "Vous ne pouvez plus taper";
                    //todo afficher le temps qu'il reste à patienter
                    break;
                case (1):
                    $cRestant = "Dernier coup";
                    break;
                default:
                    $cRestant = "Coups restants";
            }
            $phrase = ($coupRestant > 1) ? $cRestant . " : " . $coupRestant : $cRestant;
            ?>

            <p> <?= $phrase ?> </p>

        </fieldset>
        <fieldset>
            <legend>Qui frapper ?</legend>
            <?php
            //* on récupére la liste des autres persos
            $persos = $manager->getList($perso->nom());
            if (empty($persos)) {
                echo 'Personne à frapper !';
            } else {
                //* affichage des personnage qu'il est possible de frapper
                foreach ($persos as $unPerso) {
                    echo '<p><a href="?frapper=', $unPerso->id(), '">', htmlspecialchars($unPerso->nom()), '</a> (dégâts : ', $unPerso->degats(), ', niveau : ' . $unPerso->niveau() . ', force : ' . $unPerso->strength() . ' )</p>';
                }
            }
            ?>
        </fieldset>

    <?php
    } else {
    ?>



        <form action="" method="POST">
            <label for="nom">Nom du personnage : <input type="text" id="nom" name="nom" maxlength="50"></label>
            <input type="submit" value="créer ce personnage" name="creer">
            <input type="submit" value="Utiliser ce personnage" name="utiliser">
        </form>
    <?php


    }
    ?>
</body>

</html>
<?php
//* Si on a créé un personnage, on le stocke dans une variable session afin d'économiser une requête SQL.
//! pourquoi après la balise html fermante ?
if (isset($perso)) {
    $_SESSION['perso'] = $perso; //!  différence avec la ligne 26 ?
}
?>