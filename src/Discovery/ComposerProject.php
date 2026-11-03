<?php declare(strict_types=1);

namespace Rajtik\Console\Discovery;

use Composer\Autoload\ClassLoader;
use Composer\InstalledVersions;
use Nette\Utils\FileSystem;
use Nette\Utils\Json;


final readonly class ComposerProject
{
	public function __construct(
		private string $vendorDir,
		private ?string $rootComposerFile = null,
	) {
	}


	public static function detect(): self
	{
		$vendorDir = dirname((string) (new \ReflectionClass(ClassLoader::class))->getFileName(), 2);

		return new self($vendorDir, InstalledVersions::getRootPackage()['install_path'] . '/composer.json');
	}


	/** @return list<array<string, mixed>> */
	public function installedPackages(): array
	{
		$installed = $this->readJson($this->installedFile());

		// Composer 1 stored a bare list, Composer 2 wraps it in "packages"
		return $installed['packages'] ?? $installed;
	}


	/** @return array<string, mixed> */
	public function rootExtra(): array
	{
		return $this->rootComposerFile !== null
			? $this->readJson($this->rootComposerFile)['extra'] ?? []
			: [];
	}


	/** @return list<string> */
	public function files(): array
	{
		return array_values(array_filter(
			[$this->installedFile(), $this->rootComposerFile],
			fn(?string $file): bool => $file !== null && is_file($file),
		));
	}


	private function installedFile(): string
	{
		return $this->vendorDir . '/composer/installed.json';
	}


	/** @return array<mixed> */
	private function readJson(string $file): array
	{
		return is_file($file)
			? Json::decode(FileSystem::read($file), forceArrays: true)
			: [];
	}
}
