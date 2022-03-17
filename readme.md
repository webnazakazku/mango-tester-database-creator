Mango Tester database creator
======
[![build](https://github.com/webnazakazku/mango-tester-database-creator/actions/workflows/main.yaml/badge.svg)](https://github.com/webnazakazku/mango-tester-database-creator/actions/workflows/main.yaml)

Testing helper for crate test databases with easy to use API with Mango Tester.

Installation
----

The recommended way to install is via Composer:

```
composer require webnazakazku/mango-tester-database-creator
```

It requires PHP version 7.1.

Integration & configuration
-----

Example of using:

`tests/config/tests.neon`

```neon
extensions:
	mango.tester: Webnazakazku\MangoTester\Infrastructure\MangoTesterExtension
	mango.tester.presenterTester: Webnazakazku\MangoTester\PresenterTester\Bridges\Infrastructure\PresenterTesterExtension
	mango.tester.databaseCreator: Webnazakazku\MangoTester\DatabaseCreator\Bridges\NetteDI\DatabaseCreatorExtension
	mango.tester.databaseCreatorInfrastructure: Webnazakazku\MangoTester\DatabaseCreator\Bridges\Infrastructure\DatabaseCreatorInfrastructureExtension
	- Webnazakazku\MangoTester\HttpMocks\Bridges\Infrastructure\HttpExtension

	nettrine.annotations: Nettrine\Annotations\DI\AnnotationsExtension
	nettrine.cache: Nettrine\Cache\DI\CacheExtension

	nettrine.dbal: Nettrine\DBAL\DI\DbalExtension

	migrations: Nextras\Migrations\Bridges\NetteDI\MigrationsExtension

	nettrine.extensions.beberlei: Nettrine\Extensions\Beberlei\DI\BeberleiBehaviorExtension

	nettrine.orm: Nettrine\ORM\DI\OrmExtension
	nettrine.orm.cache: Nettrine\ORM\DI\OrmCacheExtension
	nettrine.orm.annotations: Nettrine\ORM\DI\OrmAnnotationsExtension

parameters:
	appContainer:
		parameters:
			appDir: %appDir%
			wwwDir: %wwwDir%
			tempDir: %tempDir%
		configFiles:
			- %appDir%/config/config.neon
			- %appDir%/config/local.neon
		databaseConnectionServiceName: database.default.connection

migrations:
	dir: %appDir%/../migrations # migrations base directory
	driver: mysql               # pgsql or mysql
	dbal: doctrine              # nextras, nette, doctrine or dibi
	withDummyData: true
	phpParams:
		container: @container
		entityManager: @nettrine.orm.entityManagerDecorator

nettrine.extensions.beberlei:
    driver: mysql

nettrine.dbal:
	debug:
		panel: false
	connection:
		driver: pdo_mysql
		host: %dbHost%
		user: %dbUser%
		password: %dbPassword%
		dbname: %dbName%

nettrine.orm:
	# Own em class
	entityManagerDecoratorClass: PPIS\System\Model\EntityManagerDecorator

nettrine.orm.annotations:
	mapping:
		App\Model\Entities: '%appDir%/Model/Entities'

mango.tester.databaseCreator:
	driver: mysql
	dbal: Webnazakazku\MangoTester\DatabaseCreator\Bridges\NextrasMigrations\MySqlNextrasMigrationsDbalAdapter
	migrations: Webnazakazku\MangoTester\DatabaseCreator\Bridges\NextrasMigrations\NextrasMigrationsDriver
	strategy: reset
	databaseName:
		format: 'app_test_%d'

services:
	- AppTests\AppConfiguratorFactory
```

`src/AppConfiguratorFactory`

```php
<?php declare(strict_types = 1);

namespace AppTests;

use Nette\Configurator;
use Nette\DI\Container as DIContainer;
use Nette\DI\Definitions\Statement as DIStatement;
use Nette\Neon\Neon;
use Nette\Utils\Finder;
use Throwable;
use Webnazakazku\MangoTester\DatabaseCreator\DatabaseCreator;
use Webnazakazku\MangoTester\Infrastructure\Container\IAppConfiguratorFactory;

class AppConfiguratorFactory implements IAppConfiguratorFactory
{

	/** @var DatabaseCreator */
	private $databaseCreator;

	public function __construct(DatabaseCreator $databaseCreator)
	{
		$this->databaseCreator = $databaseCreator;
	}

	public function create(DIContainer $testContainer): Configurator
	{
		$testDatabaseName = $this->databaseCreator->getDatabaseName();

		$testContainerParameters = $testContainer->getParameters();

		$configurator = new Configurator();
		$configurator->setDebugMode(true);
		$configurator->setTempDirectory($testContainerParameters['tempDir']);

		$appDir = __DIR__ . '/../../app';
		$wwwDir = __DIR__ . '/../../temp/tests/www';

		$configurator->addParameters(
			[
				'appDir' => $appDir,
				'wwwDir' => $wwwDir,
			]
		);

		$configurator->addConfig(__DIR__ . '/../config/app.neon');

		$configurator->createRobotLoader()
			->addDirectory($appDir)
			->register();

		$configurator->addConfig($appDir . '/config/config.neon');
		$configurator->addConfig($appDir . '/config/tests.local.neon');

		$configurator->addConfig(
			[
				'console' => [
					'url' => null,
				],
				'nettrine.dbal' => [
					'debug' => [
						'panel' => false,
					],
					'connection' => [
						'dbname' => $testDatabaseName,
					],
				],
				'services' => [
					'nettrine.dbal.connection' => [
						'setup' => [
							new DIStatement('@databaseCreator::createTestDatabase'),
						],
					],
				],
			]
		);

		return $configurator;
	}

}
```