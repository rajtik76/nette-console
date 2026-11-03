<?php declare(strict_types=1);

namespace Rajtik\Console\Generator;


final readonly class ClassName
{
	private function __construct(
		public string $namespace,
		public string $shortName,
		public string $file,
	) {
	}


	public static function resolve(string $input, string $baseNamespace, string $baseDirectory, string $suffix = ''): self
	{
		$parts = array_values(array_filter(
			preg_split('~[/\\\]+~', trim($input)) ?: [],
			fn(string $part): bool => $part !== '',
		));

		if ($parts === [] || array_filter($parts, fn(string $part): bool => !preg_match('~^[A-Za-z_][A-Za-z0-9_]*$~', $part))) {
			throw new \InvalidArgumentException("'$input' is not a valid class name.");
		}

		$parts = array_map(ucfirst(...), $parts);
		$shortName = array_pop($parts);
		if ($suffix !== '' && !str_ends_with($shortName, $suffix)) {
			$shortName .= $suffix;
		}

		return new self(
			implode('\\', [trim($baseNamespace, '\\'), ...$parts]),
			$shortName,
			implode('/', [rtrim($baseDirectory, '/\\'), ...$parts, $shortName . '.php']),
		);
	}


	public function baseName(string $suffix = ''): string
	{
		return $suffix !== '' && str_ends_with($this->shortName, $suffix) && $this->shortName !== $suffix
			? substr($this->shortName, 0, -strlen($suffix))
			: $this->shortName;
	}
}
