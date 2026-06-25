<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator\Bridges\Infrastructure;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nextras\Dbal\IConnection;
use Webnazakazku\MangoTester\DatabaseCreator\Bridges\InfrastructureNextrasDbal\NextrasDbalHook;
use Webnazakazku\MangoTester\DatabaseCreator\Bridges\InfrastructureNextrasDbal\NextrasDbalServiceHelpers;
use Webnazakazku\MangoTester\Infrastructure\MangoTesterExtension;

/**
 * @property-read \stdClass $config
 */
class DatabaseCreatorInfrastructureExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'nextrasDbal' => Expect::bool(false)->default(interface_exists(IConnection::class)),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('createDatabaseHook'))
			->setType(DatabaseCreatorHook::class)
			->addTag(MangoTesterExtension::TAG_HOOK);

		if ($this->config->nextrasDbal) {
			$this->setupNextrasDbal();
		}
	}

	protected function setupNextrasDbal(): void
	{
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('nextrasDbalHook'))
			->setType(NextrasDbalHook::class)
			->addTag(MangoTesterExtension::TAG_HOOK);

		$serviceName = $builder->getByType(IConnection::class);
		$def = $serviceName !== null ? $builder->getDefinition($serviceName) : null;
		if ($def !== null && !isset($def->getTags()[MangoTesterExtension::TAG_REQUIRE])) {
			assert($def instanceof ServiceDefinition);
			NextrasDbalServiceHelpers::modifyConnectionDefinition($def);
		}
	}

}
