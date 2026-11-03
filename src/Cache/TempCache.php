<?php declare(strict_types=1);

namespace Rajtik\Console\Cache;

use Nette\Utils\FileSystem;
use Nette\Utils\Finder;


// Deletes files but keeps directories, because RobotLoader of the running process writes its cache on shutdown
final readonly class TempCache
{
	private const string ContainerDirectory = 'nette.configurator';


	public function __construct(
		private string $cacheDir,
	) {
	}


	public function clearContainer(): void
	{
		$this->deleteFiles($this->cacheDir . '/' . self::ContainerDirectory);
	}


	public function clearAll(): void
	{
		$this->deleteFiles($this->cacheDir);
	}


	private function deleteFiles(string $directory): void
	{
		if (!is_dir($directory)) {
			return;
		}

		foreach (Finder::findFiles('*')->from($directory) as $file) {
			FileSystem::delete($file->getPathname());
		}
	}
}
