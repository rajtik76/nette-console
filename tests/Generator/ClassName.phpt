<?php declare(strict_types=1);

use Rajtik\Console\Generator\ClassName;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('appends suffix and resolves file', function () {
	$class = ClassName::resolve('sendReport', 'App\Console', '/app/Console', 'Command');

	Assert::same('App\Console', $class->namespace);
	Assert::same('SendReportCommand', $class->shortName);
	Assert::same('/app/Console/SendReportCommand.php', $class->file);
	Assert::same('SendReport', $class->baseName('Command'));
});


test('nested path becomes sub-namespace', function () {
	$class = ClassName::resolve('Mail\\SendReportCommand', 'App\Console\\', '/app/Console/', 'Command');

	Assert::same('App\Console\Mail', $class->namespace);
	Assert::same('SendReportCommand', $class->shortName);
	Assert::same('/app/Console/Mail/SendReportCommand.php', $class->file);
});


test('rejects invalid names', function () {
	foreach (['', '/', '1Foo', 'Foo-Bar', 'Foo/../Bar'] as $input) {
		Assert::exception(fn() => ClassName::resolve($input, 'App', '/app'), InvalidArgumentException::class);
	}
});
