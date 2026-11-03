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


test('clearAll removes nested empty directories', function () {
	$cacheDir = createTempDir() . '/cache';
	FileSystem::write($cacheDir . '/app/_Nette.Database/a/b/item', '');
	FileSystem::write($cacheDir . '/nette.configurator/sub/Container_abc.php', '');

	$cache = new TempCache($cacheDir);
	$cache->clearContainer();
	Assert::true(is_dir($cacheDir . '/nette.configurator'));
	Assert::false(is_dir($cacheDir . '/nette.configurator/sub'));

	$cache->clearAll();
	Assert::true(is_dir($cacheDir . '/app'));
	Assert::false(is_dir($cacheDir . '/app/_Nette.Database'));
});


test('symlinks are not followed', function () {
	$dir = createTempDir();
	$cacheDir = $dir . '/cache';
	FileSystem::write($dir . '/shared/keep.php', '');
	FileSystem::write($dir . '/target.php', '');
	FileSystem::createDir($cacheDir . '/latte');
	symlink($dir . '/shared', $cacheDir . '/latte/shared');
	symlink($dir . '/target.php', $cacheDir . '/link.php');

	(new TempCache($cacheDir))->clearAll();

	Assert::true(is_file($dir . '/shared/keep.php'));
	Assert::true(is_link($cacheDir . '/latte/shared'));
	Assert::true(is_file($dir . '/target.php'));
	Assert::false(is_link($cacheDir . '/link.php'));
});


test('unreadable directory fails instead of being skipped', function () {
	$cacheDir = createTempDir() . '/cache';
	FileSystem::write($cacheDir . '/latte/template.php', '');
	chmod($cacheDir . '/latte', 0o000);
	if (is_readable($cacheDir . '/latte')) {
		Tester\Environment::skip('Running with permissions that ignore chmod.');
	}

	try {
		Assert::exception(fn() => (new TempCache($cacheDir))->clearAll(), Nette\InvalidStateException::class);
	} finally {
		chmod($cacheDir . '/latte', 0o755);
	}
});


test('clearing missing cache is a no-op', function () {
	Assert::noError(fn() => (new TempCache(createTempDir() . '/missing'))->clearAll());
});
