<?php
namespace app\modules\exchange\helpers;

/**
 * Class Messages
 * @package app\modules\exchange\helpers
 */
class Messages
{
    const REQUIRED_EMPTY = 'required parameter is not set';
    const INVALID_PARAM = 'parameter value is invalid';
    const USER_NOT_FOUND = 'specified user does not exist';
    const AMOUNT_RANNGE_OUT = 'amount is out of the range';
    const TOO_MANY_IDS = 'reception identifiers are too many';

    const ERR_INVALID_PARAM = 'Invalid Parameters';
    const ERR_FAILED = 'Failed';
    const ERR_UNAUTHORIZED = 'Unauthorized';
    const ERR_NOT_FOUND = 'Not Found';
    const ERR_SERVER_ERROR = 'Server Error';
    const ERR_MAINTENANCE = 'In Maintenance';


    const HTTP_BADREQUEST   = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_NOT_FOUND    = 404;
    const HTTP_SERVER_ERROR = 500;
    const HTTP_MAINTENANCE  = 503;
}