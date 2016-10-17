<?php

namespace HelePartnerSyncApi;

use Exception;
use HelePartnerSyncApi\Methods\Method;
use HelePartnerSyncApi\Responses\ErrorResponse;
use HelePartnerSyncApi\Responses\SuccessResponse;
use LogicException;
use Throwable;

class Client
{

	const VERSION = '1.0.0';

	const HEADER_SIGNATURE = 'X-Hele-Signature';
	const HEADER_SIGNATURE_ALGORITHM = 'X-Hele-Signature-Algorithm';

	const SIGNATURE_ALGORITHM = 'sha1';

	/**
	 * @var string
	 */
	private $secret;

	/**
	 * @var Method[]
	 */
	private $methods;

	/**
	 * @var RequestFactory
	 */
	private $requestFactory;

	/**
	 * @param string $secret
	 * @param RequestFactory $requestFactory
	 */
	public function __construct($secret, RequestFactory $requestFactory)
	{
		Validator::checkString($secret);

		$this->secret = $secret;
		$this->requestFactory = $requestFactory;
	}

	public function registerMethod(Method $method)
	{
		$this->methods[$method->getName()] = $method;
	}

	/**
	 * @return SuccessResponse|ErrorResponse
	 */
	public function run()
	{
		try {
			$request = $this->requestFactory->createRequest();

			$this->validateRequest($request);

			$method = $this->getMethod($request->getMethod());

			$responseData = $method->call($request);

		} catch (Exception $e) {
			return new ErrorResponse($this->secret, $e->getMessage());

		} catch (Throwable $e) {
			return new ErrorResponse($this->secret, $e->getMessage());
		}

		return new SuccessResponse(
			$this->secret,
			$responseData
		);
	}

	private function validateRequest(Request $request)
	{
		if ($request->getExpectedVersion() !== self::VERSION) {
			throw new LogicException(sprintf('Request expected version %s, but client is %s', $request->getExpectedVersion(), self::VERSION));
		}

		if (hash_hmac(self::SIGNATURE_ALGORITHM, $request->getRawBody(), $this->secret) !== $request->getSignature()) {
			throw new LogicException('Signature in HTTP Request is invalid!');
		}
	}

	/**
	 * @param string $method
	 * @return Method
	 */
	private function getMethod($method)
	{
		if (!isset($this->methods[$method])) {
			throw new LogicException(sprintf('Method %s was not registered!', $method));
		}

		return $this->methods[$method];
	}

}
