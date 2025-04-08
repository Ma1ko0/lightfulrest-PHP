<?php

declare(strict_types=1);

namespace App\User;

use Exception;

class UserNotFoundException extends Exception
{
	public $message = 'The user you requested does not exist.';
}
