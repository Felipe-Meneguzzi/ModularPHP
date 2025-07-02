<?php
declare(strict_types=1);

namespace App\Module\RequestLog\Service;

use App\Core\Exception\AppException;
use App\Entity\RequestLogEntity;
use App\Module\RequestLog\Repository\IRequestLogRepository;
use DateTime;

interface IRequestLogService {
	public function run(array $iDTO): void;
}

class RequestLogService implements IRequestLogService {
	public function __construct(protected IRequestLogRepository $repository) {}

	public function run(array $iDTO): void {
        $formatedTime = DateTime::createFromFormat('d/m/Y H:i:s', $iDTO['time']);

        $data = [
            'user_id' => $iDTO['user_id'],
            'uri' => $iDTO['uri'],
            'method' => $iDTO['method'],
            'headers' => json_encode($iDTO['headers']),
            'body' => json_encode($iDTO['body']),
            'cookies' => json_encode($iDTO['cookies']),
            'agent' => $iDTO['agent'],
            'time' => $formatedTime,
            'ip' => $iDTO['ip']
        ];

        $entity = new RequestLogEntity($data);

		if (!$this->repository->insert($entity)){
            throw new AppException('Cant insert request log in database');
        }
	}
}