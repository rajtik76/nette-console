<?php declare(strict_types=1);

namespace Rajtik\Console\DI;

use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Rajtik\Console\Cache\TempCache;
use Rajtik\Console\Command\CacheClearCommand;
use Rajtik\Console\Command\MakeCommandCommand;
use Rajtik\Console\Command\PackageDiscoverCommand;
use Rajtik\Console\Discovery\PackageDiscovery;
use Rajtik\Console\Generator\FileGenerator;


/**
 * @property-read \stdClass $config
 */
final class NetteConsoleExtension extends CompilerExtension
{
	public function __construct(
		private readonly bool $cliMode = false,
	) {
	}


	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'stubs' => Expect::string()->nullable(),
			'commands' => Expect::structure([
				'namespace' => Expect::string('App\Console'),
				'directory' => Expect::string()->nullable(),
			]),
		]);
	}


	public function loadConfiguration(): void
	{
		if (!$this->cliMode) {
			return;
		}

		$builder = $this->getContainerBuilder();
		$config = $this->config;

		$builder->addDefinition($this->prefix('tempCache'))
			->setFactory(TempCache::class, [$builder->parameters['tempDir'] . '/cache']);

		$builder->addDefinition($this->prefix('command.cacheClear'))
			->setFactory(CacheClearCommand::class);

		$builder->addDefinition($this->prefix('fileGenerator'))
			->setFactory(FileGenerator::class);

		$builder->addDefinition($this->prefix('discovery'))
			->setFactory([PackageDiscovery::class, 'fromComposer'])
			->setAutowired(false);

		$builder->addDefinition($this->prefix('command.packageDiscover'))
			->setFactory(PackageDiscoverCommand::class, [$this->prefix('@discovery')]);

		$builder->addDefinition($this->prefix('command.makeCommand'))
			->setFactory(MakeCommandCommand::class, [
				'namespace' => $config->commands->namespace,
				'directory' => $config->commands->directory ?? $builder->parameters['appDir'] . '/Console',
				'stubDirectory' => $config->stubs,
			]);
	}
}
