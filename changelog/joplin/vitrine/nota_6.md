---
#### 💡 Insights de Persistência
---
*   A inclusão explícita de campos de descrição no `ProductMapper` resolve o erro histórico de vitrines vazias.
*   O uso de `float` no preço não é estético, é uma trava de segurança para o motor de impostos e frete.
*   O carregamento de categorias agora utiliza o método `readByIds` para evitar o problema de *N+1 queries* em menus complexos.
---
*Trabalho em constante evolução para elevar o padrão de engenharia do catálogo.*
