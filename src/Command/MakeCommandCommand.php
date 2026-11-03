<?php declare(strict_types=1);

namespace Rajtik\Console\Command;

use Rajtik\Console\Cache\TempCache;
use Rajtik\Console\Generator\ClassName;
use Rajtik\Console\Generator\FileGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;


#[AsCommand(name: 'make:command', description: 'Create a new console command')]
final class MakeCommandCommand extends GeneratorCommand
{
	private const string Suffix = 'Command';


	public function __construct(
		FileGenerator $generator,
		private readonly string $namespace,
		private readonly string $directory,
		?TempCache $cache = null,
		?string $stubDirectory = null,
	) {
		parent::__construct($generator, $cache, $stubDirectory);
	}


	protected function configure(): void
	{
		parent::configure();
		$this->addOption('command', null, InputOption::VALUE_REQUIRED, 'Terminal name of the command, defaults to app:<kebab-name>');
	}


	protected function stubName(): string
	{
		return 'command.stub';
	}


	protected function defaultStubDirectory(): string
	{
		return dirname(__DIR__) . '/stubs';
	}


	protected function targetFile(string $name, InputInterface $input): string
	{
		return $this->className($name)->file;
	}


	protected function replacements(string $name, InputInterface $input): array
	{
		$class = $this->className($name);
		$command = $input->getOption('command');

		return [
			'namespace' => $class->namespace,
			'class' => $class->shortName,
			'command' => is_string($command) && $command !== ''
				? $command
				: 'app:' . strtolower((string) preg_replace('~(?<=[a-z0-9])(?=[A-Z])~', '-', $class->baseName(self::Suffix))),
		];
	}


	private function className(string $name): ClassName
	{
		return ClassName::resolve($name, $this->namespace, $this->directory, self::Suffix);
	}
}
