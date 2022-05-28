<?php

namespace App\Traits;

/**
 * Global structured response.
 */
trait Responder
{
    private static $status = [
        // Success
        200 => [
            'code'    => 200,
            'message' => 'OK',
        ],
        201 => [
            'code'    => 201,
            'message' => 'Created',
        ],
        202 => [
            'code'    => 202,
            'message' => 'Accepted'
        ],

        // Client errors
        400 => [
            'code'    => 400,
            'message' => 'Bad Request'
        ],
        401 => [
            'code'    => 401,
            'message' => 'Unauthorized'
        ],
        403 => [
            'code'    => 403,
            'message' => 'Forbidden'
        ],
        404 => [
            'code'    => 404,
            'message' => 'Not Found'
        ],
        405 => [
            'code'    => 405,
            'message' => 'Method Not Allowed'
        ],
        408 => [
            'code'    => 408,
            'message' => 'Request Timeout'
        ],
        419 => [
            'code'    => 419,
            'message' => 'Token Missmatch'
        ],

        // Server errors
        500 => [
            'code'    => 500,
            'message' => 'Internal Server Error'
        ],
        503 => [
            'code'    => 503,
            'message' => 'Service Unavailable'
        ],
    ];

    /**
     * @param int   $code | number for the error code
     * @param array $data | data to be included in return, nullable (return empty object)
     *
     * @return json | standard structured response
     */
    public static function response(int $code, array $data = [])
    {
        $status = self::checkStatus($code);

        $response = [
            'status' => [
                'code'    => $status['code'],
                'message' => $status['message'],
            ],
            'data' => (object) $data,
        ];

        if (empty($data)) unset($response['data']);

        return response($response, $status['code']);
    }

    private static function checkStatus(int $code)
    {
        if (!in_array($code, array_keys(self::$status))) {
            $status_code = 500;
            $status_message = 'Invalid status code';
        } else {
            $status_code = self::$status[$code]['code'];
            $status_message = self::$status[$code]['message'];
        }

        return [
            'code' => $status_code,
            'message' => $status_message
        ];
    }
}
