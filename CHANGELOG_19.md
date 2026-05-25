# Registro de Modificações IA

---

### Correção: Uso de findOneBy no ExtensionRepository

- **Implementação:** Substituição da chamada `findBy()` pelo método `findOneBy()` nativo da Alpha Engine no controlador `catalog/controller/account/register.php`, eliminando a necessidade de validação de índice `[0]` em array.
- **Motivo:** O método `findBy()` (que retorna coleções) não foi mapeado/declarado na classe base de repositórios da Alpha Engine, causando o erro `Call to undefined method`. A intenção do bloco de código era buscar apenas uma extensão específica baseada em critérios. O método correto implementado no `BaseRepository` para extrair uma única entidade é o `findOneBy()`.
- **Benefício:** Código mais limpo, limando o erro fatal e acessando diretamente o registro de configuração do Captcha necessário para renderização da página de cadastro.