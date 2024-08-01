<?php

namespace Adam314\CommissionTask\Repository;

use Adam314\CommissionTask\Interface\ClientInterface;

class Client
{
    public function findOneById(int $id): ClientInterface|false
    {
        return false;
    }

    public function store(ClientInterface $user)
    {
    }
}
