<?php declare(strict_types=1);

namespace Rajtik\Console\Generator;

use Nette\Utils\FileSystem;


final class FileGenerator
{
	/** @param array<string, string> $replacements */
	public function generate(string $stubFile, string $target, array $replacements, bool $force = false): void
	{
		if (!$force && file_exists($target)) {
			throw new FileExistsException("File '$target' already exists.");
		}

		$placeholders = array_map(fn(string $key): string => '{{ ' . $key . ' }}', array_keys($replacements));
		$content = str_replace($placeholders, array_values($replacements), FileSystem::read($stubFile));

		FileSystem::write($target, $content, mode: null);
	}
}
