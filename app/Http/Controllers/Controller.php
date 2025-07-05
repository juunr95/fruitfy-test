<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Contacts Management API",
 *     version="1.0.0",
 *     description="API documentation for the Contacts Management System",
 *     @OA\Contact(
 *         email="admin@contacts.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Local development server"
 * )
 * 
 * @OA\Tag(
 *     name="Contacts",
 *     description="API endpoints for managing contacts"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
