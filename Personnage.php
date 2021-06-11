<?php
//*Personnage.php

class Personnage //? Son rôle est de représenter les personnages de la BDD => correspond au controller
{

    //todo Ajouter les attributs de niveau et d'expérience ainsi que les setter, getters, champs bdd etc
    protected $_id;
    protected $_nom;
    protected $_degats;
    protected $_niveau;
    protected $_experience;
    protected $_strength;
    protected $_nbCoup;
    protected $staminaDate;

    //? Constante renvoyée par la méthode `frapper` si on se frappe soi-même.
    const CEST_MOI = 1;
    //? Constante renvoyée par la méthode `frapper` si on a tué le personnage en le frappant.
    const PERSONNAGE_TUE = 2;
    //? Constante renvoyée par la méthode `frapper` si on a bien frappé le personnage.
    const PERSONNAGE_FRAPPE = 3;
    //? Constante renvoyée par la méthode frapper si on a frappé 3 fois en moins de 24h
    const PERSONNAGE_REPOS = 4;





    //todo construct
    public function __construct(array $donnees)
    {
        $this->hydrate($donnees);
    }

    //todo hydratation
    //* Un tableau de données doit être passé à la fonction (d'où le préfixe « array »).

    public function hydrate(array $donnees)
    {
        //* On récupère le nom du setter correspondant à l'attribut.
        foreach ($donnees as $key => $value) {
            $method = "set" . ucfirst($key);
            // var_dump(($method));
            // var_dump(method_exists($this, $method));
            //* Si le setter correspondant existe.
            if (method_exists($this, $method)) {
                //* On appelle le setter.
                $this->$method($value);
            }
        }
    }

    public function frapper(Personnage $perso)
    {
        //* inflige des dégâts à l'adversaire
        // vérifier qu'on ne se frappe pas soi-même
        if ($perso->id() == $this->_id) {
            // Si c'est le cas, on stoppe tout en renvoyant une valeur signifiant que le personnage ciblé est le personnage qui attaque.
            return self::CEST_MOI;
        }
        // On vérifie s'il reste de la stamina
        elseif ($this->stamina()) {
            // On donne 10 points d'xp au personnage attaquant
            $this->xpUp();
            // On indique au personnage frappé qu'il doit recevoir des dégâts.
            return $perso->recevoirDegats($this->strength());
        } else {
            //sinon c'est que le personnage doit se reposer
            return self::PERSONNAGE_REPOS;
        }
    }

    public function recevoirDegats(int $dmg)
    {
        //* l'adversaire inflige des dégâts
        // On augmente de 5 les dégâts.
        $this->_degats += $dmg;
        // Si on a 100 de dégâts ou plus, la méthode renverra une valeur signifiant que le personnage a été tué.
        if ($this->_degats >= 100) {
            return self::PERSONNAGE_TUE;
        }
        // Sinon, elle renverra une valeur signifiant que le personnage a bien été frappé.
        return self::PERSONNAGE_FRAPPE;
    }



    public function xpUp()
    {
        $this->_experience += 10;
        if ($this->_experience >= 100) {
            return $this->levelUp();
        }
    }

    public function levelUp()
    {
        $this->_niveau++;
        $this->_strength += 2;
        $this->_experience = 0;
        $this->_nbCoup = ($this->_nbCoup == 2 xor $this->_nbCoup == 3) ? 1 : 0;
        $this->_degats = ($this->_degats <= 20) ? 0 : $this->_degats - 20;
        // if ($this->_degats <= 0) {
        //     $this->_degats = 0;
        // } else {
        //     $this->_degats -= 20;
        // }
    }

    public function stamina(): bool
    {
        // si le nombre de coup est inferieur à 3, on incrémente le nbre de coup, 
        if ($this->nbCoup() < 3) {
            $this->_nbCoup++;
            // on check la valeur de nbCoup => si =3 on récupère le timestamp,
            if ($this->nbCoup() == 3) {
                $this->setStaminaDate(time());
            }
            // on renvoie true
            return true;
        }
        // si le nombre de coup est égal à 3
        elseif ($this->nbCoup() == 3) {
            // on vérifie si le timestamp est plus grand que staminaDate + 86400,
            if (time() >= ($this->getStaminaDate() + 86400)) {
                // si c'est le cas le nombre de coup passe à 1 et on renvoie true,
                $this->setNbCoup(1);
                return true;
            } else {
                // sinon on renvoie false
                return false;
            }
        }
    }

    // public function stamina(): bool
    // {
    //     // si le nombre de coup est inferieur à 3, on incrémente le nbre de coup, 
    //     if ($this->nbCoup() < 3) {
    //         $this->_nbCoup++;
    //         // on check la valeur de nbCoup => si =3 on récupère le timestamp,
    //         if ($this->nbCoup() == 3) {
    //             $this->setStaminaDate(time());
    //         }
    //         // on renvoie true
    //         return true;
    //     }
    //     // si le nombre de coup est égal à 3
    //     elseif ($this->nbCoup() == 3) {
    //         // on vérifie si le timestamp est plus grand que staminaDate + 86400,
    //         if (time() >= ($this->getStaminaDate() + 86400)) {
    //             // si c'est le cas le nombre de coup passe à 1 et on renvoie true,
    //             $this->setNbCoup(1);
    //             return true;
    //         } else {
    //             // sinon on renvoie false
    //             return false;
    //         }
    //     }
    // }




    //* retourner true si le nom est vide.
    public function nomValide()
    {
        return (bool) !empty($this->_nom);
    }

    //todo getters

    public function id()
    {
        return $this->_id;
    }
    public function degats()
    {
        return $this->_degats;
    }
    public function nom()
    {
        return $this->_nom;
    }
    public function niveau()
    {
        return $this->_niveau;
    }
    public function experience()
    {
        return $this->_experience;
    }
    public function strength()
    {
        return $this->_strength;
    }
    public function nbCoup()
    {
        return $this->_nbCoup;
    }
    public function dateTimePremierCoup()
    {
        return $this->_dateTimePremierCoup;
    }
    public function dateTimeRecharge()
    {
        return $this->_dateTimeRecharge;
    }
    public function getStaminaDate()
    {
        return $this->staminaDate;
    }

    //todo setters
    public function setId(int $id)
    {
        if ($id > 0) {
            $this->_id = $id;
        }
    }
    public function setDegats(int $degats)
    {
        if ($degats >= 0 && $degats <= 100) {
            $this->_degats = $degats;
        }
    }
    public function setNom(string $nom)
    {
        $this->_nom = $nom;
    }
    public function setNiveau(string $niveau)
    {
        $this->_niveau = $niveau;
    }
    public function setExperience(string $experience)
    {
        $this->_experience = $experience;
    }
    public function setStrength(string $strength)
    {
        $this->_strength = $strength;
    }
    public function setNbCoup(string $nbCoup)
    {
        $this->_nbCoup = $nbCoup;
    }
    public function setStaminaDate($time)
    {
        $this->staminaDate = $time;
    }
}
