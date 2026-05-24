# Registro de Modificações IA (Sessão 4)

---

### Alpha Engine: Auditoria do Schema e Implementação de Entidades Ausentes (Pedidos e CMS)
**Data:** [Data Atual]
**O que foi feito:**
- Criação das entidades `OrderOption`, `OrderStatus`, `OrderSubscription` e `OrderReturn` (sendo mapeada para `return`, evitando conflito de nome reservado) para completar as hierarquias de Pedidos e Pós-vendas.
- Criação das entidades `Module`, `Event` e `Startup` para completar a modelagem de configuração do sistema (Hooks, injeção de Middlewares e armazenamento JSON de módulos de extensões).
- Mapeamento de instâncias `#[ManyToOne]` nas sub-entidades de Order (`OrderOption` e `OrderSubscription`) e OrderReturn garantindo hidratação autônoma pelo DAO.
- Tipagem de dados e conversão flutuante para preços nas assinaturas, compatibilidade estrita do PHP 8.4.
**Benefícios:** Esta iteração fecha os "Buracos Negros" do banco de dados na nova arquitetura. O motor logístico da Alpha Engine agora enxerga a totalidade do ciclo de um pedido — desde as opções e assinaturas escolhidas até uma eventual devolução (RMA). Na infraestrutura, a disponibilidade de `Module` e `Event` viabiliza a refatoração completa do motor de extensão, abandonando arrays brutas a favor de objetos manipuláveis via Repositório.

---

### Alpha Engine: Repositórios e Mappers da Malha de Devolução (RMA)
**Data:** [Data Atual]
**O que foi feito:**
- Foram implementados os Mappers para abstração do DAO: `OrderReturnMapper`, `ReturnActionMapper`, `ReturnHistoryMapper`, `ReturnReasonMapper` e `ReturnStatusMapper`.
- Criação dos repositórios correspondentes com foco em buscar as devoluções por Cliente e por Pedido (`OrderReturnRepository`).
- Criação do `ReturnDictionaryRepository` estruturado como *Facade* (Fachada) para puxar facilmente listas de status, motivos e ações com base no idioma (`languageId`) do cliente ativo, eliminando as dezenas de Models isolados do OpenCart legado.
**Benefícios:** A gestão de logística reversa e SAC (Devoluções) agora estão totalmente independentes da arquitetura defasada e dos *queries* complexos. Os *Controllers* da interface não precisam mais mesclar bancos de dados de idiomas, bastando chamar os métodos concisos como `getReasonsByLanguage()`.

---

### Alpha Engine: Correção de Schema e Refatoração de Dicionário de RMA
**Data:** [Data Atual]
**O que foi feito:**
- Correção estrutural na modelagem das entidades `ReturnAction` e `ReturnReason`. Foi detectado que o OpenCart não utiliza o padrão `_description` nestas tabelas (`db_schema.php`), mantendo os atributos `language_id` e `name` enraizados na tabela principal.
- Refatoração do `ReturnDictionaryRepository` para consumir os Mappers primários (Flat Tables) em vez de relacionamentos *OneToMany*.
**Benefícios:** Prevenção de exceções severas no momento em que o DAO fosse montar as queries dinâmicas, garantindo precisão total na extração das traduções de devolução de forma limpa.

---

### Alpha Engine: Malha de Segurança e Painel Administrativo (Users)
**Data:** [Data Atual]
**O que foi feito:**
- Criação das Entidades `User`, `UserGroup` e `UserLogin` tipadas estritamente com PHP 8.4, fechando o escopo administrativo pendente em relação às tabelas nativas de permissão.
- Mapeamento de instâncias `#[ManyToOne]` garantindo que cada *User* possua um *UserGroup* extraído diretamente pelo ORM no processo de hidratação e que os logs de *UserLogin* se relacionem com o *User* correspondente.
- Criação de Mappers dedicados e Repositórios de Domínio contendo utilitários cruciais para segurança como `findByUsername()`, `findByEmail()` e `countRecentLogins()`.
**Benefícios:** Desacoplamento do sistema de autenticação e proteção Anti-Bruteforce. O Backoffice agora passa a usufruir da segurança de Domain Objects e não depende mais das Strings puras em Models legados. A gestão de permissões de módulos via `UserGroup` passa a ser entregue como um Array Limpo vindo do `$userGroup->getPermissionArray()`.