<?php
namespace Alpha\Model\Domain\Entities;

use Alpha\Model\Domain\BaseEntity;
use Alpha\Model\Domain\Attributes\ManyToOne;

/**
 * Entidade TopicDescription - Traduções para o nome e descrição dos tópicos.
 * 
 * Melhoras aplicadas (Alpha Engine):
 * - Localização de Títulos: Permite que os nomes dos tópicos sejam traduzidos para diferentes idiomas.
 * - Injeção de Contexto: Propriedade topicId mapeada para que o DAO vincule automaticamente a tradução ao seu tópico pai.
 * - Tipagem Estrita: Propriedades string inicializadas para evitar erros de renderização.
 * - Relacionamento Bidirecional: #[ManyToOne] para o Topic pai e para o Language.
 */
class TopicDescription extends BaseEntity
{
    private int $topicId = 0;
    private int $languageId = 0;
    private string $name = '';
    private string $description = '';

    #[ManyToOne(targetEntity: Topic::class, foreignKey: 'topicId')]
    private ?Topic $topic = null;

    #[ManyToOne(targetEntity: Language::class, foreignKey: 'languageId')]
    private ?Language $language = null;

    public function getTopicId(): int
    {
        return $this->topicId;
    }

    public function setTopicId(int $topicId): self
    {
        $this->topicId = $topicId;
        return $this;
    }

    public function getLanguageId(): int
    {
        return $this->languageId;
    }

    public function setLanguageId(int $languageId): self
    {
        $this->languageId = $languageId;
        return $this;
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

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getTopic(): ?Topic { return $this->topic; }
    public function setTopic(?Topic $topic): self { $this->topic = $topic; return $this; }

    public function getLanguage(): ?Language { return $this->language; }
    public function setLanguage(?Language $language): self { $this->language = $language; return $this; }
}