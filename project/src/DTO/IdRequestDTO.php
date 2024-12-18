<?php

namespace App\DTO;


use Symfony\Component\Validator\Constraints as Assert;
class IdRequestDTO
{
    #[Assert\NotBlank(message: "ID is required.")]
    #[Assert\Type(type: "numeric", message: "ID must be a number.")]
    #[Assert\Length(
        max: 8,
        maxMessage: "The id cannot be longer than {{ limit }} characters."
    )]
    public string $id;
}