<?php


namespace Thales\PhpBanking\Service;

use Thales\PhpBanking\Model\Client\ClientRepositoryInterface;
use PDOException;
use Exception;


class ClientService
{
    private $clientRepository;

    public function __construct(ClientRepositoryInterface $clientRepository)
    {
        $this->clientRepository = $clientRepository;
    }

    public function createClient($username, $password, $name, $cpfcnpj, $email)
    {
        if (empty($username) || empty($name) || empty($cpfcnpj) || empty($email) || empty($password)) {
            throw new Exception("Todos os campos são obrigatórios.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email inválido.");
        }

        if (!preg_match('/^[0-9]{11,14}$/', $cpfcnpj)) {
            throw new Exception("CPF/CNPJ inválido. Deve conter apenas números (11 ou 14 dígitos).");
        }

        try {
            return $this->clientRepository->createClient($username, $password, $name, $cpfcnpj, $email);
        } catch (PDOException $e) {
            if ($e->getCode() === '23505') { // erro de violação de UNIQUE no PostgreSQL
                throw new Exception("Já existe um usuário com dados únicos conflitantes.");
            }

            throw $e;
        }
    }

    public function getClient($id)
    {
        $client = $this->clientRepository->getClient($id);
        if (!$client) {
            throw new Exception("Usuário não encontrado.");
        }
        return $client;
    }

    public function getAllClients()
    {
        return $this->clientRepository->getAllClients();
    }

    public function updateClient($id, $username, $password, $name, $email)
    {
        $client = $this->clientRepository->getClient($id);
        if (!$client) {
            throw new Exception("Usuário não encontrado para atualização.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email inválido.");
        }

        return $this->clientRepository->updateClient($id, $username, $password, $name, $email);
    }
}
