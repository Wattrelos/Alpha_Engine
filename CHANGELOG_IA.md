
---

### Alpha Engine: Refatoração do Fluxo de Informação (CMS)
**Data:** [Data Atual]
**O que foi feito:**
- Migração do controller `information.php` para utilizar a `BaseController`, transformando-o num verdadeiro *Skinny Controller*.
- Implementação do método `getInformationDisplayData` no `InformationRepository`, transferindo toda a construção do DTO, formatação de HTML (`html_entity_decode`) e resolução de Breadcrumbs para a camada de Domínio.
- `InformationMapper` atualizado para estender `BaseMapper`, herdando a injeção nativa de banco de dados, mas mantendo a consulta SQL altamente otimizada que resolve Multi-store, Status e Language diretamente no banco.
**Benefícios:** Código drasticamente reduzido no controller, eliminação de laços `foreach` de hidratação manual, menor acoplamento e suporte automático ao renderizador de layouts unificado da Alpha Engine.

---

### Alpha Engine: Refatoração do Fluxo de Carrinho (Cart)
**Data:** [Data Atual]
**O que foi feito:**
- Migração de lógica de exibição de `checkout/cart.php` para utilizar a `BaseController`, transformando-o num verdadeiro *Skinny Controller*.
- Implementação do método `getCartDisplayData` no `CartRepository`, extraindo loops, checagens de peso, verificações de sessão e hidratação de produtos da visão.
**Benefícios:** Enorme redução do tamanho do controller; reuso imediato das lógicas de validação de produtos do carrinho (evita duplicação entre minicart, api e página de cart principal); simplificação da leitura utilizando o pattern DTO (`ViewResponse`).