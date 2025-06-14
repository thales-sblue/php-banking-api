<?php

namespace Thales\PhpBanking\Controller;

use Thales\PhpBanking\Service\ClientService;
use Thales\PhpBanking\Utils\Response;
use Exception;

class ClientController
{
    private ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function handleRequest($method, $uri): void
    {
        $id = isset($uri[2]) ? (int)$uri[2] : null;

        try {
            switch ($method) {
                case 'GET':
                    if ($id) {
                        $client = $this->clientService->getClient($id);
                        Response::sendJson($client);
                    } else {
                        $clients = $this->clientService->getAllClients();
                        Response::sendJson($clients);
                    }
                    break;

                case 'POST':
                    $data = json_decode(file_get_contents('php://input'), true);

                    if (!isset($data['username'], $data['password'], $data['name'], $data['cpfcnpj'], $data['email'])) {
                        Response::sendError('Dados obrigatórios ausentes (username, password, name, cpfcnpj, email)', 400);
                    }

                    $client = $this->clientService->createClient(
                        $data['username'],
                        $data['password'],
                        $data['name'],
                        $data['cpfcnpj'],
                        $data['email']
                    );

                    if (!$client) {
                        Response::sendError('Erro ao criar cliente.', 500);
                    }

                    Response::sendJson([
                        'message' => 'Cliente criado com sucesso',
                        'client' => $client
                    ], 201);
                    break;

                case 'PUT':
                    if (!$id) {
                        Response::sendError('ID do cliente é obrigatório para atualização', 400);
                    }

                    $data = json_decode(file_get_contents('php://input'), true);

                    if (!isset($data['username'], $data['password'], $data['name'], $data['email'])) {
                        Response::sendError('Dados obrigatórios ausentes para atualização (username, password, name, email)', 400);
                    }

                    $updated = $this->clientService->updateClient(
                        $id,
                        $data['username'],
                        $data['password'],
                        $data['name'],
                        $data['email']
                    );

                    if (!$updated) {
                        Response::sendError("Erro ao atualizar cliente com ID $id", 500);
                    }

                    Response::sendJson([
                        'message' => 'Cliente atualizado com sucesso',
                        'client' => $updated
                    ]);
                    break;

                default:
                    Response::sendError('Método não permitido', 405);
                    break;
            }
        } catch (Exception $e) {
            Response::sendError('Erro inesperado no servidor', 500, $e->getMessage());
        }
    }
}
