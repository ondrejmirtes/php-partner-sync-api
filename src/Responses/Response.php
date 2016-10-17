<?php

namespace HelePartnerSyncApi\Responses;

use HelePartnerSyncApi\Client;

abstract class Response
{

	/**
	 * @var string
	 */
	private $secret;

	/**
	 * @param string $secret
	 */
	public function __construct($secret)
	{
		$this->secret = $secret;
	}

	public function render()
	{
		$response = json_encode(array(
			'success' => $this->isSuccessful(),
			'message' => $this->getMessage(),
			'data' => $this->getData(),
		), JSON_PRETTY_PRINT);

		$signature = hash_hmac(Client::SIGNATURE_ALGORITHM, $response, $this->secret);

		$this->send($signature, $response);
	}

	/**
	 * @return mixed
	 */
	abstract public function getData();

	/**
	 * @return bool
	 */
	abstract public function isSuccessful();

	/**
	 * @return string
	 */
	abstract public function getMessage();

	/**
	 * @param string $signature
	 * @param string $response
	 */
	private function send($signature, $response)
	{
		$httpCode = $this->isSuccessful() ? 200 : 500;

		header('HTTP/1.1 ' . $httpCode);
		header(Client::HEADER_SIGNATURE . ': ' . $signature);
		header(Client::HEADER_SIGNATURE_ALGORITHM . ': ' . Client::SIGNATURE_ALGORITHM);

		echo $response;
	}

}
