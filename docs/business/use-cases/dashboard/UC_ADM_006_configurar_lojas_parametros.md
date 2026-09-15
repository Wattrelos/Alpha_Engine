# UC_ADM_006 - Configurar Lojas & Parâmetros do Motor

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_ADM_006` |
| **Nome** | Configurar Lojas & Parâmetros do Motor (Multi-Loja On-Premise) |
| **Módulo** | Painel Administrativo - Operações do Sistema |
| **Atores Primários** | Administrador Geral (*Admin*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Configuração do Sistema |
| **Frequência de Uso** | Baixa |
| **Rastreabilidade** | **RF:** [RF023](/docs/requirements/functional/functional_requirements.yaml) (Gestão administrativa global)<br>**RNF:** [RNF003](/docs/requirements/non_functional/non_functional_requirements.yaml) (Segurança e isolamento multi-tenant), [RNF004](/docs/requirements/non_functional/non_functional_requirements.yaml) (Alta disponibilidade) |

---

## 1. 🎯 Descrição Sumária
Permite exclusivamente ao Administrador Geral gerenciar as configurações vitais da plataforma Alpha Engine (`/setting/setting` e `/setting/store`), parametrizando lojas físicas e virtuais do ecossistema multi-loja (*multi-tenant* `store_id`), URLs canônicas, logotipo corporativo, dados fiscais da matriz, políticas de segurança, parâmetros de integração de gateways, servidor SMTP de e-mails transacionais e chaves de APIs.

---

## 2. ⚡ Pré-Condições
- Usuário autenticado com papel de Administrador Geral (*Super Admin*).

---

## 3. ✅ Pós-Condições
- Parâmetros gravados na tabela `tbkk_setting` vinculados ao `store_id`.
- Recarregamento a quente (*Hot Reload*) das configurações do motor sem necessidade de reinício do servidor.

---

## 4. 🚀 Gatilho (Trigger)
O Administrador acessa "Sistema > Configurações > Lojas" no painel.

---

## 5. 🔄 Fluxo Principal (Caminho Feliz)

1. **Ator:** Acessa o formulário de configurações da loja ativa (`store_id = 0` para matriz).
2. **Sistema:** Renderiza o formulário com abas técnicas:
   - **Geral:** Nome da Loja, Razão Social, CNPJ, Endereço Físico e Telefone;
   - **Loja:** Título da Vitrine, Meta Descrição SEO e Layout Padrão;
   - **Local:** País, Estado (UF), Fuso Horário, Moeda Padrão (BRL) e Idioma;
   - **Opções:** Exigência de aprovação para contas corporativas PJ, termos de consentimento LGPD, controle de stockout;
   - **Imagens:** Logotipos da loja e do cabeçalho, Favicon e marca d'água de proteção;
   - **Servidor:** Configurações de SMTP seguro, limites de rate limiting, chaves de API e modo de manutenção.
3. **Ator:** Altera os parâmetros desejados e clica em "Salvar Configurações".
4. **Sistema:** Valida a integridade dos parâmetros, grava na tabela `tbkk_setting` e limpa as chaves de configuração no Redis.
5. **Sistema:** Exibe notificação de sucesso e aplica as novas regras imediatamente.

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Ativação do Modo de Manutenção:**
  1. O administrador ativa a chave "Modo de Manutenção" para uma intervenção técnica.
  2. A vitrine pública passa a exibir a página estilizada de manutenção para visitantes comuns, permitindo acesso apenas para administradores logados.

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Tentativa de Acesso por Operador Comum:**
  1. Um usuário sem privilégios de Admin tenta acessar a rota de configurações.
  2. O sistema bloqueia a requisição com HTTP 403 (*Access Denied*) e registra a tentativa no log de auditoria de segurança.

---

## 8. 📜 Regras de Negócio Aplicadas

- **RNF003 & RNF004:** Isolamento rigoroso das definições de cada tenant em ambiente On-Premise.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Chaves e valores de configuração de sistema e infraestrutura.

### Saídas:
- Painel de configuração validado e cache do sistema renovado.
