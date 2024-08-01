<?php

namespace Adam314\CommissionTask\Interface;

use Adam314\CommissionTask\Type\ClientType;

interface ClientInterface
{
    public function getType(): ClientType;

    public function getId(): int;
}
