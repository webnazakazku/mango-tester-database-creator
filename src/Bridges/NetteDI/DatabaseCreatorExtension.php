<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator\Bridges\NetteDI;

use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Webnazakazku\MangoTester\DatabaseCreator\Bridges\NetteTester\DatabaseNameResolver;
use Webnazakazku\MangoTester\DatabaseCreator\DatabaseCreator;
use Webnazakazku\MangoTester\DatabaseCreator\DatabaseStrategyAccessor;
use Webnazakazku\MangoTester\DatabaseCreator\Drivers\MySqlDatabaseDriver;
use Webnazakazku\MangoTester\DatabaseCreator\Drivers\PostgreSqlDatabaseDriver;
use Webnazakazku\MangoTester\DatabaseCreator\IDatabaseNameResolver;
use Webnazakazku\MangoTester\DatabaseCreator\IDbal;
use Webnazakazku\MangoTester\DatabaseCreator\MigrationHashSuffixDatabaseNameResolver;
use Webnazakazku\MangoTester\DatabaseCreator\Mutex;
use Webnazakazku\MangoTester\DatabaseCreator\Strategies\ContinueOrResetDatabaseStrategy;
use Webnazakazku\MangoTester\DatabaseCreator\Strategies\ResetDatabaseStrategy;
use Webnazakazku\MangoTester\DatabaseCreator\Strategies\TemplateDatabaseStrategy;

/**
 * @property-read \stdClass $config
 */
class DatabaseCreatorExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'dbal' => Expect::string()->required(),
			'migrations' => Expect::string()->required(),
			'driver' => Expect::string()->required(),
			'strategy' => Expect::string()->required(),
			'databaseName' => Expect::structure([
				'format' => Expect::string(DatabaseNameResolver::DEFAULT_FORMAT),
				'type' => Expect::string('tester'),
				'migrationHashSuffix' => Expect::bool(false),
			]),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('mutex'))
			->setType(Mutex::class)
			->setArguments([$builder->parameters['tempDir']]);
		$builder->addDefinition($this->prefix('databaseCreator'))
			->setType(DatabaseCreator::class);

		$this->registerDbal();
		$this->registerMigrations();
		$this->registerDriver();
		$this->registerStrategy();
		$this->registerNameResolver();
	}

	private function registerDbal(): void
	{
		$builder = $this->getContainerBuilder();
		$def = $builder->addDefinition($this->prefix('dbal'));
		$def->setType(IDbal::class);
		$def->setFactory($this->config->dbal);
	}

	private function registerMigrations(): void
	{
		$builder = $this->getContainerBuilder();
		$def = $builder->addDefinition($this->prefix('migrationsDriver'));
		$def->setFactory($this->config->migrations);
	}

	private function registerDriver(): void
	{
		$builder = $this->getContainerBuilder();
		$def = $builder->addDefinition($this->prefix('databaseDriver'));

		if ($this->config->driver === 'postgres') {
			$def->setFactory(PostgreSqlDatabaseDriver::class);
		} elseif ($this->config->driver === 'mysql') {
			$def->setFactory(MySqlDatabaseDriver::class);
		}
	}

	private function registerStrategy(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addAccessorDefinition($this->prefix('databaseStrategyAccessor'))
			->setImplement(DatabaseStrategyAccessor::class)
			->setReference($this->prefix('@strategy'));

		$def = $builder->addDefinition($this->prefix('strategy'));
		if ($this->config->strategy === 'template') {
			$def->setFactory(TemplateDatabaseStrategy::class, [TemplateDatabaseStrategy::DEFAULT_FORMAT]);
		} elseif ($this->config->strategy === 'reset') {
			$def->setFactory(ResetDatabaseStrategy::class);
		} elseif ($this->config->strategy === 'continueOrReset') {
			$def->setFactory(ContinueOrResetDatabaseStrategy::class);
		} else {
			$def->setFactory($this->config->strategy);
		}
	}

	private function registerNameResolver(): void
	{
		$builder = $this->getContainerBuilder();

		$def = $builder->addDefinition($this->prefix('databaseNameResolver'));
		$def->setType(IDatabaseNameResolver::class);

		if ($this->config->databaseName->type === 'tester') {
			$def->setFactory(DatabaseNameResolver::class)
				->setArguments([$this->config->databaseName->format]);
		} else {
			$def->setFactory($this->config->databaseName->type);
		}

		if ($this->config->databaseName->migrationHashSuffix ?? false) {
			$def->setAutowired(false);
			$builder->addDefinition($this->prefix('databaseNameResolverDecorator'))
				->setType(IDatabaseNameResolver::class)
				->setFactory(MigrationHashSuffixDatabaseNameResolver::class, [
					'nameResolver' => $def,
				]);
		}
	}

}
