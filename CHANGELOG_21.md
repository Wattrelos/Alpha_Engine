# Registro de Modificações IA

---

### Padronização da Extração no ExtensionRepository (register.php)

- **Implementação:** Substituição da tentativa de usar métodos customizados (`search`, `findBy`, `getExtensionsByType`) pelo método de contrato universal `findAll()`, inerente a todos os repositórios baseados na interface da Alpha Engine.
- **Implementação:** Injeção de uma lógica de hidratação condicional que extrai corretamente os atributos da entidade `Extension` usando *getters* (ex: `getType()`) em vez de tratá-la apenas como um array. O resultado é consolidado num array limpo para ser aceito pelas bibliotecas nativas de validação do OpenCart.
- **Motivo:** O ecossistema nativo do OpenCart (Views e Loaders) espera um array de dados, enquanto a Alpha Engine retorna Entidades estritamente tipadas. Os erros ocorriam primeiro pela invocação de métodos de busca inexistentes na classe, e posteriormente poderiam causar falhas do tipo `Cannot use object of type Extension as array`.
- **Benefício:** Resolução completa e definitiva do gargalo na inicialização do Captcha. O repositório extrai a coleção (instantaneamente da memória cache), o controlador mapeia a configuração sem conflitos de tipagem, mantendo o "Skinny Controller" 100% interoperável com o OpenCart 4, sem precisar reescrever a página do zero.