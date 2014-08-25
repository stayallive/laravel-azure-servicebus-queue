<?php

namespace Stayallive\LaravelAzureServicebusQueue;

use Illuminate\Http\Request;
use WindowsAzure\Common\ServicesBuilder;
use Illuminate\Queue\Connectors\ConnectorInterface;

class AzureConnector implements ConnectorInterface {

	/**
	 * The current request instance.
	 *
	 * @var \Illuminate\Http\Request;
	 */
	protected $request;

    /**
     * Create a new Azure connector instance.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Stayallive\LaravelAzureServicebusQueue\AzureConnector
     */
	public function __construct(Request $request) {
		$this->request = $request;
	}

	/**
	 * Establish a queue connection.
	 *
	 * @param  array $config
	 *
	 * @return \Illuminate\Queue\QueueInterface
	 */
	public function connect(array $config) {
        $connectionString = 'Endpoint=' . $config['endpoint'] . ';SharedSecretIssuer=' . $config['secretissuer'] . ';SharedSecretValue=' . $config['secret'];
		$serviceBusRestProxy = ServicesBuilder::getInstance()->createServiceBusService($connectionString);

		return new AzureQueue($serviceBusRestProxy, $this->request, $config['queue']);
	}

}