---
### Refatoração Core: Blindagem Defensiva na Classe Twig Nativa
---
**Implementação:**
- Substituição do bloco `catch (\Twig\Error\SyntaxError)` por `catch (\Throwable)` no método `render()` do arquivo `system/library/template/twig.php`. Injeção de rotina de expurgo de buffer de saída (`ob_end_clean()`) e gravação isolada de log (`alpha_twig_engine_trace.log`).
**Motivo:**
- A implementação original do OpenCart falhava criticamente em ambientes PHP 8+ por capturar estritamente erros de sintaxe do próprio motor Twig. Falhas de tipagem (`TypeError`) ou funções indefinidas disparavam instâncias da classe nativa `\Error`, vazando o escopo de execução e causando o clássico *White Screen of Death (WSOD)*.
**Benefício:**
- O cerco defensivo (Anti-WSOD) da Alpha Engine agora cobre todas as vias de renderização possíveis do sistema original. Independentemente de ser um módulo mal codificado ou uma atualização de tema imprecisa, a falha será amortecida de forma segura, o buffer da memória limpo e o trace exato da linha/motivo do problema será reportado no log do servidor.

