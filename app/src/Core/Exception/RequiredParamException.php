<?php

namespace App\Core\Exception;

class RequiredParamException extends AppException {
	public function __construct(array $params) {
        $message = 'One of the required params is empty, please check documentation: ' . json_encode($params);
        $code = 400;
		parent::__construct($message, $code, null);
	}

}