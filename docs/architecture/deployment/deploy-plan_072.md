# DP-72: Fazer a tela para o administrador da loja com a aba inserir informações

- **Tipo:** Deployment plan
- **Status:** Closed
- **Autor:** [Wattrelos](https://github.com/Wattrelos)
- **Criado em:** 2026-08-10 22:56:11
- **Labels:** Nenhuma
- **Responsáveis:** Wattrelos
- **URL no GitHub:** https://github.com/Wattrelos/Alpha_Engine/issues/72

## Descrição

# Plano de Implementação: Aba "Informações" e Gestão de Páginas Institucionais

Este plano contempla a adição da aba **"Informações"** na página de Configurações da Loja (`/configuracoes`) no painel de controle (Dashboard Admin), com suporte a **edição das páginas institucionais padrão**, **pré-visualização em tempo real (Live Preview Markdown)** e **criação de novas páginas institucionais personalizadas**.

---

## 🎨 Funcionalidades Aprovadas

1. **Edição das Páginas Padrão da Loja:**
   - **Sobre Nós** (ID 1)
   - **Termos e Condições** (ID 2)
   - **Política de Privacidade** (ID 3)
   - **Informações de Entrega** (ID 4)
2. **Editor Markdown com Pré-Visualização em Tempo Real (Live Preview):**
   - Modalidade de visualização dividida ou alternada (Editar / Pré-visualizar HTML gerado via Markdown).
3. **Criação e Exclusão de Novas Páginas Institucionais:**
   - Botão para adicionar novas páginas institucionais personalizadas (ex: "Política de Troca e Devolução", "Trabalhe Conosco").
   - Geração automática ou personalização do Slug SEO (`seo_url`).
   - Opção de remover páginas personalizadas.

---

## 📐 Alterações Propostas

### 1. Camada de Persistência e Repositórios

#### [MODIFY] [InformationMapper.php](/core/Mappers/EntityMappers/InformationMapper.php)
- Implementar método `createInformation(array $data, int $languageId, int $storeId): int` para criar nova página em `agsc_information`, `agsc_information_description` e `agsc_information_to_store`.
- Implementar método `updateInformation(int $informationId, int $languageId, int $storeId, array $data): bool`.
- Implementar método `deleteInformation(int $informationId): bool` para remoção segura de páginas institucionais personalizadas.
- Adicionar o binding de `seo_url` para gerar automaticamente o slug amigável da página (ex: `/pt-br/pagina/politica-de-troca`).

#### [MODIFY] [InformationRepository.php](/core/Model/Domain/Repositories/InformationRepository.php)
- Adicionar métodos `getAllInformationsAdmin(int $languageId, int $storeId): array` para listar todas as páginas ativas e inativas no painel.
- Adicionar método `saveInformationPage(int $informationId, array $data): int`.
- Adicionar método `deleteInformationPage(int $informationId): bool`.

---

### 2. Controladores do Painel Administrativo

#### [MODIFY] [EditStoreSettingAction.php](/core/Admin/Controllers/Actions/Setting/StoreSetting/EditStoreSettingAction.php)
- Carregar todas as páginas institucionais via `InformationRepository`.
- Recuperar os slugs SEO associados a cada página via `SeoUrlRepository`.
- Injetar no Twig a lista de páginas e os dados de tradução/metadados.

#### [MODIFY] [UpdateStoreSettingAction.php](/core/Admin/Controllers/Actions/Setting/StoreSetting/UpdateStoreSettingAction.php)
- Processar atualizações de páginas institucionais existentes.
- Processar submissão de novas páginas institucionais (`new_information` se preenchido).
- Validar títulos e conteúdos.

#### [NEW] [DeleteInformationAction.php](/core/Admin/Controllers/Actions/Setting/StoreSetting/DeleteInformationAction.php)
- Action dedicada para excluir páginas institucionais personalizadas via AJAX ou requisição POST, impedindo a exclusão acidental das 4 páginas de sistema (IDs 1, 2, 3 e 4).

---

### 3. Interface do Usuário (Twig, CSS e JavaScript)

#### [MODIFY] [edit.html.twig](/resources/views/admin/setting/store_setting/edit.html.twig)
- Adicionar a aba **Informações** na navegação principal por abas.
- Criar a sub-navegação em botões/pílulas (Pills) para alternar entre as páginas.
- Adicionar cabeçalho com o botão **"+ Nova Página Institutional"** que abre um modal ou revela formulário de adição.
- Implementar caixas de texto com o botão **"Pré-visualizar Conteúdo"** usando a biblioteca de renderização em tempo real (ou script integrado Parsedown JS/Showdown).
- Adicionar validações visuais e botões para Salvar e Excluir.

---

## 🧪 Plano de Verificação

### Automated & Unit Verification
- Script PHP para testar a criação, atualização e remoção de registros em `agsc_information`, `agsc_information_description`, `agsc_information_to_store` e `agsc_seo_url`.

### Manual Verification
1. Abrir `/configuracoes` no painel.
2. Acessar a aba **Informações**.
3. Editar uma página existente (ex: "Termos e Condições") usando Markdown e conferir a pré-visualização ao vivo.
4. Criar uma **Nova Página** (ex: "Garantia e Suporte").
5. Verificar no front-end em `/{lang}/pagina/garantia-e-suporte` se a nova página carrega perfeitamente e aparece no rodapé da loja.

# Tarefas de Execução: Aba "Informações" e Gestão de Páginas Institucionais

- `[x]` Implementar métodos de persistência de páginas institucionais em `InformationMapper.php` e `InformationRepository.php`
- `[x]` Criar/atualizar as Actions do Admin (`EditStoreSettingAction.php`, `UpdateStoreSettingAction.php`, `DeleteInformationAction.php`)
- `[x]` Adicionar rota para exclusão de páginas institucionais personalizadas em `Config/Routes.php`
- `[x]` Atualizar a View Twig `resources/views/admin/setting/store_setting/edit.html.twig` (nova aba Informações, sub-abas, modal/form de nova página e Live Preview Markdown)
- `[x]` Testar e verificar criação, edição, exclusão e exibição no front-end (`/pagina/{slug}`)

# Resumo da Implementação: Aba "Informações" no Dashboard de Configurações

A nova aba **"Informações"** foi adicionada à página de **Configurações da Loja** (`/configuracoes`) no painel administrativo. Ela permite ao gestor editar centralizadamente o conteúdo textual, títulos, slugs SEO e status de exibição de todas as páginas institucionais da loja, além de criar novas páginas personalizadas.

---

## 🌟 Principais Recursos Implementados

1. **Gestão das Páginas Institucionais Padrão do Sistema:**
   - **Sobre Nós** (ID 1)
   - **Termos e Condições** (ID 2)
   - **Política de Privacidade** (ID 3)
   - **Informações de Entrega** (ID 4)
2. **Editor Markdown com Pré-Visualização ao Vivo (Live Preview):**
   - Suporte a visualização **Lado a Lado** (Split screen), **Apenas Editor** ou **Apenas Pré-visualização**.
   - Renderização instantânea no navegador para títulos (`#`), listas (`-`), negritos (`**`), citações e links sem recarregar a página.
3. **Criação de Novas Páginas Institucionais Personalizadas:**
   - Formulário em sub-aba **"+ Criar Nova"** para adicionar novas páginas (ex: *"Política de Trocas e Devoluções"*, *"Garantia"*).
   - Definição de URL amigável (`/pagina/sua-url-slug`), ordem de exibição no rodapé e metadados SEO.
4. **Proteção e Exclusão Segura:**
   - Botão para excluir páginas institucionais personalizadas (`DeleteInformationAction.php`).
   - Proteção de segurança contra a exclusão acidental das 4 páginas institucionais padrão do sistema.

---

## 🛠️ Arquivos Modificados e Criados

- [InformationMapper.php](/core/Mappers/EntityMappers/InformationMapper.php): Adicionados métodos `getAllInformationsAdmin()`, `getInformationForAdmin()`, `createInformation()`, `updateInformation()`, `deleteInformation()` e `saveSeoUrl()`.
- [InformationRepository.php](/core/Model/Domain/Repositories/InformationRepository.php): Expostos os métodos de administração e controle de páginas institucionais.
- [EditStoreSettingAction.php](/core/Admin/Controllers/Actions/Setting/StoreSetting/EditStoreSettingAction.php): Injetada a coleção de páginas institucionais no Twig.
- [UpdateStoreSettingAction.php](/core/Admin/Controllers/Actions/Setting/StoreSetting/UpdateStoreSettingAction.php): Adicionada lógica de processamento e persistência das edições e novas páginas.
- [DeleteInformationAction.php](/core/Admin/Controllers/Actions/Setting/StoreSetting/DeleteInformationAction.php): Nova Action para remover páginas institucionais personalizadas.
- [Routes.php](/Config/Routes.php): Registrada a rota `admin.setting.information.delete`.
- [edit.html.twig](/resources/views/admin/setting/store_setting/edit.html.twig): Interface com a quinta aba **Informações**, navegação por pílulas (sub-tabs), editor Live Preview e estilos CSS.

---

## 🧪 Validação dos Testes

Executamos testes automatizados no banco de dados (`test_info_crud.php`), confirmando:
- Lista e leitura de páginas institucionais.
- Criação de nova página personalizada com slug SEO.
- Atualização de conteúdo e metadados.
- Exclusão de página personalizada.
- Proteção ativa das páginas padrão do sistema (tentativa de exclusão do ID 1 recusada com sucesso).

