<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      title="Nome da Sua API",
 *      version="1.0.0",
 *      description="Descrição da sua API",
 *      @OA\Contact(
 *          email="contato@example.com"
 *      ),
 *      @OA\License(
 *          name="Licença",
 *          url="http://www.exemplo.com/licenca"
 *      )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
