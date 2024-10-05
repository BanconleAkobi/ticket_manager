<?php

namespace App\Enum;

enum Status : string
{
    case Ouvert = "OUVERT";
    case En_cours = "EN_COURS";
    case Resolu = "RESOLU";
    case Ferme = "FERME";
}
