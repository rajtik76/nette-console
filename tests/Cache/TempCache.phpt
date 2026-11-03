<?php declare(strict_types=1);

use Nette\Utils\FileSystem;
use Rajtik\Console\Cache\TempCache;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('clearContainer keeps other caches', function () {
	$cacheDir = createTempDir() . '/cache';
	FileSystem::write($cacheDir . '/nette.configurator/Container_abc.php', '');
	FileSystem::write($cacheDir . '/latte/template.php', '');

	(new TempCache($cacheDir))->clearContainer();

	Assert::false(is_file($cacheDir . '/nette.configurator/Container_abc.php'));
	Assert::true(is_file($cacheDir . '/latte/template.php'));
});


test('clearAll removes files and keeps directories', function () {
	$cacheDir = createTempDir() . '/cache';
	FileSystem::write($cacheDir . '/latte/template.php', '');
	FileSystem::write($cacheDir . '/nette.robotLoader/index.php', '');

	(new TempCache($cacheDir))->clearAll();

	Assert::false(is_file($cacheDir . '/latte/template.php'));
	Assert::false(is_file($cacheDir . '/nette.robotLoader/index.php'));
	Assert::true(is_dir($cacheDir . '/nette.robotLoader'));
});


test('clearing missing cache is a no-op', function () {
	Assert::noError(fn() => (new TempCache(createTempDir() . '/missing'))->clearAll());
});
