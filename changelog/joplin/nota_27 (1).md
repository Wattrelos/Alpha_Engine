---
### Alpha Engine: Malha de Segurança e Painel Administrativo (Users)
---
**Data:** [Data Atual]
**O que foi feito:**
- Criação das Entidades `User`, `UserGroup` e `UserLogin` tipadas estritamente com PHP 8.4, fechando o escopo administrativo pendente em relação às tabelas nativas de permissão.
- Mapeamento de instâncias `#[ManyToOne]` garantindo que cada *User* possua um *UserGroup* extraído diretamente pelo ORM no processo de hidratação e que os logs de *UserLogin* se relacionem com o *User* correspondente.
- Criação de Mappers dedicados e Repositórios de Domínio contendo utilitários cruciais para segurança como `findByUsername()`, `findByEmail()` e `countRecentLogins()`.
**Benefícios:** Desacoplamento do sistema de autenticação e proteção Anti-Bruteforce. O Backoffice agora passa a usufruir da segurança de Domain Objects e não depende mais das Strings puras em Models legados. A gestão de permissões de módulos via `UserGroup` passa a ser entregue como um Array Limpo vindo do `$userGroup->getPermissionArray()`.# Registro de Modificações IA (Sessão 5)
