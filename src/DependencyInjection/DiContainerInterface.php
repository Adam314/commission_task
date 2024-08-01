<?php

namespace Adam314\CommissionTask\DependencyInjection;

interface DiContainerInterface
{
    public function getService(string $serviceName);
}
