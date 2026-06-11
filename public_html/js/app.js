/**
 * Alpha Engine - Main App Script
 * Handles header interactions, sticky states, and mobile touch submenus.
 */
(function () {
    'use strict';

    const initHeader = () => {
        // 1. Sticky Header Functionality (Optimized with requestAnimationFrame)
        const telhado = document.querySelector('.egen-telhado');
        if (telhado) {
            let isScrolling = false;

            const handleScroll = () => {
                if (window.scrollY > 50) {
                    telhado.classList.add('sticky');
                } else {
                    telhado.classList.remove('sticky');
                }
                isScrolling = false;
            };
            
            // Run once on load to handle initial position
            handleScroll();
            
            // Listen to window scroll optimally
            window.addEventListener('scroll', () => {
                if (!isScrolling) {
                    window.requestAnimationFrame(handleScroll);
                    isScrolling = true;
                }
            }, { passive: true });
        }

        // 2. Touch/Click Navigation support for recursive dropdown menu on mobile devices
        const menuButton = document.querySelector('.egen-menu-button-left');
        const mainDropdown = document.querySelector('.egen-dropdown-menu-recursive');

        if (menuButton && mainDropdown) {
            menuButton.addEventListener('click', (e) => {
                if (window.innerWidth < 992) {
                    e.preventDefault();
                    e.stopPropagation();
                    mainDropdown.classList.toggle('show');
                }
            });

            // Optimized with Event Delegation instead of multiple listeners
            mainDropdown.addEventListener('click', (e) => {
                if (window.innerWidth >= 992) return;

                const link = e.target.closest('a');
                if (!link) return;

                const li = link.closest('li');
                if (!li) return;

                const submenu = li.querySelector('.egen-submenu');
                if (submenu) {
                    const isOpen = submenu.classList.contains('show');
                    
                    // Close other submenus at the same level
                    const parentUl = li.closest('ul');
                    if (parentUl) {
                        const openSubmenus = parentUl.querySelectorAll('.egen-submenu.show');
                        openSubmenus.forEach(sub => {
                            if (sub !== submenu) {
                                sub.classList.remove('show');
                            }
                        });
                    }
                    
                    e.preventDefault();
                    e.stopPropagation();

                    if (!isOpen) {
                        submenu.classList.add('show');
                    } else {
                        submenu.classList.remove('show');
                    }
                }
            });
        }

        // 3. Close menus when clicking outside the menu container
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.ag-menu-container')) {
                if (mainDropdown) {
                    mainDropdown.classList.remove('show');
                }
                const submenus = document.querySelectorAll('.egen-submenu.show');
                submenus.forEach(sub => sub.classList.remove('show'));
            }
        });

        // 4. Global Wishlist Button Listener (Event Delegation)
        // Gerencia os cliques nos botões de wishlist, protegendo elementos injetados dinamicamente (AJAX).
        document.addEventListener('click', (e) => {
            const wishlistBtn = e.target.closest('.egen-add-to-wishlist-btn');
            if (wishlistBtn) {
                e.preventDefault();
                const productId = wishlistBtn.getAttribute('data-product-id');
                
                if (productId) {
                    // Aqui nós interceptamos o produto correto a partir do escopo isolado do DOM
                    console.log('Interceptado clique para Wishlist. Produto ID:', productId);
                    
                    // TODO: Implementar a chamada AJAX nativa da Alpha Engine
                    /* fetch('/api/wishlist/add', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ product_id: productId })
                    }).then(res => res.json()).then(data => { alert('Adicionado à wishlist!'); }); */
                    
                    // Feedback visual usando o novo sistema global de notificações
                    window.showNotification('Produto adicionado à sua lista de desejos!', 'success');
                }
            }
        });

        // 5. Global Notification System (Toast)
        // Disponibilizado no escopo global (window) para ser consumido por qualquer módulo ou AJAX.
        window.showNotification = (message, type = 'success') => {
            let container = document.getElementById('egen-toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'egen-toast-container';
                // Estilização estrutural do container de notificações flutuantes
                Object.assign(container.style, {
                    position: 'fixed',
                    top: '20px',
                    right: '20px',
                    zIndex: '9999',
                    display: 'flex',
                    flexDirection: 'column',
                    gap: '10px',
                    pointerEvents: 'none'
                });
                document.body.appendChild(container);
            }

            const toast = document.createElement('div');
            // Reutiliza o design de alertas premium existente no new-stylesheet.css
            toast.className = `egen-alert egen-alert--${type}`;
            Object.assign(toast.style, {
                margin: '0',
                boxShadow: '0 10px 30px rgba(0,0,0,0.5)',
                opacity: '0',
                transform: 'translateX(100%)',
                transition: 'all 0.4s cubic-bezier(0.16, 1, 0.3, 1)',
                pointerEvents: 'auto',
                minWidth: '280px'
            });

            const iconClass = type === 'success' ? 'fa-circle-check' : (type === 'danger' ? 'fa-circle-exclamation' : 'fa-circle-info');
            toast.innerHTML = `<i class="fa-solid ${iconClass}" style="font-size: 1.1rem; display: flex; align-items: center;"></i> <span>${message}</span>`;

            container.appendChild(toast);

            // Anima a entrada (Slide In)
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    toast.style.opacity = '1';
                    toast.style.transform = 'translateX(0)';
                });
            });

            // Auto-remove a notificação após 3.5 segundos (Slide Out)
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100%)';
                setTimeout(() => toast.remove(), 400); // Aguarda o CSS terminar a transição
            }, 3500);
        };

        // 6. Global Add to Cart Form Listener (AJAX)
        // Intercepta a submissão nativa do formulário e converte para AJAX assíncrono.
        document.addEventListener('submit', (e) => {
            const cartForm = e.target.closest('.egen-prod-card-form');
            if (cartForm) {
                e.preventDefault(); // Impede o redirecionamento/recarregamento da página
                
                const formData = new FormData(cartForm);
                const actionUrl = cartForm.getAttribute('action');
                
                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' // Sinaliza ao backend que é uma requisição AJAX
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Falha ao adicionar ao carrinho');
                    return response.json().catch(() => ({})); // Tenta parsear JSON se disponível
                })
                .then(data => {
                    window.showNotification('Produto adicionado ao carrinho com sucesso!', 'success');
                    
                    // Atualiza dinamicamente as badges do carrinho pelo DOM
                    const quantityAdded = parseInt(formData.get('quantity') || 1, 10);
                    const badges = document.querySelectorAll('.egen-cart-badge');
                    
                    badges.forEach(badge => {
                        // Incrementa o valor atual (ou usa a resposta da API se o seu backend já retornar algo como data.total_items)
                        let currentCount = parseInt(badge.textContent || 0, 10);
                        let newCount = (data && data.total_items !== undefined) ? parseInt(data.total_items, 10) : currentCount + quantityAdded;
                        badge.textContent = newCount;
                        
                        // Pequena animação de "pulso" para chamar atenção visual do usuário
                        badge.style.transition = 'transform 0.2s ease';
                        badge.style.transform = 'scale(1.4)';
                        setTimeout(() => badge.style.transform = 'scale(1)', 200);
                    });
                })
                .catch(error => {
                    console.error('Cart Error:', error);
                    window.showNotification('Erro ao adicionar produto ao carrinho. Tente novamente.', 'danger');
                });
            }
        });

        // 7. Global Login Form Listener (AJAX)
        // Intercepta formulários de login para exibir falhas e sucessos usando o sistema de Toast nativo.
        document.addEventListener('submit', (e) => {
            const loginForm = e.target.closest('.egen-login-form');
            if (loginForm) {
                e.preventDefault(); 
                
                const formData = new FormData(loginForm);
                const actionUrl = loginForm.getAttribute('action');
                
                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json' // Otimiza a resposta do backend informando que esperamos JSON
                    }
                })
                .then(async (response) => {
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        throw data; // Lança o payload de erro para ser tratado no bloco catch
                    }
                    
                    window.showNotification(data.success || 'Login realizado com sucesso! Redirecionando...', 'success');
                    setTimeout(() => window.location.href = data.redirect || '/conta', 1200); // Aguarda 1.2s para que o usuário leia a notificação
                })
                .catch(error => {
                    console.error('Login Error:', error);
                    const errorMsg = error.error || error.message || 'E-mail e/ou senha incorretos. Tente novamente.';
                    window.showNotification(errorMsg, 'danger');
                });
            }
        });
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeader);
    } else {
        initHeader();
    }
})();
