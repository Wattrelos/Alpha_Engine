---
### Alpha Engine: Criação de Entidades para Autorização e Tokens de Usuário e Cliente
---
**Data:** [Data Atual]
**O que foi feito:**
- Foram criadas as classes de Domínio para representar os mecanismos de persistência e segurança de sessão: `CustomerAuthorize`, `CustomerToken`, `UserAuthorize` e `UserToken`.
- Mapeamento dos relacionamentos bidirecionais (atributo `#[ManyToOne]`) garantindo que cada token ou autorização mantenha o contexto da entidade pai associada (`Customer` ou `User`).
- Inicialização de propriedades primitivas com valores estritos (`int = 0`, `string = ''`, `bool = false`) prevenindo `Fatal Error: Uninitialized Property` na hidratação pela Engine.
**Benefícios:** Consistência com o Data Access Object (DAO) e a camada de segurança. Agora os métodos de recuperação de senha e autorização persistente (manter conectado) poderão ser manuseados pelo Doctrine/UnitOfWork e Repository Patterns garantindo a integridade dos dados de IPs, User Agents e Datas de Expiração sem quebrar nas buscas de ORM.
