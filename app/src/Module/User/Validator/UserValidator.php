<?php
declare(strict_types = 1);

namespace App\Module\User\Validator;

use App\ValueObject\Email;
use App\ValueObject\Phone;
use App\ValueObject\CPF;
use App\Core\Exception\AppStackException;
use Throwable;

final class UserValidator {

    public static function validate(array $iDTO): array {
        $errors = [];

        /***************************************************************************************************/
        /*****************************************REQUIRED FIELDS*******************************************/
        /***************************************************************************************************/
        if (empty($iDTO['name'])) {
            $errors[] = "'name' is required";
        }

        if (empty($iDTO['login'])) {
            $errors[] = "'login' is required";
        }

        if (empty($iDTO['password'])) {
            $errors[] = "'password' is required";
        }

        if (empty($iDTO['email'])) {
            $errors[] = "'email' is required";
        } else {
            try {
                Email::fromString($iDTO['email']);
                $iDTO['email'] = Email::fromString($iDTO['email'])->__toString();
            } catch (Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (empty($iDTO['user_type_uuid'])) {
            $errors[] = "'user_type_uuid' is required";
        }

        if (empty($iDTO['cpf'])) {
            $errors[] = "'cpf' is required";
        } else {
            try {
                CPF::fromString($iDTO['cpf']);
                $iDTO['cpf'] = CPF::fromString($iDTO['cpf'])->__toString();
            } catch (Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }
        /***************************************************************************************************/
        /***************************************************************************************************/
        /***************************************************************************************************/


        if (!empty($iDTO['phone'])) {
            try {
                Phone::fromString($iDTO['phone']);
                $iDTO['phone'] = Phone::fromString($iDTO['phone'])->__toString();
            } catch (Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }
    

        
        if (!empty($errors)) {
            throw new AppStackException($errors, 400);
        }

        return $iDTO;
    }
}