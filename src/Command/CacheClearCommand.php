<?php declare(strict_types=1);

namespace Rajtik\Console\Command;

use Rajtik\Console\Cache\TempCache;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(name: 'cache:clear', description: 'Remove compiled DI container, templates and other cached data')]
final class CacheClearCommand extends Command
{
	public function __construct(
		private readonly TempCache $cache,
	) {
		parent::__construct();
	}


	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->cache->clearAll();
		$output->writeln('<info>Cache cleared.</info>');
		return Command::SUCCESS;
	}
}
