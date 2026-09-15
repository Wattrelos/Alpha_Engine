# Guia de Execução & Apresentação da Suíte BDD

Consulte as especificações detalhadas do módulo em [README.md](/features/frontend/README.md).

---

## 🖥️ Modos de Execução dos Testes

### 1. Execução ao Vivo no Terminal (Modo Apresentação / Formato Pretty)
Para demonstrar a execução passo a passo em sala de aula ou revisão:

```bash
./vendor/bin/behat --format=pretty
```

### 2. Execução Silenciosa por Módulo
```bash
# Frontend
./vendor/bin/behat features/frontend/ --no-snippets

# Carrinho (Cart)
./vendor/bin/behat features/cart/ --no-snippets

# Checkout
./vendor/bin/behat features/checkout/ --no-snippets
```

### 3. Atalhos via Composer
```bash
composer test:behat
composer test:phpunit
```
