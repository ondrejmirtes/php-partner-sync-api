<?php

namespace HelePartnerSyncApi;

use Closure;
use HelePartnerSyncApi\Methods\CheckSlots;
use HelePartnerSyncApi\Methods\CreateReservation;

class Application
{

	/**
	 * @var Client
	 */
	private $client;

	/**
	 * @var Request|null
	 */
	private $request;

	/**
	 * @param string $partnerId
	 */
	public function __construct($partnerId)
	{
		$this->client = new Client($partnerId);
	}

	public function setRequest(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * @param Closure $callback function (DateTime $date)
	 */
	public function onCheckSlots(Closure $callback)
	{
		$this->client->registerMethod(new CheckSlots($callback));
	}

	/**
	 * @param Closure $callback function (DateTime $startDateTime, DateTime $endDateTime, int $quantity, array $parameters)
	 */
	public function onCreateReservation(Closure $callback)
	{
		$this->client->registerMethod(new CreateReservation($callback));
	}

	public function run()
	{
		$request = $this->getRequest();

		return $this->client->run($request);
	}

	/**
	 * @return Request
	 */
	private function getRequest()
	{
		if ($this->request === null) {
			$this->request = new Request(file_get_contents('php://input'));
		}
		return $this->request;
	}

}
