<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown when the chosen time is no longer free (taken, blocked or outside the bookable window).
 */
class SlotUnavailableException extends Exception
{
    //
}
