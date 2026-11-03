<?php declare(strict_types=1);

namespace Rajtik\Console\Discovery;

use Nette\Neon\Entity;
use Nette\Neon\Neon;


final readonly class DiscoveredExtension
{
	/** @param list<mixed> $arguments */
	public function __construct(
		public string $package,
		public string $name,
		public string $class,
		public array $arguments = [],
	) {
	}


	public static function fromDefinition(string $package, string $name, string $definition): self
	{
		$parsed = Neon::decode($definition);

		return $parsed instanceof Entity
			? new self($package, $name, (string) $parsed->value, array_values($parsed->attributes))
			: new self($package, $name, (string) $parsed);
	}
}
