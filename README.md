Nette Console
=============

Artisan-like console for [Nette](https://nette.org):

- [Symfony Console](https://symfony.com/doc/current/components/console.html) wired into DI via [contributte/console](https://github.com/contributte/console)
- **package discovery**: DI extensions of installed packages register themselves after `composer require`
- `make:*` generators with overridable stubs


Installation
------------

    composer require rajtik76/nette-console

Attach discovery in `Bootstrap`, before the container is created:

```php
use Rajtik\Console\Discovery\PackageDiscovery;

PackageDiscovery::fromComposer()->attach($this->configurator);
```

Add an entry script, e.g. `nette` in the project root:

```php
#!/usr/bin/env php
<?php declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

// debug mode recompiles the container on config changes; set NETTE_DEBUG=0 in production
$container = (new App\Bootstrap)->bootConsoleApplication(debugMode: getenv('NETTE_DEBUG') !== '0');
exit($container->getByType(Contributte\Console\Application::class)->run());
```

    php nette list


Commands
--------

| Command | Description |
|---|---|
| `make:command <Name>` | Creates `App\Console\<Name>Command`. `Mail/Report` → `App\Console\Mail\ReportCommand`, terminal name `app:report` (`--command` to override, `--force` to overwrite). |
| `package:discover` | Lists extensions registered by discovery. |
| `cache:clear` | Deletes files in `temp/cache` (DI container, Latte, ...). |

Commands extend `Rajtik\Console\Command\Command` and return an `ExitCode` from `handle()`;
the process exits with its numeric value (`Success` = 0, `Failure` = 1, `Invalid` = 2):

```php
use Rajtik\Console\Command\Command;
use Rajtik\Console\Command\ExitCode;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:send-report')]
final class SendReportCommand extends Command
{
    protected function handle(InputInterface $input, OutputInterface $output): ExitCode
    {
        return ExitCode::Success;
    }
}
```

Any service extending `Symfony\Component\Console\Command\Command` is a command. Register
generated commands through `search` in `services.neon`:

```neon
search:
    - in: %appDir%
      classes:
        - *Command
```


Package discovery
-----------------

A package declares its extensions in `composer.json` using NEON entity syntax:

```json
"extra": {
    "nette": {
        "extensions": {
            "migrations": "Acme\\Migrations\\DI\\MigrationsExtension(%consoleMode%)"
        }
    }
}
```

- The container is recompiled after `composer install/update` (production) and whenever `vendor/composer/installed.json` changes (debug mode).
- An extension of the same name in the application's `extensions:` section wins.
- Two packages claiming the same name fail the compilation.
- Opt out in the root `composer.json`:

  ```json
  "extra": {
      "nette": {
          "dont-discover": ["acme/migrations"]
      }
  }
  ```

`"*"` disables discovery entirely.


Configuration
-------------

```neon
netteConsole:
    stubs: %rootDir%/stubs          # application stubs override package ones (command.stub, ...)
    commands:
        namespace: App\Console
        directory: %appDir%/Console
```


Writing a generator
-------------------

Extend `Rajtik\Console\Command\GeneratorCommand` and implement `stubName()`, `defaultStubDirectory()`,
`targetFile()` and `replacements()`. Stub placeholders use `{{ key }}`. After generating, the
DI container cache is invalidated so new classes found by `search` are registered on the next run.


Checks
------

    composer run ci:check    # PHPStan level 8 + Nette Tester
