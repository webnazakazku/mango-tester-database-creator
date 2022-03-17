<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator\Bridges\Infrastructure;

use Nette\DI\Container;
use Nette\DI\ContainerBuilder;
use Webnazakazku\MangoTester\DatabaseCreator\DatabaseCreator;
use Webnazakazku\MangoTester\Infrastructure\Container\AppContainerHook;

class DatabaseCreatorHook extends AppContainerHook
{

	/** @var DatabaseCreator */
	private $databaseCreator;

	public function __construct(DatabaseCreator $databaseCreator)
	{
		$this->databaseCreator = $databaseCreator;
	}


	public function onCompile(ContainerBuilder $builder): void
	{
		$builder->addImportedDefinition('databaseCreator')
			->setType(DatabaseCreator::class);
		$builder->resolve();
	}


	public function onCreate(Container $applicationContainer): void
	{
		$applicationContainer->addService('databaseCreator', $this->databaseCreator);
	}

}
