<?php

namespace HelePartnerSyncApi\Method;

use DateTime;

class GetSlotsTest extends MethodTestCase
{

	public function testSuccess()
	{
		$today = new DateTime('today');
		$request = $this->getRequestMock(array(
			'date' => $today->format(DateTime::W3C),
			'parameters' => array(),
		));

		$startDateTime = new DateTime('+1 hour');
		$endDateTime = new DateTime('+2 hours');
		$closure = function () use ($startDateTime, $endDateTime) {
			return array(
				array(
					'startDateTime' => $startDateTime,
					'endDateTime' => $endDateTime,
					'capacity' => 1,
				),
			);
		};
		$method = new GetSlots($closure);
		$response = $method->call($request);

		$this->assertSame(array(
			array(
				'startDateTime' => $startDateTime->format(DateTime::W3C),
				'endDateTime' => $endDateTime->format(DateTime::W3C),
				'capacity' => 1,
			),
		), $response);
	}

	public function testFailures()
	{
		$now = new DateTime();

		$this->checkException(
			array(),
			array(),
			'Missing keys (date, parameters)'
		);
		$this->checkException(
			array(
				'date' => 'now',
				'parameters' => array(),
			),
			array(
				array(),
			),
			'W3C datetime expected, string (now) given.'
		);
		$this->checkException(
			array(
				'date' => $now->format(DateTime::W3C),
				'parameters' => array(),
			),
			array(
				array(
					'startDateTime' => new DateTime(),
				)
			),
			'Missing keys (endDateTime, capacity)'
		);
		$this->checkException(
			array(
				'date' => $now->format(DateTime::W3C),
				'parameters' => array(),
			),
			array(
				array(
					'startDateTime' => new DateTime(),
					'endDateTime' => new DateTime(),
					'capacity' => 'string',
				)
			),
			'Int expected, string (string) given.'
		);
		$this->checkException(
			array(
				'date' => $now->format(DateTime::W3C),
				'parameters' => array(),
			),
			array(
				array(
					'startDateTime' => 'not-a-datetime',
					'endDateTime' => new DateTime(),
					'capacity' => 1,
				)
			),
			'DateTime expected, string (not-a-datetime) given.'
		);
	}

	/**
	 * @param array $requestData
	 * @param array $responseData
	 * @param string $error
	 */
	private function checkException(array $requestData, array $responseData, $error)
	{
		try {
			$request = $this->getRequestMock($requestData);
			$method = new GetSlots(function () use ($responseData) {
				return $responseData;
			});
			$method->call($request);
			$this->fail('Expected exception to be thrown');

		} catch (MethodException $e) {
			$this->assertContains($error, $e->getMessage());
		}
	}

}
