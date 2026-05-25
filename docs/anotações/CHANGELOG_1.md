---
**Data:** 2024-06-08 (Atual)
**Tipo:** Correção de Bug
**Descrição:** Correção de assinatura de método no evento de tradução.
**Detalhes:** O arquivo `action.php` estava disparando um erro fatal ao chamar `Translation::index()`, pois este método aguardava o segundo argumento como um `array`, enquanto o sistema de eventos desempacota os argumentos (`...$args`). O gatilho `language/*/before` envia as strings `$route`, `$prefix` e `$code`. A assinatura do método em `translation.php` foi corrigida para aceitar as 3 strings corretamente por referência.
**Benefícios:** Restabelece o funcionamento do carregamento de traduções por eventos, impedindo o erro fatal 500 durante o fluxo inicial (boot) da aplicação.