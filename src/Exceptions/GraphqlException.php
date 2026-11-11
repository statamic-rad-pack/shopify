<?php

namespace StatamicRadPack\Shopify\Exceptions;

use RuntimeException;

class GraphqlException extends RuntimeException
{
    public function __construct(string $message, public array $errors = [])
    {
        parent::__construct('Shopify GraphQL error: '.$message);
    }
}
