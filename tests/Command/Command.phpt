<?php declare(strict_types=1);

use Rajtik\Console\Command\Command;
use Rajtik\Console\Command\ExitCode;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('handle() result becomes numeric exit status', function () {
	foreach (ExitCode::cases() as $code) {
		$command = new class ($code) extends Command {
			public function __construct(
				private readonly ExitCode $code,
			) {
				parent::__construct('test');
			}


			protected function handle(InputInterface $input, OutputInterface $output): ExitCode
			{
				return $this->code;
			}
		};

		Assert::same($code->value, (new CommandTester($command))->execute([]));
	}
});

