<?php

namespace App\Core\Exception;

class NotFoundException extends AppException {
	public function __construct(string $resource) {
        $message = $resource . ' not found';
        $code = 404;
		parent::__construct($message, $code, null);
	}

}