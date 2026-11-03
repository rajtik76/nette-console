<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();
Tester\Environment::setupFunctions();


function createTempDir(): string
{
	$dir = __DIR__ . '/tmp/' . getmypid() . '-' . bin2hex(random_bytes(4));
	Nette\Utils\FileSystem::createDir($dir);
	return $dir;
}
