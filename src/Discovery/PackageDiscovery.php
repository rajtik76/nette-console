<?php declare(strict_types=1);

namespace Rajtik\Console\Discovery;

use Nette\Bootstrap\Configurator;
use Nette\DI\Compiler;


final readonly class PackageDiscovery
{
	private const string Wildcard = '*';


	public function __construct(
		private ComposerProject $project,
	) {
	}


	public static function fromComposer(): self
	{
		return new self(ComposerProject::detect());
	}


	public function attach(Configurator $configurator): void
	{
		$configurator->onCompile[] = function (Configurator $configurator, Compiler $compiler): void {
			$compiler->addExtension('packageDiscovery', new DiscoveryExtension($this));
		};
	}


	/** @return list<DiscoveredExtension> */
	public function extensions(): array
	{
		$ignored = $this->ignoredPackages();
		if (in_array(self::Wildcard, $ignored, true)) {
			return [];
		}

		$extensions = [];
		foreach ($this->project->installedPackages() as $package) {
			$name = $package['name'] ?? null;
			$definitions = $package['extra']['nette']['extensions'] ?? [];
			if (!is_string($name) || !is_array($definitions) || in_array($name, $ignored, true)) {
				continue;
			}

			foreach ($definitions as $extensionName => $definition) {
				$extensions[] = DiscoveredExtension::fromDefinition($name, (string) $extensionName, (string) $definition);
			}
		}

		return $extensions;
	}


	/** @return list<string> */
	public function dependencies(): array
	{
		return $this->project->files();
	}


	/** @return list<string> */
	private function ignoredPackages(): array
	{
		return array_values((array) ($this->project->rootExtra()['nette']['dont-discover'] ?? []));
	}
}
