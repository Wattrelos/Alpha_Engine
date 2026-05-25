# Registro de Modificações IA

---

### Refatoração: Padronização Alpha Engine nos Controladores de Conta Complementares

- **Implementação:** Limpeza nos arquivos secundários da conta do cliente (`affiliate.php`, `authorize.php`, `download.php` e `forgotten.php`). Todos os despachadores antigos `$this->load->model()` foram substituídos pelas injeções dinâmicas `$this->getRepository(...)`. Garantida a extensão de `BaseController` para `Authorize` e `Forgotten`, limpando a carga manual de templates e headers que davam margem para o *White Screen of Death* (WSOD). JSON responses foram consolidados no padrão `$this->jsonResponse()`.
- **Motivo:** Esses controladores ainda continham resquícios cruciais do núcleo MVC legado do OpenCart. Rotas como "Esqueci minha senha" e "Downloads" carregavam e inicializavam bibliotecas da base de dados antiga e não estavam protegidas pelo `ViewRenderer` nativo em caso de erro nos arquivos Twig.
- **Benefício:** Uniformidade técnica em todo o diretório de Controle de Conta. Todas as requisições agora estão blindadas pelo escopo *Anti-WSOD* e ganham velocidade ao utilizar o carregamento preguiçoso dos novos Repositórios e Mappers da Alpha Engine. A manutenção de resposta AJAX agora obedece o *wrapper* interno, mitigando duplicação de códigos para definição de cabeçalhos nas rotas assíncronas.