<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;
class PutRequestDTO
{
    #[Assert\NotBlank(message: "Login is required.")]
    #[Assert\Length(
        max: 8,
        maxMessage: "The login cannot be longer than {{ limit }} characters."
    )]
    public string $login;

    #[Assert\NotBlank(message: "Password is required.")]
    #[Assert\Length(
        max: 8,
        maxMessage: "The pass cannot be longer than {{ limit }} characters."
    )]
    public string $pass;

    #[Assert\NotBlank(message: "Phone is required.")]
    #[Assert\Length(
        max: 8,
        maxMessage: "The phone cannot be longer than {{ limit }} characters."
    )]
    public string $phone;
}