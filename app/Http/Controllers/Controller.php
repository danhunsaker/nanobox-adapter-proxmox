<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 *  @SWG\Swagger(
 *      basePath="/api/v1",
 *      @SWG\Info(
 *          title="Nanobox Adapter: Proxmox",
 *          description="An adapter for deploying Nanobox apps to Proxmox clusters.",
 *          @SWG\Contact(
 *              name="Dan Hunsaker",
 *              email="danhunsaker@gmail.com",
 *          ),
 *          @SWG\License(
 *              name="MIT",
 *              url="http://choosealicense.com/licenses/mit/"
 *          ),
 *          version="1.0.0",
 *      ),
 *      externalDocs={
 *          "description"="Official documentation here",
 *          "url"="https://docs.nanobox.io/providers/create/",
 *      },
 *      consumes={"application/json"},
 *      produces={"application/json"},
 *      tags={
 *          {
 *              "name"="meta",
 *              "description"="Endpoints related to the provider",
 *          },
 *          {
 *              "name"="keys",
 *              "description"="Endpoints related to SSH key management",
 *          },
 *          {
 *              "name"="servers",
 *              "description"="Endpoints related to server management",
 *          },
 *      },
 *  )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;
}
