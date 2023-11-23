<?php

namespace App;

class Responses
{
	public static function success($msg = null)
	{
		$apiResp = [
			'code' => 200
		];

		if ($msg) $apiResp['message'] = $msg;

		return $apiResp;
	}

	public static function created($msg = null)
	{
		$apiResp = [
			'code' => 201
		];

		if ($msg) $apiResp['message'] = $msg;

		return $apiResp;
	}

	public static function no_content_to_send($msg = null)
	{
		$apiResp = [
			'code' => 204
		];

		if ($msg) $apiResp['message'] = $msg;

		return $apiResp;
	}

	public static function bad_request($msg = null)
	{
		$apiResp = [
			'code' => 400
		];

		if ($msg) $apiResp['message'] = $msg;

		return $apiResp;
	}

	public static function unauthorized($msg = null)
	{
		$apiResp = [
			'code' => 401
		];

		if ($msg) $apiResp['message'] = $msg;

		return $apiResp;
	}

	public static function not_found($msg = null)
	{
		$apiResp = [
			'code' => 404
		];

		if ($msg) $apiResp['message'] = $msg;

		return $apiResp;
	}

	public static function unprocessable_entity($msg = null)
	{
		$apiResp = [
			'code' => 422
		];

		if ($msg) $apiResp['message'] = $msg;

		return $apiResp;
	}

	public static function error($msg = null)
	{
		$apiResp = [
			'code' => 500
		];
		if (env('APP_DEBUG')) $apiResp['message'] = $msg;

		return $apiResp;
	}
}
