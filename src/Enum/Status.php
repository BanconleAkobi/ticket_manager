<?php

namespace App\Enum;

enum Status : string
{
    case Ouvert = "OUVERT";
    case En_cours = "EN COURS";
    case Resolu = "RÉSOLU";
    case Ferme = "FERMÉ";
}
