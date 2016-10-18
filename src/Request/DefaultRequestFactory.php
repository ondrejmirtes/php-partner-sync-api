<?php

namespace HelePartnerSyncApi\Request;

use HelePartnerSyncApi\Client;

class DefaultRequestFactory implements RequestFactory
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

	/**
	 * @return Request
	 */
	public function createRequest()
	{
		$headers = $this->getHeaders();

		if (!isset($headers[Client::HEADER_SIGNATURE])) {
			throw new RequestException(sprintf('Missing %s header in HTTP request', Client::HEADER_SIGNATURE));
		}

		if (!isset($headers[Client::HEADER_SIGNATURE_ALGORITHM])) {
			throw new RequestException(sprintf('Missing %s header in HTTP request', Client::HEADER_SIGNATURE_ALGORITHM));
		}

		return new Request(file_get_contents('php://input'), $this->secret, $headers[Client::HEADER_SIGNATURE], $headers[Client::HEADER_SIGNATURE_ALGORITHM]);
	}

	/**
	 * @return string[]
	 */
	private function getHeaders()
	{
		if (function_exists('apache_request_headers')) {
			return apache_request_headers();
		}

		$headers = array();
		foreach ($_SERVER as $k => $v) {
			if (strncmp($k, 'HTTP_', 5) == 0) {
				$k = substr($k, 5);
			} elseif (strncmp($k, 'CONTENT_', 8)) {
				continue;
			}
			$headers[strtr($k, '_', '-')] = $v;
		}

		return $headers;
	}

}
