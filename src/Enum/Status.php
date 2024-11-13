<?php

namespace App\Enum;

enum Status : string
{
    case Open = "OPEN";
    case In_progress = "IN_PROGRESS";
    case Resolved = "RESOLVED";
    case Closed = "CLOSED";
}
