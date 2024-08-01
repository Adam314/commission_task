<?php

namespace Adam314\CommissionTask\Entity;

use Adam314\CommissionTask\Interface\ClientInterface;
use Adam314\CommissionTask\Type\ClientType;

class Client implements ClientInterface
{
    public function __construct(private int $id, private ClientType $type)
    {
    }

    public function getType(): ClientType
    {
        return $this->type;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
