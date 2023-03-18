<?php

namespace GitHub\Models;

class Comment
{
    public string $authorAssociation;
    public string $body;
    public Reactions $reactions;

    public function __construct(array $parameters)
    {
        $this->authorAssociation = $parameters['author_association'] ?? '';
        $this->body = $parameters['body'] ?? '';
        $this->reactions = new Reactions($parameters['reactions'] ?? []);
    }
}
