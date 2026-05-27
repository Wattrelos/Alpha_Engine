---
### Alpha Engine: Auditoria de Domínio e Implementação de Catálogos e Endereços
---
**Data:** [Data Atual]
**O que foi feito:**
- Criação das Entidades Vitais `Address`, `Attribute` e `AttributeGroup`.
- Mapeamento estruturado das chaves estrangeiras com injeção de classes ricas (`Country`, `Zone`, `Customer`, `AttributeGroup`) evitando SQLs JOIN manuais. As conversões de PascalCase para SnakeCase nativas da Engine lidam fluidamente com nomes de colunas numéricas (como `address1` para `address_1`).
- Criação do `AddressRepository` com regras de negócios cruciais implementadas como: `getDefaultAddress(int $customerId)` permitindo autocompletamento fácil no processo de Checkout.
**Benefícios:** Desacoplamento estrutural em painéis complexos. Ao separar Mappers e Repositórios para a malha de Livro de Endereços (Address Book), a tela de Checkout deixará de invocar Modelos que misturam interface com banco. O catálogo também ganha previsibilidade com `AttributeRepository` organizando suas listagens por `sortOrder` em consultas limpas.
