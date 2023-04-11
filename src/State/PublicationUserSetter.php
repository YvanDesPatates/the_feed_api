<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PublicationUserSetter implements ProcessorInterface
{
    public function __construct(private readonly ProcessorInterface $processor,
                                private readonly TokenStorageInterface $storage)
    {
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        $token = $this->storage->getToken();
        $data->setAuteur($token->getUser());

        $this->processor->process($data, $operation, $uriVariables, $context);
    }

}