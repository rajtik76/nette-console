<?php declare(strict_types=1);

namespace Rajtik\Console\Tests\fixtures;

use Nette\DI\CompilerExtension;


final class GreeterExtension extends CompilerExtension
{
	public function __construct(
		public readonly bool $debugMode = false,
	) {
	}


	public function loadConfiguration(): void
	{
		$this->getContainerBuilder()->addDefinition($this->prefix('greeting'))
			->setType(\ArrayObject::class)
			->setFactory(\ArrayObject::class, [['debugMode' => $this->debugMode]]);
	}
}
