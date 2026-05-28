<?php

namespace Alpha\Model\DataTransferObject;

use Alpha\Model\DataTransferObject\Attributes\Validation;

/**
 * ContactFormDTO - Objeto de transferência de dados para formulário de contato.
 * Estende BaseDTO para herdar funcionalidades básicas de manipulação de dados.
 */
class ContactFormDTO extends BaseDTO
{
    #[Validation(required: true, minLength: 3, maxLength: 32)]
    protected string $name = '';

    #[Validation(required: true, email: true, callback: 'validateUniqueEmail')]
    protected string $email = '';

    #[Validation(required: true, minLength: 5, maxLength: 100)]
    protected string $subject = '';

    #[Validation(required: true, minLength: 10)]
    protected string $message = '';

    protected bool $newsletter = false;

    /**
     * Callback para validar se o e-mail já existe no banco.
     * O $context é injetado pelo EntityHydrator.
     */
    public function validateUniqueEmail(mixed $value, array $context): bool|string
    {
        /** @var \Alpha\Mappers\Account\CustomerMapper $customerMapper */
        $customerMapper = $context['customer_mapper'] ?? null;

        if ($customerMapper) {
            $exists = $customerMapper->findByEmail((string)$value);
            if ($exists) {
                return 'error_email_exists';
            }
        }
        return true;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function isNewsletter(): bool
    {
        return $this->newsletter;
    }

    public function setNewsletter(bool $newsletter): self
    {
        $this->newsletter = $newsletter;
        return $this;
    }
}