<?php declare(strict_types=1);

namespace Rajtik\Console\Discovery;

use Nette\DI\CompilerExtension;
use Nette\DI\Extensions\ExtensionsExtension;
use Nette\DI\Helpers;
use Nette\DI\InvalidConfigurationException;
use Nette\Schema\Expect;
use Nette\Schema\Schema;


// Only the ExtensionsExtension phase may add extensions; it runs after the app's `extensions:` section, so the app wins on name clash.
final class DiscoveryExtension extends ExtensionsExtension
{
	public function __construct(
		private readonly PackageDiscovery $discovery,
	) {
	}


	public function getConfigSchema(): Schema
	{
		return Expect::array();
	}


	public function loadConfiguration(): void
	{
		$this->compiler->addDependencies($this->discovery->dependencies());

		$registered = array_change_key_case($this->compiler->getExtensions(), CASE_LOWER);
		$discovered = [];

		foreach ($this->discovery->extensions() as $extension) {
			$key = strtolower($extension->name);
			if (isset($discovered[$key])) {
				throw new InvalidConfigurationException(sprintf(
					"Extension name '%s' is claimed by both '%s' and '%s'. Register one of them manually in the 'extensions' section.",
					$extension->name,
					$discovered[$key],
					$extension->package,
				));
			}

			$discovered[$key] = $extension->package;
			if (isset($registered[$key])) {
				continue;
			}

			$this->compiler->addExtension($extension->name, $this->instantiate($extension));
		}
	}


	private function instantiate(DiscoveredExtension $extension): CompilerExtension
	{
		if (!is_a($extension->class, CompilerExtension::class, allow_string: true)) {
			throw new InvalidConfigurationException(sprintf(
				"Extension '%s' from package '%s' not found or is not Nette\\DI\\CompilerExtension descendant.",
				$extension->class,
				$extension->package,
			));
		}

		$arguments = Helpers::expand($extension->arguments, $this->getContainerBuilder()->parameters);

		return new ($extension->class)(...$arguments);
	}
}
