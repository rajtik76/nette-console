<?php declare(strict_types=1);

namespace Rajtik\Console\Cache;

use Nette\Utils\FileInfo;
use Nette\Utils\FileSystem;
use Nette\Utils\Finder;


// Deletes files and nested empty directories but keeps top-level cache directories,
// because RobotLoader of the running process writes its cache on shutdown
final readonly class TempCache
{
	private const string ContainerDirectory = 'nette.configurator';


	public function __construct(
		private string $cacheDir,
	) {
	}


	public function clearContainer(): void
	{
		$this->clear($this->cacheDir . '/' . self::ContainerDirectory);
	}


	public function clearAll(): void
	{
		$this->clear($this->cacheDir);
	}


	private function clear(string $directory): void
	{
		if (!is_dir($directory)) {
			return;
		}

		// symlinked directories are neither entered nor removed, so nothing outside the cache is touched
		$entries = Finder::find()
			->from($directory)
			->descentFilter(fn(FileInfo $file): bool => !$file->isLink())
			->childFirst()
			->ignoreUnreadableDirs(false);

		foreach ($entries as $path => $file) {
			if (!$file->isDir()) {
				FileSystem::delete($path);
			} elseif (
				!$file->isLink()
				&& !($directory === $this->cacheDir && $file->getRelativePath() === '')
				&& !(new \FilesystemIterator($path))->valid()
			) {
				rmdir($path);
			}
		}
	}
}
