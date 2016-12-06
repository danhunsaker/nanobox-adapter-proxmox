<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @SWG\Swagger(
 *   basePath="/api/v1",
 *   @SWG\Info(
 *     title="Nanobox Provider: Proxmox",
 *     description="A provider for deploying Nanobox apps to Proxmox clusters.",
 *     @SWG\Contact(
 *       name="Dan Hunsaker",
 *       email="danhunsaker@gmail.com",
 *     ),
 *     @SWG\License(
 *       name="MIT",
 *     ),
 *     version="1.0.0",
 *   ),
 *   consumes={"application/json"},
 *   produces={"application/json"},
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;
}
