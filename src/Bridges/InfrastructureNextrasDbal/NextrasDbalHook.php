<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator\Bridges\InfrastructureNextrasDbal;

use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;
use Nextras\Dbal\Connection;
use Webnazakazku\MangoTester\Infrastructure\Container\AppContainerHook;


class NextrasDbalHook extends AppContainerHook
{
	public function onCompile(ContainerBuilder $builder): void
	{
		$def = $builder->getDefinitionByType(Connection::class);
		assert($def instanceof ServiceDefinition);
		NextrasDbalServiceHelpers::modifyConnectionDefinition($def);
	}
}
