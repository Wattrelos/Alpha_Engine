<?php

namespace Alpha\Model\DataTransferObject;

/**
 * ViewResponse - Objeto de transferência de dados para padronizar o abastecimento das Views.
 * 
 * Melhoras Alpha Engine:
 * - Interface Fluida: Permite encadeamento de métodos para construção rápida de respostas.
 * - Normalização Twig: Garante que chaves globais (breadcrumbs, success, warning) sejam consistentes.
 * - Encapsulamento: Isola a lógica de metadados da página dos dados de domínio.
 * Centraliza metadados como breadcrumbs, mensagens de sucesso e dados específicos da entidade.
 */
class ViewResponse extends BaseDTO 
{
    /**
     * Adiciona um rastro de navegação único.
     */
    public function addBreadcrumb(string $text, string $href): self 
    {
        $breadcrumbs = $this->get('breadcrumbs', []);
        $breadcrumbs[] = [
            'text' => $text,
            'href' => $href
        ];

        return $this->set('breadcrumbs', $breadcrumbs);
    }

    /**
     * Mescla uma coleção de breadcrumbs ao rastro atual.
     */
    public function addBreadcrumbs(array $breadcrumbs): self 
    {
        $existing = $this->get('breadcrumbs', []);
        return $this->set('breadcrumbs', array_merge($existing, $breadcrumbs));
    }

    /**
     * Define o título principal que será exibido no template (<h1>).
     */
    public function setHeadingTitle(string $title): self
    {
        return $this->set('heading_title', $title);
    }

    /**
     * Define a mensagem de sucesso da visualização.
     */
    public function setSuccess(string $message): self 
    {
        return $this->set('success', $message);
    }

    /**
     * Define um alerta de erro/aviso para a visualização.
     */
    public function setWarning(string $message): self
    {
        return $this->set('error_warning', $message);
    }

    /**
     * Retorna o array de dados internos do DTO.
     */
    public function getData(): array
    {
        return $this->toArray();
    }
}
