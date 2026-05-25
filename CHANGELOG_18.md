# Registro de Modificações IA

---

### Correção de Nomenclatura de Método no ExtensionRepository

- **Implementação:** Substituição da chamada de método `search()` para `findBy()` no controlador `catalog/controller/account/register.php`.
- **Motivo:** O repositório base da *Alpha Engine* segue o padrão de nomenclatura arquitetural assente no Doctrine/Repository Pattern para recuperar dados. O método sugerido anteriormente (`search`) não existia, causando o *Fatal Error* `Call to undefined method`.
- **Benefício:** Restaura o carregamento e verificação do Captcha na tela de Cadastro de Clientes, garantindo que o Repositório de Extensões consiga extrair as informações corretas e injetar o controlador do Captcha sem quebrar o sistema.