<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Info(
 *     title="My API",
 *     version="1.0.0",
 *     description="مستندات API برای ثبت‌نام و احراز هویت کاربران"
 * )
 */

abstract class Controller
{
    use AuthorizesRequests;
}
