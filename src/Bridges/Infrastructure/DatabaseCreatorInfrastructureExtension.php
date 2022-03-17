<?php declare(strict_types = 1);

namespace Webnazakazku\MangoTester\DatabaseCreator\Bridges\Infrastructure;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use Nextras\Dbal\IConnection;
use Webnazakazku\MangoTester\DatabaseCreator\Bridges\InfrastructureNextrasDbal\NextrasDbalHook;
use Webnazakazku\MangoTester\DatabaseCreator\Bridges\InfrastructureNextrasDbal\NextrasDbalServiceHelpers;
use Webnazakazku\MangoTester\Infrastructure\MangoTesterExtension;

class DatabaseCreatorInfrastructureExtension extends CompilerExtension
{

	/** @var mixed[]  */
	public $defaults = [
		'nextrasDbal' => false,
	];

	public function __construct()
	{
		$this->defaults['nextrasDbal'] = interface_exists(IConnection::class);
	}


	public function loadConfiguration(): void
	{
		$config = $this->validateConfig($this->defaults);

		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('createDatabaseHook'))
			->setClass(DatabaseCreatorHook::class)
			->addTag(MangoTesterExtension::TAG_HOOK);

		if ($config['nextrasDbal']) {
			$this->setupNextrasDbal();
		}
	}


	protected function setupNextrasDbal(): void
	{
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('nextrasDbalHook'))
			->setClass(NextrasDbalHook::class)
			->addTag(MangoTesterExtension::TAG_HOOK);

		$serviceName = $builder->getByType(IConnection::class);
		$def = $serviceName ? $builder->getDefinition($serviceName) : null;
		if ($def && !isset($def->getTags()[MangoTesterExtension::TAG_REQUIRE])) {
			assert($def instanceof ServiceDefinition);
			NextrasDbalServiceHelpers::modifyConnectionDefinition($def);
		}
	}

}
