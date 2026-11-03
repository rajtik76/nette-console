<?php declare(strict_types=1);

namespace Rajtik\Console\Command;

use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


abstract class Command extends SymfonyCommand
{
	abstract protected function handle(InputInterface $input, OutputInterface $output): ExitCode;


	final protected function execute(InputInterface $input, OutputInterface $output): int
	{
		return $this->handle($input, $output)->value;
	}
}
