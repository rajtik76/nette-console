<?php declare(strict_types=1);

namespace Rajtik\Console\Command;

use Rajtik\Console\Discovery\PackageDiscovery;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(name: 'package:discover', description: 'List DI extensions registered by installed packages')]
final class PackageDiscoverCommand extends Command
{
	public function __construct(
		private readonly PackageDiscovery $discovery,
	) {
		parent::__construct();
	}


	protected function handle(InputInterface $input, OutputInterface $output): ExitCode
	{
		$extensions = $this->discovery->extensions();
		if ($extensions === []) {
			$output->writeln('No packages to discover.');
			return ExitCode::Success;
		}

		$table = new Table($output);
		$table->setHeaders(['Package', 'Extension', 'Class']);
		foreach ($extensions as $extension) {
			$table->addRow([$extension->package, $extension->name, $extension->class]);
		}

		$table->render();
		return ExitCode::Success;
	}
}
