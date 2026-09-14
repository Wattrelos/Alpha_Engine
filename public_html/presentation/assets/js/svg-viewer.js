/**
 * ALPHA ENGINE - VISUALIZADOR MODAL DE DIAGRAMAS SVG COM PAN & ZOOM
 * Suporte a zoom suave por roda do mouse, arrasto (pan) e tela cheia
 */

(function () {
  'use strict';

  // Injetar estrutura do Modal se ainda não existir no DOM
  function ensureModalExists() {
    if (document.getElementById('svgViewerModal')) return;

    const modalHTML = `
      <div id="svgViewerModal" class="modal-overlay" role="dialog" aria-modal="true" aria-labelledby="modalDiagramTitle">
        <div class="modal-topbar">
          <div class="modal-title-box">
            <span id="modalDiagramTitle" class="modal-title">Visualizador de Diagrama Técnico</span>
            <span id="modalDiagramSubtitle" class="modal-subtitle">Use a roda do mouse para Zoom e arraste para navegar</span>
          </div>
          <div class="modal-controls">
            <button type="button" class="modal-btn" id="modalZoomInBtn" title="Aproximar (+)">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
              Zoom +
            </button>
            <button type="button" class="modal-btn" id="modalZoomOutBtn" title="Afastar (-)">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
              Zoom -
            </button>
            <button type="button" class="modal-btn" id="modalResetBtn" title="Ajustar ao Padrão (100%)">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path><path d="M3 3v5h5"></path></svg>
              Reset
            </button>
            <a id="modalDownloadBtn" class="modal-btn" download title="Baixar Arquivo SVG Original">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
              SVG
            </a>
            <button type="button" class="modal-btn close-btn" id="modalCloseBtn" title="Fechar Visualizador (Esc)">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
              Fechar
            </button>
          </div>
        </div>
        <div class="modal-viewport" id="modalViewport">
          <div class="modal-image-wrap" id="modalImageWrap">
            <img id="modalSvgImage" src="" alt="Diagrama Técnico" />
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
    setupModalEvents();
  }

  let scale = 1;
  let translateX = 0;
  let translateY = 0;
  let isDragging = false;
  let startX = 0;
  let startY = 0;

  function updateTransform() {
    const wrap = document.getElementById('modalImageWrap');
    if (wrap) {
      wrap.style.transform = `translate(${translateX}px, ${translateY}px) scale(${scale})`;
    }
  }

  function resetTransform() {
    scale = 1;
    translateX = 0;
    translateY = 0;
    updateTransform();
  }

  function setupModalEvents() {
    const modal = document.getElementById('svgViewerModal');
    const viewport = document.getElementById('modalViewport');
    const closeBtn = document.getElementById('modalCloseBtn');
    const zoomInBtn = document.getElementById('modalZoomInBtn');
    const zoomOutBtn = document.getElementById('modalZoomOutBtn');
    const resetBtn = document.getElementById('modalResetBtn');

    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        modal.classList.remove('active');
      });
    }

    if (zoomInBtn) {
      zoomInBtn.addEventListener('click', () => {
        scale = Math.min(scale * 1.25, 8);
        updateTransform();
      });
    }

    if (zoomOutBtn) {
      zoomOutBtn.addEventListener('click', () => {
        scale = Math.max(scale / 1.25, 0.25);
        updateTransform();
      });
    }

    if (resetBtn) {
      resetBtn.addEventListener('click', resetTransform);
    }

    // Zoom via Roda do Mouse
    if (viewport) {
      viewport.addEventListener('wheel', (e) => {
        e.preventDefault();
        const delta = e.deltaY > 0 ? 0.85 : 1.15;
        scale = Math.min(Math.max(scale * delta, 0.2), 10);
        updateTransform();
      }, { passive: false });

      // Arrasto com mouse (Pan)
      viewport.addEventListener('mousedown', (e) => {
        if (e.button !== 0) return; // apenas botão esquerdo
        isDragging = true;
        startX = e.clientX - translateX;
        startY = e.clientY - translateY;
      });

      window.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        translateX = e.clientX - startX;
        translateY = e.clientY - startY;
        updateTransform();
      });

      window.addEventListener('mouseup', () => {
        isDragging = false;
      });
    }
  }

  // Função global para abrir o modal de diagrama
  window.openDiagramModal = function (src, title, subtitle) {
    ensureModalExists();
    const modal = document.getElementById('svgViewerModal');
    const img = document.getElementById('modalSvgImage');
    const titleEl = document.getElementById('modalDiagramTitle');
    const subEl = document.getElementById('modalDiagramSubtitle');
    const downloadBtn = document.getElementById('modalDownloadBtn');

    if (img) img.src = src;
    if (titleEl) titleEl.textContent = title || 'Diagrama Técnico';
    if (subEl) subEl.textContent = subtitle || 'Visualização interativa em alta fidelidade';
    if (downloadBtn) {
      downloadBtn.href = src;
      downloadBtn.setAttribute('download', src.split('/').pop() || 'diagrama.svg');
    }

    resetTransform();
    modal.classList.add('active');
  };

  // Auto-vincular cliques em containers de diagrama
  document.addEventListener('DOMContentLoaded', () => {
    ensureModalExists();

    const frames = document.querySelectorAll('.diagram-frame');
    frames.forEach((frame) => {
      frame.addEventListener('click', () => {
        const img = frame.querySelector('img');
        if (!img) return;
        const container = frame.closest('.diagram-container');
        const title = container ? container.querySelector('.diagram-title')?.textContent : img.alt;
        const badge = container ? container.querySelector('.diagram-badge')?.textContent : '';
        window.openDiagramModal(img.src, title, badge);
      });
    });

    const zoomBtns = document.querySelectorAll('.btn-zoom');
    zoomBtns.forEach((btn) => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const container = btn.closest('.diagram-container');
        if (!container) return;
        const img = container.querySelector('.diagram-frame img');
        if (!img) return;
        const title = container.querySelector('.diagram-title')?.textContent;
        const badge = container.querySelector('.diagram-badge')?.textContent;
        window.openDiagramModal(img.src, title, badge);
      });
    });
  });

})();
