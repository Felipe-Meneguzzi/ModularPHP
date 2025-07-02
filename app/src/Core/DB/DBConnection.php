<?php
declare(strict_types=1);

namespace App\Core\DB;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;

interface IDBConnection {
    public function getConnection(): Connection;
}

class DBConnection implements IDBConnection {
	private Connection $connection;

	public function __construct(string $driver, string $host, string $database, string $username, string $password, string $charset) {
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver'    => $driver,
            'host'      => $host,
            'database'  => $database,
            'username'  => $username,
            'password'  => $password,
            'charset'   => $charset,
        ]);

        $capsule->bootEloquent();

        $this->connection = $capsule->getConnection();
	}

	public function getConnection(): Connection {
		return $this->connection;
	}

}