<?php

require '/app/vendor/autoload.php';

use Ramsey\Uuid\Uuid;

// Gerar um UUID v4 (aleatório)
$uuid4 = Uuid::uuid4()->toString();
echo "UUID v4: " . $uuid4 . "\n";