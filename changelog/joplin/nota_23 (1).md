---
### Alpha Engine: Repositórios e Mappers de Segurança (Auth/Tokens)
---
**Data:** [Data Atual]
**O que foi feito:**
- Criação dos Mappers: `CustomerAuthorizeMapper`, `CustomerTokenMapper`, `UserAuthorizeMapper` e `UserTokenMapper` abstraindo diretamente as tabelas do schema nativo para as novas classes de entidade da Alpha Engine.
- Criação dos Repositórios correspondentes herdando `AbstractRepository`.
- Implementação de métodos utilitários de Domínio voltados à segurança: `findByToken($token)`, `findByCode($code)` para validações, e `clearTokensForCustomer()` / `clearTokensForUser()` para reset atômico após troca de senha bem-sucedida.
**Benefícios:** Desacoplamento absoluto da camada de autenticação. Agora, *Controllers* relacionados a Login, Registro ou Redefinição de Senha não interagem com query builders do OpenCart. Basta injetar as intenções através dos métodos concisos de persistência e validação da Alpha Engine, fechando brechas de retenção de tokens zumbis através do `clearTokens`.# Registro de Modificações IA (Sessão 4)
