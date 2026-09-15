# UC_ADM_007 - Gerenciar Usuários & Grupos de Permissões (RBAC)

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_ADM_007` |
| **Nome** | Gerenciar Usuários e Grupos de Permissões (RBAC) |
| **Módulo** | Painel Administrativo - Operações do Sistema |
| **Atores Primários** | Administrador Geral (*Admin*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Segurança e Acessos |
| **Frequência de Uso** | Baixa |
| **Rastreabilidade** | **RF:** [RF023](/docs/requirements/functional/functional_requirements.yaml) (Gestão administrativa)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Controle de acesso granular RBAC - Role-Based Access Control) |

---

## 1. 🎯 Descrição Sumária
Permite ao Administrador Geral gerenciar as contas de colaboradores internos (Vendedores de Balcão, Operadores de Caixa, Analistas de SAC/RMA, Gerentes de Depósito e Administradores) e definir perfis granulares de acesso baseados em papéis (RBAC - *Role-Based Access Control*), configurando permissões de **Leitura (Access)** e **Modificação (Modify)** rota a rota.

---

## 2. ⚡ Pré-Condições
- Administrador Geral autenticado.

---

## 3. ✅ Pós-Condições
- Contas administrativas criadas na tabela `tbkk_user` e matrizes de permissão salvas em `tbkk_user_group`.

---

## 4. 🚀 Gatilho (Trigger)
O Administrador acessa "Sistema > Usuários > Grupos de Usuários" ou "Usuários".

---

## 5. 🔄 Fluxo Principal (Criar Papel de Vendedor com Permissões Restritas)

1. **Ator:** Acessa "Grupos de Usuários" e clica em "Novo Grupo".
2. **Ator:** Define o nome do grupo (ex: *"Vendedores de Balcão - Loja Física"*).
3. **Sistema:** Exibe a árvore completa de rotas e controladores do sistema com caixas de seleção:
   - *Permissões de Acesso (Leitura);*
   - *Permissões de Modificação (Gravação/Edição).*
4. **Ator:** Marca permissão de leitura para `catalog/product` e permissão de modificação exclusiva para `pos/balcao`, desmarcando módulos financeiros e configurações do sistema.
5. **Ator:** Clica em "Salvar Grupo".
6. **Sistema:** Persiste a matriz serializada de permissões na tabela `tbkk_user_group`.
7. **Ator:** Cadastra um novo usuário colaborador vinculando-o ao novo grupo criado.
8. **Sistema:** O colaborador, ao fazer login no painel, visualiza apenas os menus e funcionalidades aos quais possui autorização expressa.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Desativação Rápida de Colaborador Desligado:**
  1. O administrador altera o status do colaborador para `Inativo (0)`.
  2. O sistema encerra imediatamente as sessões ativas do usuário e impede novos logins.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Tentativa de Excluir o Próprio Usuário ou o Grupo Super Admin:**
  1. O administrador tenta apagar a sua própria conta ativa ou o grupo de Administradores primário.
  2. O sistema bloqueia a ação por segurança: *"Você não pode excluir sua própria conta ou o grupo Super Admin."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RNF003 (RBAC):** Princípio do menor privilégio (*Least Privilege Principle*) aplicado a todos os operadores do sistema.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- `user_group_name`, `permission[access][]`, `permission[modify][]`, `username`, `password`, `email`.

### Saídas:
- Perfil de usuário configurado e acessos restritos aplicados.
