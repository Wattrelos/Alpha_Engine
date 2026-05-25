# Registro de Modificações IA

---

### Correção da busca de extensões no ExtensionRepository (register.php)

- **Implementação:** O controlador `register.php` foi modificado para utilizar o método `$this->extensionRepository->getExtensionsByType('captcha')`, iterando sobre o resultado para encontrar a configuração do captcha ativo.
- **Motivo:** O repositório específico `ExtensionRepository` foi desenvolvido conservando as nomenclaturas estruturais e seguras nativas do OpenCart para extração de módulos (como `getExtensionsByType`), e não adotou totalmente o mapeamento abstrato de extração (como os métodos `findOneBy` que tentamos usar antes). 
- **Benefício:** Resolução definitiva dos `Fatal Errors` de carregamento na tela de registro. O controlador de cadastro passa a identificar e invocar a validação/exibição do Captcha perfeitamente.