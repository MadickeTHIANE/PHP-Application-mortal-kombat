<?php
//* PersonnageManager.php

require_once('./Personnage.php');

class PersonnageManager //? Son rôle est de gérer les personnages de la BDD => correspond au model
{

    //todo Quelles seront les caractéristiques de mes objets ?
    private $_db;


    //todo construct
    public function __construct($db)
    {
        $this->setDb($db);
    }

    //todo Quelles seront les fonctionnalités de mes objets ? => enregistrer, modifier, supprimer et sélectionner (également compter le nombre de personnages, récupérer une liste de plusieurs personnages, savoir si un personnage existe)

    // Enregistrer un nouveau personnage
    public function add(Personnage $perso)
    {
        //* Préparation de la requête d'insertion.
        $q = $this->_db->prepare('INSERT INTO perso_mk(nom) VALUES(:nom)');
        //* Assignation des valeurs pour le nom du personnage.
        $q->bindValue(':nom', $perso->nom());
        //* Exécution de la requête.
        $q->execute();
        //* Hydratation du personnage passé en paramètre avec assignation de son identifiant et des dégâts initiaux (= 0). 

        //* hydrate de la class Personnage
        $perso->hydrate([

            'id' => $this->_db->lastInsertId(), //* Retourne l'identifiant de la dernière ligne insérée dans la bdd ou la valeur d'une séquence
            'degats' => 0,
            'niveau' => 1,
            'strength' => 5,
            'experience' => 0,
            'nbCoup' => 0
        ]);
    }

    // Compter le nombre de personnages
    public function count()
    {
        //* Exécute une requête COUNT() et retourne le nombre de résultats retourné.
        return $this->_db->query('SELECT COUNT(*) FROM perso_mk')->fetchColumn();
    }

    // supprimer un personnage
    public function delete(Personnage $perso)
    {
        //* Exécute une requête de type DELETE.
        $this->_db->exec('DELETE FROM perso_mk WHERE id =' . $perso->id());
    }

    // savoir si un personnage existe
    public function exists($info)
    {
        //* On veut voir si tel personnage ayant pour id $info existe.
        if (is_int($info)) {
            //* On exécute alors une requête COUNT() avec une clause WHERE, et on retourne un boolean.
            return (bool) $this->_db->query('SELECT COUNT(*) FROM perso_mk WHERE id=' . $info)->fetchColumn(); //! pourquoi on a pas besoin de faire une requête préparée ?
            //* Sinon, c'est qu'on veut vérifier que le nom existe ou pas.
        }
        //* Sinon, c'est qu'on veut vérifier que le nom existe ou pas.

        $q = $this->_db->prepare('SELECT COUNT(*) FROM perso_mk WHERE nom = :nom');
        $q->execute([':nom' => $info]);

        return (bool)  $q->fetchColumn();
    }


    // sélectionner un personnage
    public function get($info)
    {
        //* Si le paramètre est un entier, on veut récupérer le personnage avec son identifiant.
        if (is_int($info)) {
            //* Exécute une requête de type SELECT avec une clause WHERE, et retourne un objet Personnage.
            $q = $this->_db->query('SELECT id, nom, degats, niveau, experience, strength, nbCoup, staminaDate FROM perso_mk WHERE id=' . $info);
            $donnees = $q->fetch(PDO::FETCH_ASSOC);
            return new Personnage($donnees);
        } else {
            //* Sinon, on veut récupérer le personnage avec son nom.
            $q = $this->_db->prepare('SELECT id, nom, degats, niveau, experience, strength, nbCoup, staminaDate FROM perso_mk WHERE nom=:nom');
            $q->execute([':nom' => $info]);
            $donnees = $q->fetch(PDO::FETCH_ASSOC);
            // var_dump($donnees);
            return new Personnage($donnees);
        }
        //* Exécute une requête de type SELECT avec une clause WHERE, et retourne un objet Personnage.
    }

    // récupérer une liste de plusieurs personnages
    public function getList($nom)
    {
        $persos = [];

        //* Retourner la liste de tous les personnages sauf celui utilisé
        $q = $this->_db->prepare('SELECT * FROM perso_mk WHERE NOT nom=:nom ORDER BY nom');

        $q->execute([':nom' => $nom]);

        //* Le résultat sera un tableau d'instances de Personnage.

        while ($donnees = $q->fetch(PDO::FETCH_ASSOC)) {
            $persos[] = new Personnage($donnees);
        }
        return $persos;
    }

    // modifier un personnage 
    public function update(Personnage $perso)
    {
        //* Prépare une requête de type UPDATE.
        if ($perso->nbCoup() == 3) {
            $q = $this->_db->prepare('UPDATE perso_mk SET degats = :degats, niveau = :niveau, experience = :experience, strength = :strength, nbCoup = :nbCoup, staminaDate = :staminaDate WHERE id=:id');
            //* Assignation des valeurs à la requête.
            $q->bindValue(':degats', $perso->degats(), PDO::PARAM_INT);
            $q->bindValue(':niveau', $perso->niveau(), PDO::PARAM_INT);
            $q->bindValue(':experience', $perso->experience(), PDO::PARAM_INT);
            $q->bindValue(':strength', $perso->strength(), PDO::PARAM_INT);
            $q->bindValue(':nbCoup', $perso->nbCoup(), PDO::PARAM_INT);
            $q->bindValue(':staminaDate', $perso->getStaminaDate(), PDO::PARAM_INT);
            $q->bindValue(':id', $perso->id(), PDO::PARAM_INT);
        } else {
            $q = $this->_db->prepare('UPDATE perso_mk SET degats = :degats, niveau = :niveau, experience = :experience, strength = :strength, nbCoup = :nbCoup WHERE id=:id');
            //* Assignation des valeurs à la requête.
            $q->bindValue(':degats', $perso->degats(), PDO::PARAM_INT);
            $q->bindValue(':niveau', $perso->niveau(), PDO::PARAM_INT);
            $q->bindValue(':experience', $perso->experience(), PDO::PARAM_INT);
            $q->bindValue(':strength', $perso->strength(), PDO::PARAM_INT);
            $q->bindValue(':nbCoup', $perso->nbCoup(), PDO::PARAM_INT);
            $q->bindValue(':id', $perso->id(), PDO::PARAM_INT);
        }
        //* Exécution de la requête.
        $q->execute();
    }


    //todo setters
    public function setDb(PDO $db)
    {
        $this->_db = $db;
    }
}
