<?php declare(strict_types=1);

use Nette\Utils\FileSystem;
use Rajtik\Console\Discovery\ComposerProject;
use Rajtik\Console\Discovery\DiscoveredExtension;
use Rajtik\Console\Discovery\PackageDiscovery;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';

$project = __DIR__ . '/../fixtures/project';


test('collects extensions of installed packages, skipping dont-discover ones', function () use ($project) {
	$discovery = new PackageDiscovery(new ComposerProject($project . '/vendor', $project . '/composer.json'));

	Assert::equal([
		new DiscoveredExtension(
			'acme/greeter',
			'greeter',
			'Rajtik\Console\Tests\fixtures\GreeterExtension',
			['%debugMode%'],
		),
	], $discovery->extensions());
});


test('without root composer.json nothing is ignored', function () use ($project) {
	$discovery = new PackageDiscovery(new ComposerProject($project . '/vendor'));

	Assert::same(['greeter', 'ignored'], array_map(fn($e) => $e->name, $discovery->extensions()));
});


test('dont-discover "*" disables discovery', function () use ($project) {
	$root = createTempDir() . '/composer.json';
	FileSystem::write($root, '{"extra": {"nette": {"dont-discover": ["*"]}}}');

	Assert::same([], (new PackageDiscovery(new ComposerProject($project . '/vendor', $root)))->extensions());
});


test('missing installed.json yields no extensions', function () {
	Assert::same([], (new PackageDiscovery(new ComposerProject(createTempDir())))->extensions());
});


test('definition without arguments', function () {
	$extension = DiscoveredExtension::fromDefinition('acme/a', 'a', 'Acme\FooExtension');

	Assert::same('Acme\FooExtension', $extension->class);
	Assert::same([], $extension->arguments);
});
