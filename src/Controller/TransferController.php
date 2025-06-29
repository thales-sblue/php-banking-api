<?php

namespace Thales\PhpBanking\Controller;

use Thales\PhpBanking\Service\TransferService;
use Thales\PhpBanking\resources\Response;
use Exception;

class TransferController
{
    private TransferService $transferService;

    public function __construct(TransferService $transferService)
    {
        $this->transferService = $transferService;
    }

    public function handleRequest(string $method, array $uri): void
    {
        $id = isset($uri[2]) ? (int)$uri[2] : null;

        try {
            switch ($method) {
                case 'GET':
                    header('Content-Type: text/html; charset=utf-8');
                    require __DIR__ . "/../View/Transfer/create.phtml";

                    break;

                case 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);

                    if (!isset($data['fromAccountId'], $data['toAccountId'], $data['amount'])) {
                        Response::sendError('Campos obrigatórios não informados!', 400);
                    }

                    $transfer = $this->transferService->processTransfer(
                        $data['fromAccountId'],
                        $data['toAccountId'],
                        $data['amount']
                    );

                    Response::sendJson([
                        'message' => 'Transferência realizada com sucesso!',
                        'transfer' => $transfer
                    ], 201);
                    break;

                default:
                    Response::sendError('Método não permitido!', 405);
                    break;
            }
        } catch (Exception $e) {
            Response::sendError('Erro ao processar a transferência!', 500, $e->getMessage());
        }
    }
}
