---
### Refatoração Core: Resiliência de I/O na Compilação de Templates
---
**Implementação:**
- Inclusão de verificação estrita de retorno booleano (`$written === false`) na função nativa `file_put_contents` dentro do método `compile()` na classe `Opencart\System\Library\Template\Template`.
**Motivo:**
- O método nativo falhava em validar o sucesso da escrita física no disco para o cache de templates compilados. Em cenários de exaustão de armazenamento (*Disk Full*) ou corrupção de permissões no diretório `DIR_CACHE`, o método continuava a execução informando que o arquivo existia, causando avisos catastróficos durante o `include()` subsequente.
**Benefício:**
- Alinhamento com a nova diretriz de proteção *Anti-WSOD*. Ao lançar uma `\RuntimeException` imediatamente no momento da falha de I/O, a exceção é interceptada pelo bloco `try-catch` implementado anteriormente na camada `render()`, registrando a verdadeira causa do problema no log e prevenindo quebras não rastreáveis na interface da loja. Mantém-se o uso correto do disco para permitir a alocação de Bytecodes no *Zend OPcache*.

