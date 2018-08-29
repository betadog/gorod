<?php

include 'service.php';


$service = new TroykaService();
$service->init();

$result = $service->getInfo('112233');