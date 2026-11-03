<?php declare(strict_types=1);

use Contributte\Console\Application;
use Contributte\Console\DI\ConsoleExtension;
use Nette\Bootstrap\Configurator;
use Rajtik\Console\Application as BannerApplication;
use Rajtik\Console\DI\NetteConsoleExtension;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


function createContainer(bool $cliMode): Nette\DI\Container
{
	$configurator = new Configurator;
	$configurator->setTempDirectory(createTempDir());
	$configurator->setDebugMode(true);
	$configurator->defaultExtensions['console'] = [ConsoleExtension::class, [$cliMode]];
	$configurator->defaultExtensions['netteConsole'] = [NetteConsoleExtension::class, [$cliMode]];
	return $configurator->createContainer();
}


test('registers commands in console mode', function () {
	$application = createContainer(cliMode: true)->getByType(Application::class);

	Assert::true($application->has('make:command'));
	Assert::true($application->has('package:discover'));
	Assert::true($application->has('cache:clear'));
});


test('replaces the application with the banner one', function () {
	$application = createContainer(cliMode: true)->getByType(Application::class);

	Assert::type(BannerApplication::class, $application);
	Assert::contains("|_| |_|\\___|\\__|\\__\\___|", $application->getLongVersion());
	Assert::match('%A%</>' . "\n" . '                 console', $application->getLongVersion());
});


test('registers nothing for web requests', function () {
	Assert::null(createContainer(cliMode: false)->getByType(Application::class, throw: false));
});
