<?php declare(strict_types=1);

use Nette\Bootstrap\Configurator;
use Nette\DI\InvalidConfigurationException;
use Nette\Utils\FileSystem;
use Rajtik\Console\Discovery\ComposerProject;
use Rajtik\Console\Discovery\PackageDiscovery;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$project = __DIR__ . '/../fixtures/project';


function createConfigurator(PackageDiscovery $discovery): Configurator
{
	$configurator = new Configurator;
	$configurator->setTempDirectory(createTempDir());
	$configurator->setDebugMode(true);
	$discovery->attach($configurator);
	return $configurator;
}


test('registers discovered extension with expanded arguments', function () use ($project) {
	$container = createConfigurator(new PackageDiscovery(new ComposerProject($project . '/vendor', $project . '/composer.json')))
		->createContainer();

	Assert::same(['debugMode' => true], $container->getByType(ArrayObject::class)->getArrayCopy());
	Assert::false($container->hasService('ignored.greeting'));
});


test('extension defined by application wins over discovered one', function () use ($project) {
	$configurator = createConfigurator(new PackageDiscovery(new ComposerProject($project . '/vendor', $project . '/composer.json')));
	$configurator->addConfig(['extensions' => [
		'greeter' => new Nette\DI\Definitions\Statement(Rajtik\Console\Tests\fixtures\GreeterExtension::class, [false]),
	]]);

	Assert::same(['debugMode' => false], $configurator->createContainer()->getByType(ArrayObject::class)->getArrayCopy());
});


test('two packages claiming the same name fail loudly', function () {
	$vendor = createTempDir() . '/vendor';
	$extension = ['nette' => ['extensions' => ['greeter' => Rajtik\Console\Tests\fixtures\GreeterExtension::class]]];
	FileSystem::write($vendor . '/composer/installed.json', json_encode(['packages' => [
		['name' => 'acme/one', 'extra' => $extension],
		['name' => 'acme/two', 'extra' => $extension],
	]], JSON_THROW_ON_ERROR));

	Assert::exception(
		fn() => createConfigurator(new PackageDiscovery(new ComposerProject($vendor)))->createContainer(),
		InvalidConfigurationException::class,
		"Extension name 'greeter' is claimed by both 'acme/one' and 'acme/two'.%a%",
	);
});


test('unknown extension class fails loudly', function () {
	$vendor = createTempDir() . '/vendor';
	FileSystem::write($vendor . '/composer/installed.json', json_encode(['packages' => [
		['name' => 'acme/broken', 'extra' => ['nette' => ['extensions' => ['broken' => 'Acme\Missing']]]],
	]], JSON_THROW_ON_ERROR));

	Assert::exception(
		fn() => createConfigurator(new PackageDiscovery(new ComposerProject($vendor)))->createContainer(),
		InvalidConfigurationException::class,
		"Extension 'Acme\\Missing' from package 'acme/broken' not found%a%",
	);
});
