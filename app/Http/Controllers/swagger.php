<?php

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Connect API",
 *     description="مستندات API برای اپلیکیشن Connect",
 *     @OA\Contact(
 *         email="info@connect.com"
 *     )
 * )
 * 
 * @OA\Server(
 *     description="Local Environment",
 *     url="http://localhost:8000"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */ 