<?php declare(strict_types=1);

namespace Rajtik\Console;

use Contributte\Console\Application as ContributteApplication;


/**
 * Console application printing the nette banner above `list` and `--version`.
 */
class Application extends ContributteApplication
{
	private const string Banner = <<<'TXT'
		            _   _
		 _ __   ___| |_| |_ ___
		| '_ \ / _ \ __| __/ _ \
		| | | |  __/ |_| ||  __/
		|_| |_|\___|\__|\__\___|
		TXT;


	public function getLongVersion(): string
	{
		$name = $this->getName() !== 'UNKNOWN' ? $this->getName() : 'console';
		$version = $this->getVersion() !== 'UNKNOWN' ? ' ' . $this->getVersion() : '';

		// right-align the caption under the banner, ignoring formatter tags
		$width = max(array_map(strlen(...), explode("\n", self::Banner)));
		$indent = str_repeat(' ', max(0, $width - strlen($name . $version)));

		return '<fg=blue>' . self::Banner . "</>\n"
			. $indent . $name . ($version === '' ? '' : '<info>' . $version . '</info>');
	}
}
