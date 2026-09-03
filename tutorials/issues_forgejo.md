# Trabalhando com issues no Forgejo

## Criando um Issue via linha de comando

No terminal, navegue até a pasta do seu repositório:

```bash
cd /caminho/do/repositorio
```

Crie um issue:

```bash
git issue create
```

Preencha os campos conforme solicitado.

# Inserir as informações direto no banco de dados

Mexer direto nas tabelas do banco de dados do Forgejo exige alguns cuidados estruturais para não corromper o sistema.
Aqui está como a estrutura do banco funciona e o passo a passo seguro para fazer essa importação:
## 1. Onde fica o Banco de Dados?
Por padrão, se você fez uma instalação padrão e rápida do Forgejo, ele utiliza o SQLite.
O arquivo do banco geralmente fica na pasta de dados do Forgejo (procure por um arquivo chamado gitea.db ou forgejo.db dentro do diretório onde o Forgejo foi instalado ou em /var/lib/forgejo/).
Se você configurou o Forgejo com MySQL/MariaDB ou PostgreSQL, você pode acessá-lo usando o terminal ou ferramentas gráficas como o [DBeaver](https://dbeaver.io/).
## 2. A Estrutura das Tabelas de Issues
Para inserir uma issue manualmente via SQL, você não pode preencher apenas a tabela principal. O Forgejo conecta uma issue a várias tabelas. As principais que você precisa modificar são:


* issue: É a tabela principal. Armazena o título, o corpo do texto, o ID do repositório, o ID do usuário que criou e o status (aberta/fechada).
* issue_index: O Forgejo usa essa tabela para controlar a numeração sequencial das issues (#1, #2, #3) por repositório. É crucial atualizar essa tabela, ou as próximas issues criadas pela interface vão dar erro de chave duplicada.
* comment: Quando você cria uma issue, o texto descritivo dela é salvo na tabela comment com o tipo 0 (que significa "conteúdo inicial da issue").


## 3. Exemplo de Script SQL para Inserção
Se você for inserir via banco, o padrão de comandos SQL (adaptável para SQLite, MySQL ou Postgres) segue esta lógica para cada issue:

-- 1. Descubra o ID do seu repositório na tabela `repository` (ex: digamos que seja ID 1)-- 2. Descubra o ID do seu usuário na tabela `user` (ex: digamos que seja ID 1)
-- Insere a issue na tabela principalINSERT INTO issue (repo_id, index, poster_id, name, content, is_closed, is_pull, num_comments, created_unix, updated_unix)VALUES (1, 1, 1, 'Título da minha issue importada', 'Texto descritivo da issue', 0, 0, 0, STRFTIME('%s','now'), STRFTIME('%s','now'));
-- Pega o ID gerado automaticamente na tabela acima (ex: gerou o ID 50) e cria o comentário inicialINSERT INTO comment (type, poster_id, issue_id, commit_id, line, content, created_unix, updated_unix)VALUES (0, 1, 50, 0, 0, 'Texto descritivo da issue', STRFTIME('%s','now'), STRFTIME('%s','now'));
-- MUITO IMPORTANTE: Atualize o contador de índices do repositório para evitar travamentos na interface gráficaUPDATE issue_index SET max_index = 1 WHERE repo_id = 1;

------------------------------
## ⚠️ O Caminho Inverso: Importar do Forgejo para o GitHub
Depois que as issues estiverem salvas no seu Forgejo local, você mencionou o plano de importar para o GitHub.
É importante destacar que o GitHub não possui uma ferramenta nativa na interface web para puxar de volta as issues de um servidor Forgejo/Gitea. A ferramenta de importação do GitHub só funciona de forma automatizada se o caminho for o inverso (GitHub ➔ Outra plataforma) ou se você importar de plataformas específicas parceiras.
Para levar as issues do Forgejo para o GitHub de forma automatizada, você terá duas opções principais:

   1. Usar a CLI do GitHub (gh): Você pode gerar um script bash simples que lê do seu banco de dados local e roda o comando gh issue create --title "..." --body "..." para cada linha do banco. A [GitHub CLI](https://cli.github.com/) cria as issues diretamente no repositório remoto num piscar de olhos.
   2. API do GitHub: Criar um script Python invertido (parecido com o que vimos antes), que lê do seu banco de dados local via SQL e faz um POST para a API do GitHub (https://github.com).


