<?php declare(strict_types=1);

use Nette\Utils\FileSystem;
use Rajtik\Console\Command\MakeCommandCommand;
use Rajtik\Console\Generator\FileGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


function createTester(string $dir, ?string $stubs = null): CommandTester
{
	return new CommandTester(new MakeCommandCommand(new FileGenerator, 'App\Console', $dir, stubDirectory: $stubs));
}


test('generates command class with derived terminal name', function () {
	$dir = createTempDir();

	Assert::same(Command::SUCCESS, createTester($dir)->execute(['name' => 'Mail/SendDailyReport']));

	$source = FileSystem::read($dir . '/Mail/SendDailyReportCommand.php');
	Assert::true(str_starts_with($source, "<?php declare(strict_types=1);\n"));
	Assert::contains('namespace App\Console\Mail;', $source);
	Assert::contains("#[AsCommand(name: 'app:send-daily-report'", $source);
	Assert::contains('final class SendDailyReportCommand extends Command', $source);
	Assert::contains('use Rajtik\Console\Command\Command;', $source);
	Assert::contains('return ExitCode::Success;', $source);
	exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($dir . '/Mail/SendDailyReportCommand.php'), result_code: $lintResult);
	Assert::same(0, $lintResult);
});


test('--command overrides terminal name', function () {
	$dir = createTempDir();
	createTester($dir)->execute(['name' => 'Prune', '--command' => 'cache:prune']);

	Assert::contains("#[AsCommand(name: 'cache:prune'", FileSystem::read($dir . '/PruneCommand.php'));
});


test('refuses to overwrite unless forced', function () {
	$dir = createTempDir();
	FileSystem::write($dir . '/PruneCommand.php', 'original');

	$tester = createTester($dir);
	Assert::same(Command::FAILURE, $tester->execute(['name' => 'Prune']));
	Assert::contains('already exists', $tester->getDisplay());
	Assert::same('original', FileSystem::read($dir . '/PruneCommand.php'));

	Assert::same(Command::SUCCESS, $tester->execute(['name' => 'Prune', '--force' => true]));
	Assert::contains('class PruneCommand', FileSystem::read($dir . '/PruneCommand.php'));
});


test('application stub overrides package stub', function () {
	$dir = createTempDir();
	FileSystem::write($dir . '/stubs/command.stub', '{{ namespace }}|{{ class }}|{{ command }}');

	createTester($dir . '/out', $dir . '/stubs')->execute(['name' => 'Prune']);

	Assert::same('App\Console|PruneCommand|app:prune', FileSystem::read($dir . '/out/PruneCommand.php'));
});


test('invalid name fails', function () {
	$tester = createTester(createTempDir());

	Assert::same(Command::FAILURE, $tester->execute(['name' => 'Foo-Bar']));
	Assert::contains('not a valid class name', $tester->getDisplay());
});
