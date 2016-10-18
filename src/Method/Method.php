<?php

namespace HelePartnerSyncApi\Method;

use Closure;
use HelePartnerSyncApi\Request\Request;

abstract class Method
{

	/**
	 * @var Closure|null
	 */
	private $callback;

	/**
	 * @param Closure $callback
	 */
	public function __construct(Closure $callback)
	{
		$this->callback = $callback;
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function call(Request $request)
	{
		return $this->parseResponseData(call_user_func_array(
			$this->callback,
			$this->parseRequestData($request->getData())
		));
	}

	/**
	 * @return string
	 */
	abstract public function getName();

	/**
	 * @param mixed $data
	 * @return mixed
	 */
	abstract protected function parseResponseData($data);

	/**
	 * @param array $data
	 * @return array
	 */
	abstract protected function parseRequestData($data);

}