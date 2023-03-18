<?php

namespace GitHub\Models;

class Branch // TODO is branch really the right class name?
{
    public string $ref;

    public function __construct(array $parameters)
    {
        $this->ref = $parameters['ref'] ?? '';
    }
}
