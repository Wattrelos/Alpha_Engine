# Registro de Modificações IA

---

### Refatoração de Eventos Globais: Adoção do Padrão Alpha Engine

- **Implementação:** Os controladores de eventos (`activity.php`, `statistics.php`, e `theme.php`) agora estendem `Alpha\Controller\BaseController`. Todas as invocações obsoletas de banco de dados (`$this->load->model()`) foram atualizadas para consumir `ActivityRepository`, `CustomerRepository`, `OrderRepository`, `StatisticsRepository` e `ThemeRepository` através da injeção de dependências com Lazy Loading (`$this->getRepository()`).
- **Motivo:** O sistema de eventos nativo do OpenCart (hookado através do banco de dados para disparar funções de histórico e estatística antes/depois de ações importantes) operava isolado com as Models legadas. Para descontinuar de forma segura o modelo MVC antigo do core e manter a estabilidade da orquestração de transações, senhas e atividades de usuário, era imperativo que os gatilhos também fossem modernizados.
- **Benefício:** Consistência absoluta em 100% dos relatórios da loja. Agora, qualquer evento acionado por compras, avaliações, devoluções, trocas de layout ou senhas esquecidas está sendo armazenado pela via oficial e tipada do sistema (Repository -> Mapper), sem fugir da blindagem e observabilidade do *DataAccessObject*.