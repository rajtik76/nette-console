<?php declare(strict_types=1);

namespace Rajtik\Console\Command;

use Rajtik\Console\Cache\TempCache;
use Rajtik\Console\Generator\FileExistsException;
use Rajtik\Console\Generator\FileGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


abstract class GeneratorCommand extends Command
{
	public function __construct(
		private readonly FileGenerator $generator,
		private readonly ?TempCache $cache = null,
		private readonly ?string $stubDirectory = null,
	) {
		parent::__construct();
	}


	abstract protected function stubName(InputInterface $input): string;


	abstract protected function defaultStubDirectory(): string;


	abstract protected function targetFile(string $name, InputInterface $input): string;


	/** @return array<string, string> */
	abstract protected function replacements(string $name, InputInterface $input): array;


	protected function configure(): void
	{
		$this->addArgument('name', InputArgument::REQUIRED, 'Name of the generated class');
		$this->addOption('force', 'f', InputOption::VALUE_NONE, 'Overwrite the file if it exists');
	}


	protected function handle(InputInterface $input, OutputInterface $output): ExitCode
	{
		$name = (string) $input->getArgument('name');

		try {
			$target = $this->targetFile($name, $input);
			$this->generator->generate(
				$this->stubFile($input),
				$target,
				$this->replacements($name, $input),
				(bool) $input->getOption('force'),
			);
		} catch (FileExistsException | \InvalidArgumentException $e) {
			$output->writeln('<error>' . $e->getMessage() . '</error>');
			return ExitCode::Failure;
		}

		// new classes in `search` directories are picked up only by a recompiled container
		$this->cache?->clearContainer();
		$output->writeln("<info>Created</info> $target");
		return ExitCode::Success;
	}


	private function stubFile(InputInterface $input): string
	{
		$name = $this->stubName($input);
		$custom = $this->stubDirectory . '/' . $name;

		return $this->stubDirectory !== null && is_file($custom)
			? $custom
			: $this->defaultStubDirectory() . '/' . $name;
	}
}
