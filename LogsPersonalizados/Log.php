<?php
namespace LogsPersonalizados;

/**
 * Classe Log Personalizada - Registro utilitário fora da malha de domínio.
 */
class Log
{
    private string $message = '';
    private string $dateAdded = '0000-00-00 00:00:00';

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getDateAdded(): string
    {
        return $this->dateAdded;
    }

    public function setDateAdded(string $dateAdded): self
    {
        $this->dateAdded = $dateAdded;
        return $this;
    }

    public function touch(): self
    {
        $this->dateAdded = date('Y-m-d H:i:s');
        return $this;
    }
}