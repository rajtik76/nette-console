<?php declare(strict_types=1);

namespace Rajtik\Console\Cache;

use Nette\Utils\FileSystem;


final readonly class TempCache
{
	private const string ContainerDirectory = 'nette.configurator';


	public function __construct(
		private string $cacheDir,
	) {
	}


	public function clearContainer(): void
	{
		FileSystem::delete($this->cacheDir . '/' . self::ContainerDirectory);
	}


	public function clearAll(): void
	{
		FileSystem::delete($this->cacheDir);
	}
}
