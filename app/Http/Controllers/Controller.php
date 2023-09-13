<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      title="API de Sincronização Enjoy",
 *      version="1.0.0",
 *      description="Esta API tem como objetivo fornecer um serviço para a disponibilização de informações, permitindo que nossos integradores estejam sincronizados com a base de dados da Enjoy. A API segue o padrão REST, possibilitando a manipulação de clientes, produtos e pedidos na loja virtual. Atualmente, estão disponíveis as APIs: Clientes, Pedidos e Produtos.",
 *      @OA\Contact(
 *          email="dev@hoogli.com.br"
 *      ),
 *      @OA\License(
 *          name="Licença Apache 2.0",
 *          url="https://www.apache.org/licenses/LICENSE-2.0",
 *      )
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
