/**
 * Beta Engine - APRESENTAÇÃO ACADÊMICA FATEC-FV
 * Script Principal de Controle e Interatividade da UI
 */

(function () {
  'use strict';

  // Lista sequencial de páginas da apresentação
  const PAGE_SEQUENCE = [
    'index.html',
    '01_visao.html',
    '02_processos_atividades.html',
    '03_requisitos_regras.html',
    '04_casos_de_uso.html',
    '05_matriz_rastreabilidade.html',
    '06_arquitetura_dados.html',
    '07_diagramas_sequencia.html',
    '08_checklist_disciplina.html'
  ];

  // 1. Gerenciamento de Tema (Dark / Light)
  const themeToggleBtn = document.getElementById('themeToggleBtn');
  const savedTheme = localStorage.getItem('alpha_theme') || 'dark';
  document.documentElement.setAttribute('data-theme', savedTheme);
  updateThemeIcon(savedTheme);

  if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', () => {
      const current = document.documentElement.getAttribute('data-theme') || 'dark';
      const next = current === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', next);
      localStorage.setItem('alpha_theme', next);
      updateThemeIcon(next);
    });
  }

  function updateThemeIcon(theme) {
    if (!themeToggleBtn) return;
    const icon = themeToggleBtn.querySelector('.theme-icon');
    if (!icon) return;
    if (theme === 'dark') {
      icon.innerHTML = `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>`;
      themeToggleBtn.title = 'Alternar para Tema Claro (T)';
    } else {
      icon.innerHTML = `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>`;
      themeToggleBtn.title = 'Alternar para Tema Escuro (T)';
    }
  }

  // 2. Modo Apresentação (Slides / Pitch) vs. Modo Dossiê
  const modeToggleBtn = document.getElementById('modeToggleBtn');
  const savedMode = localStorage.getItem('alpha_view_mode') || 'dossier';
  if (savedMode === 'presentation') {
    document.body.classList.add('presentation-mode');
    updateModeBtnText(true);
  }

  if (modeToggleBtn) {
    modeToggleBtn.addEventListener('click', () => {
      const isPres = document.body.classList.toggle('presentation-mode');
      localStorage.setItem('alpha_view_mode', isPres ? 'presentation' : 'dossier');
      updateModeBtnText(isPres);
    });
  }

  function updateModeBtnText(isPres) {
    if (!modeToggleBtn) return;
    const label = modeToggleBtn.querySelector('.mode-label');
    if (label) {
      label.textContent = isPres ? 'Modo Dossiê' : 'Modo Apresentação';
    }
    modeToggleBtn.classList.toggle('active', isPres);
  }

  // 3. Barra de Progresso de Leitura
  const progressBar = document.querySelector('.progress-bar');
  if (progressBar) {
    window.addEventListener('scroll', () => {
      const winScroll = document.body.scrollTop || document.documentElement.scrollTop;
      const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
      const scrolled = height > 0 ? (winScroll / height) * 100 : 0;
      progressBar.style.width = scrolled + '%';
    });
  }

  // 4. Menu Mobile
  const menuToggle = document.getElementById('menuToggle');
  const sidebar = document.querySelector('.sidebar');
  if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });

    document.addEventListener('click', (e) => {
      if (!sidebar.contains(e.target) && !menuToggle.contains(e.target) && sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
      }
    });
  }

  // 5. Destacar Página Atual na Sidebar
  const currentPath = window.location.pathname.split('/').pop() || 'index.html';
  const navItems = document.querySelectorAll('.sidebar-nav .nav-item');
  navItems.forEach((item) => {
    const href = item.getAttribute('href');
    if (href === currentPath || (currentPath === '' && href === 'index.html')) {
      item.classList.add('active');
    }
  });

  // 6. Navegação por Teclado (Setas para Páginas, F para Fullscreen, T para Tema)
  document.addEventListener('keydown', (e) => {
    // Se o usuário estiver digitando em um input de busca ou texto, não interceptar
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

    // Fechar modal com Escape
    if (e.key === 'Escape') {
      const activeModal = document.querySelector('.modal-overlay.active');
      if (activeModal) {
        activeModal.classList.remove('active');
      }
      if (sidebar && sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
      }
      return;
    }

    // Alternar Tema (T)
    if (e.key === 't' || e.key === 'T') {
      if (themeToggleBtn) themeToggleBtn.click();
      return;
    }

    // Alternar Modo Apresentação (P)
    if (e.key === 'p' || e.key === 'P') {
      if (modeToggleBtn) modeToggleBtn.click();
      return;
    }

    // Tela Cheia (F)
    if (e.key === 'f' || e.key === 'F') {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
      } else {
        if (document.exitFullscreen) document.exitFullscreen().catch(() => {});
      }
      return;
    }

    // Navegar para Página Anterior (Seta Esquerda)
    if (e.key === 'ArrowLeft') {
      navigateToAdjacentPage(-1);
    }

    // Navegar para Próxima Página (Seta Direita)
    if (e.key === 'ArrowRight') {
      navigateToAdjacentPage(1);
    }
  });

  function navigateToAdjacentPage(offset) {
    const activePage = window.location.pathname.split('/').pop() || 'index.html';
    const currentIndex = PAGE_SEQUENCE.indexOf(activePage);
    if (currentIndex === -1) return;

    const targetIndex = currentIndex + offset;
    if (targetIndex >= 0 && targetIndex < PAGE_SEQUENCE.length) {
      window.location.href = PAGE_SEQUENCE[targetIndex];
    }
  }

  // 7. Botão de Tela Cheia na Topbar
  const fullscreenBtn = document.getElementById('fullscreenBtn');
  if (fullscreenBtn) {
    fullscreenBtn.addEventListener('click', () => {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
      } else {
        if (document.exitFullscreen) document.exitFullscreen().catch(() => {});
      }
    });
  }

})();
