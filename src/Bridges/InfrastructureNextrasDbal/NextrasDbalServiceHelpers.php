<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator\Bridges\InfrastructureNextrasDbal;

use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Nette\StaticClass;
use Webnazakazku\MangoTester\DatabaseCreator\DatabaseCreator;

class NextrasDbalServiceHelpers
{

	use StaticClass;

	public static function modifyConnectionDefinition(ServiceDefinition $definition): void
	{
		$factory = $definition->getFactory();
		assert($factory !== null);
		$args = $factory->arguments;
		$args['config'] = new Statement('array_merge(?, ?)', [
			$args['config'],
			[
				'database' => new Statement('@' . DatabaseCreator::class . '::getDatabaseName'),
			],
		]);
		$definition->setArguments($args);
		$definition->addSetup(['@' . DatabaseCreator::class, 'createTestDatabase']);
	}

}
