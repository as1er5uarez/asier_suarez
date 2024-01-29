<?php

namespace App\Security;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class ApiKeyUserProvider implements UserProviderInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function loadUserByUsername($apiKey)
    {
        $client = $this->entityManager->getRepository(Client::class)->findOneBy(['apiKey' => $apiKey]);

        if (!$client) {
            throw new UsernameNotFoundException(sprintf('API Key "%s" no encontrada.', $apiKey));
        }

        return $client;
    }

    public function refreshUser(UserInterface $user)
    {
        // Not needed for API key authentication
        return $user;
    }

    public function supportsClass($class)
    {
        return Client::class === $class;
    }
}
