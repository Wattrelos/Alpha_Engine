# Registro de Modificações IA

---

### Correção de Erro Fatal no Controlador de Registro (Captcha)

- **Implementação:** Injeção da dependência `ExtensionRepository` no `catalog/controller/account/register.php` e substituição da chamada legada `$this->model_setting_extension->getExtensionByCode(...)` por uma busca em array via `$this->extensionRepository->search(...)`. Correção do escopo no `AlphaContainer.php`, promovendo `setting/extension` de um *Mapper* para um *Repository* completo.
- **Motivo:** O erro `Notice: Undefined property: Proxy::getExtensionByCode` ocorria porque o `AlphaContainer` interceptava o carregamento do modelo de extensões e retornava a nova classe orquestrada da Alpha Engine. Como a Alpha Engine desobriga a manutenção de milhares de métodos verbosos (`getByCode`, `getById`, `getByType`), substituindo-os pela função genérica de extração estruturada `search()`, o método legado chamado pelo controlador não era encontrado, derrubando a interface no momento em que a loja tentava renderizar/validar o Captcha do registro.
- **Benefício:** A página de Cadastro de Clientes volta a funcionar de imediato. Isso avança nosso princípio arquitetural, eliminando invocações aos modelos da "era das trevas" de dentro do controlador (limpando o código obsoleto do OpenCart) e delegando o resgate das configurações de extensão puramente para a camada unificada de Repositório do Domínio.