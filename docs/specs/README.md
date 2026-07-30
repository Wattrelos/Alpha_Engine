# 📑 Spec-Driven Development (SDD) na Alpha Engine

Este diretório armazena os artefatos de **Especificação Orientada a Contratos (Spec-Driven Development - SDD)** do projeto **Alpha Engine**.

## 🎯 O que é Spec-Driven Development?

No contexto da **Alpha Engine** e da colaboração com **Agentes de Inteligência Artificial**, o *Spec-Driven Development* é a prática de definir contratos formais, determinísticos e legíveis por máquina **antes** ou em **paralelo** à implementação do código.

Enquanto a documentação tradicional em Markdown (como ADRs e guias de arquitetura) fornece contexto de governança e visão geral, as especificações baseadas em esquemas (OpenAPI, JSON Schema e Gherkin) eliminam a ambiguidade em contratos de entrada e saída.

---

## 📂 Estrutura de Artefatos

```
docs/specs/
├── README.md                          # Este guia didático
├── openapi.yaml                       # Contrato OpenAPI 3.1 da API REST/Slim
├── schemas/
│   └── OrderDataDTO.json              # JSON Schema (Draft 2020-12) para OrderDataDTO
└── features/
    └── checkout_idempotency.feature   # Especificação BDD Gherkin de comportamentos
```

---

## 🔬 Benefícios do Padrão SDD para Engenharia & IA

| Camada de Especificação | Tecnologia | Papel para Desenvolvedores | Papel para Agentes de IA |
| :--- | :--- | :--- | :--- |
| **Contrato de API** | OpenAPI 3.1 (`openapi.yaml`) | Documentação interativa (Swagger/Redoc) e validação de rotas | Permite à IA gerar controladores, clientes HTTP e testes de contrato sem supor nomes de campos |
| **Esquema de DTO** | JSON Schema (`OrderDataDTO.json`) | Validação estrita de payloads JSON recebidos | Impede que a IA cometa erros de tipagem ou crie chaves inconsistentes (`customer_id` vs `customerId`) |
| **Aceitação & BDD** | Gherkin (`checkout_idempotency.feature`) | Critérios de aceitação executáveis (Behat/PHPUnit) | Define cenários GIVEN-WHEN-THEN claros para a IA construir suítes de testes de regressão |

---

## 🚀 Como Consumir Estes Artefatos

1. **Validação de OpenAPI**: Pode ser visualizado no Swagger UI ou validado via CLI:
   ```bash
   npx @redocly/cli lint docs/specs/openapi.yaml
   ```
2. **Validação de JSON Schema**: Utilizado por mappers e validações PSR-7 na Alpha Engine:
   ```php
   $validator = new JsonSchema\Validator();
   $validator->validate($data, (object)['$ref' => 'file://' . __DIR__ . '/docs/specs/schemas/OrderDataDTO.json']);
   ```
3. **Execução BDD**: Cenários expressos no formato Cucumber/Behat.
