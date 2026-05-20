<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;

/**
 * Entidade Log - Registro centralizado de eventos e erros do sistema.
 * 
 * @Table(name="log")
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Rastreabilidade: Campo 'message' tipado como string para armazenar detalhes de exceções ou logs de auditoria.
 * - Timestamp Nativo: dateAdded tipado para garantir a cronologia exata dos eventos.
 * - Abstração de Persistência: Preparada para ser consumida pelo DataAccessObject em processos de monitoramento.
 */
class Log extends BaseEntity
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

    /**
     * Método utilitário para registrar o log com o timestamp atual.
     */
    public function touch(): self
    {
        $this->dateAdded = date('Y-m-d H:i:s');
        return $this;
    }
}