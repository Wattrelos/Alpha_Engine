# Registro de Modificações IA

---

### Correção de Boot/Bootstrap: White Screen of Death (WSOD) e Desacoplamento de Pre-Actions

- **Implementação:** Criação da Entidade `Extension` que havia sido omitida no ecossistema de Domínio. Refatoração dos Controladores de Startup (`startup.php`, `event.php`, `extension.php`, `language.php`, `seo_url.php`) substituindo a herança pesada `BaseController` pela controladora nativa `\Opencart\System\Engine\Controller`.
- **Motivo:** O sistema estava falhando silenciosamente e devolvendo uma página branca porque o `BaseController` injeta dependências de interface gráfica (como o `LayoutRepository`) em um momento onde a loja ainda está em *bootstrap* e as configurações globais (Store ID, DB) não estão completamente prontas. Aliado a isso, a omissão da entidade `Extension` resultava num Fatal Error (`Class not found`) antes mesmo do tratador de exceções subir.
- **Benefício:** Restaura o acesso imediato à frente da loja. Pre-Actions são ações rodadas estritamente nos "bastidores" e precisam ser leves. Herdar da classe nativa consolida uma arquitetura segura, eliminando *overhead* e travamentos sem registro de log.