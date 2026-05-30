## 📦 Estrutura das páginas Twig


### Página Header

ag-header
  └── egen-header__container   (max-width: 1320px, centralizado)
       └── egen-header__grid   (grid: 1.5fr 1fr 1fr 1fr 1fr)
            ├── egen-header__brand-col  (logo + desc + redes sociais)
            ├── header-column × 4       (via molecule)
  └── ag-header-bottom
       └── egen-header__container
            └── egen-header__bottom-row  (copyright ←→ pagamentos)


### Página rodapé:
ag-footer
  └── egen-footer__container   (max-width: 1320px, centralizado)
       └── egen-footer__grid   (grid: 1.5fr 1fr 1fr 1fr 1fr)
            ├── egen-footer__brand-col  (logo + desc + redes sociais)
            ├── footer-column × 4       (via molecule)
  └── ag-footer-bottom
       └── egen-footer__container
            └── egen-footer__bottom-row  (copyright ←→ pagamentos)


### 





### HTML/Twig

### Assets
public_html/css/      # CSS Compilado
public_html/js/       # JS Compilado
public_html/fonts/    # Fontes



resources/views/
├── components/
│   ├── atoms/        # componentes atômicos (botões, inputs, cards)
│   ├── molecules/    # componentes moleculares (footer-column, navbar-item, product-card)
│   └── organisms/    # componentes orgânicos (header, footer, sidebar, full-product-card)
├── utilities/        # classes utilitárias (helpers)
└── pages/            # estilos específicos por página
     └── users 
          ├── register.html.twig
          ├── login.html.twig
          ├── email-verification.html.twig
          └── account-dashboard.html.twig
        





### Fontes

resources/fonts/
└── fontawesome/      # FontAwesome 6
```