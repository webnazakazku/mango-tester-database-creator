<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator\Bridges\NetteDI;

use Nette\DI\CompilerExtension;
use Webnazakazku\MangoTester\DatabaseCreator\Bridges\NetteTester\DatabaseNameResolver;
use Webnazakazku\MangoTester\DatabaseCreator\Drivers\MySqlDatabaseDriver;
use Webnazakazku\MangoTester\DatabaseCreator\Drivers\PostgreSqlDatabaseDriver;
use Webnazakazku\MangoTester\DatabaseCreator\Strategies\ContinueOrResetDatabaseStrategy;
use Webnazakazku\MangoTester\DatabaseCreator\Strategies\ResetDatabaseStrategy;
use Webnazakazku\MangoTester\DatabaseCreator\Strategies\TemplateDatabaseStrategy;
use Webnazakazku\MangoTester\DatabaseCreator\DatabaseCreator;
use Webnazakazku\MangoTester\DatabaseCreator\DatabaseStrategyAccessor;
use Webnazakazku\MangoTester\DatabaseCreator\IDatabaseNameResolver;
use Webnazakazku\MangoTester\DatabaseCreator\IDbal;
use Webnazakazku\MangoTester\DatabaseCreator\MigrationHashSuffixDatabaseNameResolver;
use Webnazakazku\MangoTester\DatabaseCreator\Mutex;


class DatabaseCreatorExtension extends CompilerExtension
{
    /** @var array<mixed>  */
	public $defaults = [
		'dbal' => null,
		'migrations' => null,
		'driver' => null,
		'strategy' => null,
		'databaseName' => [
			'format' => DatabaseNameResolver::DEFAULT_FORMAT,
			'type' => 'tester',
			'migrationHashSuffix' => false,
		],
	];


	public function loadConfiguration(): void
	{
		$config = $this->validateConfig($this->defaults);

		assert($config['dbal'] !== null);
		assert($config['migrations'] !== null);
		assert($config['driver'] !== null);
		assert($config['strategy'] !== null);

		$builder = $this->getContainerBuilder();

		if (isset($config['testDatabaseFormat'])) {
			trigger_error('testDatabaseFormat is deprecated, use databaseName.format option instead', E_USER_DEPRECATED);
			$config['databaseName']['format'] = $config['testDatabaseFormat'];
		}

		$builder->addDefinition($this->prefix('mutex'))
			->setClass(Mutex::class)
			->setArguments([$builder->parameters['tempDir']]);
		$builder->addDefinition($this->prefix('databaseCreator'))
			->setClass(DatabaseCreator::class);

		$this->registerDbal($config['dbal']);
		$this->registerMigrations($config['migrations']);
		$this->registerDriver($config['driver']);
		$this->registerStrategy($config['strategy']);
		$this->registerNameResolver($config['databaseName']);
	}


	private function registerDbal(string $dbal): void
	{
		$builder = $this->getContainerBuilder();
		$def = $builder->addDefinition($this->prefix('dbal'));
		$def->setClass(IDbal::class);
		$def->setFactory($dbal);
	}


	private function registerMigrations(string $migrations): void
	{
		$builder = $this->getContainerBuilder();
		$def = $builder->addDefinition($this->prefix('migrationsDriver'));
		$def->setFactory($migrations);
	}


	private function registerDriver(string $driver): void
	{
		$builder = $this->getContainerBuilder();
		$def = $builder->addDefinition($this->prefix('databaseDriver'));

		if ($driver === 'postgres') {
			$def->setFactory(PostgreSqlDatabaseDriver::class);
		} elseif ($driver === 'mysql') {
			$def->setFactory(MySqlDatabaseDriver::class);
		}
	}


	private function registerStrategy(string $strategy): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addAccessorDefinition($this->prefix('databaseStrategyAccessor'))
			->setImplement(DatabaseStrategyAccessor::class)
			->setReference($this->prefix('@strategy'));

		$def = $builder->addDefinition($this->prefix('strategy'));
		if ($strategy === 'template') {
			$def->setFactory(TemplateDatabaseStrategy::class, [TemplateDatabaseStrategy::DEFAULT_FORMAT]);
		} elseif ($strategy === 'reset') {
			$def->setFactory(ResetDatabaseStrategy::class);
		} elseif ($strategy === 'continueOrReset') {
			$def->setFactory(ContinueOrResetDatabaseStrategy::class);
		} else {
			$def->setFactory($strategy);
		}
	}

    /**
     * @param array<string> $config
     */
	private function registerNameResolver(array $config): void
	{
		$builder = $this->getContainerBuilder();

		$def = $builder->addDefinition($this->prefix('databaseNameResolver'));
		$def->setClass(IDatabaseNameResolver::class);

		if ($config['type'] === 'tester') {
			$def->setFactory(DatabaseNameResolver::class)
				->setArguments([$config['format']]);
		} else {
			$def->setFactory($config['type']);
		}
		if ($config['migrationHashSuffix'] ?? false) {
			$def->setAutowired(false);
			$builder->addDefinition($this->prefix('databaseNameResolverDecorator'))
				->setClass(IDatabaseNameResolver::class)
				->setFactory(MigrationHashSuffixDatabaseNameResolver::class, [
					'nameResolver' => $def,
				]);
		}
	}

}
