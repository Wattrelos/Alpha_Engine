/**
 * ==========================================================================
 * Calculadora de Materiais de Construção (Pisos e Revestimentos) - Engine JS
 * Requisitos: RF026, RF027, RF028, RF029, RF030, RF031, RF032
 * ==========================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Elementos Principais
    const btnOpen = document.getElementById('btn-open-calculator');
    const modalBackdrop = document.getElementById('calc-modal');
    const btnClose = document.getElementById('calc-btn-close');
    const btnCancel = document.getElementById('calc-btn-cancel');
    const btnApply = document.getElementById('calc-btn-apply');

    if (!modalBackdrop) return;

    // Tabs e Seções
    const tabWall = document.getElementById('calc-tab-wall');
    const tabFloor = document.getElementById('calc-tab-floor');
    const wallHeightGroup = document.getElementById('calc-group-height');
    const openingsSection = document.getElementById('calc-section-openings');
    const metricGrossLabel = document.getElementById('calc-label-gross');

    // Inputs de Dimensões
    const inputLength = document.getElementById('calc-input-length');
    const inputWidth = document.getElementById('calc-input-width');
    const inputHeight = document.getElementById('calc-input-height');
    const inputWaste = document.getElementById('calc-input-waste');
    const inputYield = document.getElementById('calc-input-yield');
    const lossChips = document.querySelectorAll('.calc-loss-chip');

    // Contêineres de Vãos
    const openingsList = document.getElementById('calc-openings-list');
    const btnAddOpening = document.getElementById('calc-btn-add-opening');

    // Outputs
    const outGross = document.getElementById('calc-out-gross');
    const outDeductions = document.getElementById('calc-out-deductions');
    const outNet = document.getElementById('calc-out-net');
    const outTotal = document.getElementById('calc-out-total');
    const outBoxes = document.getElementById('calc-out-boxes');

    // Input de quantidade da PDP
    const pdpQtyInput = document.getElementById('input-quantity');

    let currentMode = 'wall'; // 'wall' | 'floor'

    // 2. Abertura e Fechamento do Modal
    function openModal() {
        modalBackdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
        calculate();
    }

    function closeModal() {
        modalBackdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (btnOpen) {
        btnOpen.addEventListener('click', (e) => {
            e.preventDefault();
            openModal();
        });
    }

    if (btnClose) btnClose.addEventListener('click', closeModal);
    if (btnCancel) btnCancel.addEventListener('click', closeModal);

    modalBackdrop.addEventListener('click', (e) => {
        if (e.target === modalBackdrop) closeModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modalBackdrop.classList.contains('active')) {
            closeModal();
        }
    });

    // 3. Troca de Modo (Parede vs Piso)
    function setMode(mode) {
        currentMode = mode;
        if (mode === 'wall') {
            tabWall.classList.add('active');
            tabFloor.classList.remove('active');
            if (wallHeightGroup) wallHeightGroup.style.display = 'flex';
            if (openingsSection) openingsSection.style.display = 'block';
            if (metricGrossLabel) metricGrossLabel.textContent = 'Área Parede';
        } else {
            tabFloor.classList.add('active');
            tabWall.classList.remove('active');
            if (wallHeightGroup) wallHeightGroup.style.display = 'none';
            if (openingsSection) openingsSection.style.display = 'none';
            if (metricGrossLabel) metricGrossLabel.textContent = 'Área Piso';
        }
        calculate();
    }

    if (tabWall) tabWall.addEventListener('click', () => setMode('wall'));
    if (tabFloor) tabFloor.addEventListener('click', () => setMode('floor'));

    // 4. Chips de Margem de Perda
    lossChips.forEach(chip => {
        chip.addEventListener('click', () => {
            lossChips.forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            const percent = chip.getAttribute('data-percent');
            if (inputWaste && percent) {
                inputWaste.value = percent;
                calculate();
            }
        });
    });

    if (inputWaste) {
        inputWaste.addEventListener('input', () => {
            lossChips.forEach(c => {
                c.classList.toggle('active', c.getAttribute('data-percent') === inputWaste.value);
            });
            calculate();
        });
    }

    // 5. Adicionar / Remover Aberturas
    if (btnAddOpening && openingsList) {
        btnAddOpening.addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'calc-opening-row';
            row.innerHTML = `
                <select class="calc-input calc-opening-type" style="padding-right: 8px;">
                    <option value="porta">Porta</option>
                    <option value="janela">Janela</option>
                    <option value="outro">Outro Vão</option>
                </select>
                <div class="calc-input-wrapper">
                    <input type="number" step="0.01" min="0" value="0.80" placeholder="Larg." class="calc-input calc-op-w">
                    <span class="calc-input-suffix">m</span>
                </div>
                <div class="calc-input-wrapper">
                    <input type="number" step="0.01" min="0" value="2.10" placeholder="Alt." class="calc-input calc-op-h">
                    <span class="calc-input-suffix">m</span>
                </div>
                <div class="calc-input-wrapper">
                    <input type="number" step="1" min="1" value="1" placeholder="Qtd" class="calc-input calc-op-qty">
                    <span class="calc-input-suffix">un</span>
                </div>
                <button type="button" class="calc-btn-remove-row" title="Remover vão">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            `;
            openingsList.appendChild(row);
            bindOpeningRowEvents(row);
            calculate();
        });
    }

    function bindOpeningRowEvents(row) {
        const removeBtn = row.querySelector('.calc-btn-remove-row');
        if (removeBtn) {
            removeBtn.addEventListener('click', () => {
                row.remove();
                calculate();
            });
        }
        row.querySelectorAll('input, select').forEach(el => {
            el.addEventListener('input', calculate);
            el.addEventListener('change', calculate);
        });
    }

    if (openingsList) {
        openingsList.querySelectorAll('.calc-opening-row').forEach(bindOpeningRowEvents);
    }

    // 6. Monitoramento de inputs para recálculo
    [inputLength, inputWidth, inputHeight, inputYield].forEach(input => {
        if (input) {
            input.addEventListener('input', calculate);
            input.addEventListener('change', calculate);
        }
    });

    // 7. Motor de Cálculo Matemático
    function calculate() {
        const length = parseFloat(inputLength ? inputLength.value : 0) || 0;
        const width = parseFloat(inputWidth ? inputWidth.value : 0) || 0;
        const height = parseFloat(inputHeight ? inputHeight.value : 0) || 0;
        const wastePercent = parseFloat(inputWaste ? inputWaste.value : 10) || 0;
        const yieldPerBox = parseFloat(inputYield ? inputYield.value : 1) || 1;

        const wasteMargin = Math.max(0, wastePercent) / 100;
        const safeYield = Math.max(0.0001, yieldPerBox);

        let grossArea = 0;
        let deductions = 0;
        let netArea = 0;
        let totalAreaWithWaste = 0;
        let calculatedBoxes = 0;

        if (currentMode === 'wall') {
            // RF026: (2 * length * height) + (2 * width * height)
            grossArea = (2 * length * height) + (2 * width * height);

            // RF027: Deduções de vãos
            if (openingsList) {
                openingsList.querySelectorAll('.calc-opening-row').forEach(row => {
                    const w = parseFloat(row.querySelector('.calc-op-w')?.value || 0) || 0;
                    const h = parseFloat(row.querySelector('.calc-op-h')?.value || 0) || 0;
                    const qty = parseInt(row.querySelector('.calc-op-qty')?.value || 1) || 1;
                    deductions += (w * h * qty);
                });
            }

            netArea = Math.max(0, grossArea - deductions);
            totalAreaWithWaste = netArea * (1 + wasteMargin);
        } else {
            // RF028: length * width
            grossArea = length * width;
            deductions = 0;
            netArea = grossArea;
            totalAreaWithWaste = grossArea * (1 + wasteMargin);
        }

        // RF030: CEIL(total_area / yield_per_box)
        if (totalAreaWithWaste > 0 && safeYield > 0) {
            calculatedBoxes = Math.ceil(Math.round(totalAreaWithWaste * 1000000) / 1000000 / safeYield);
        } else {
            calculatedBoxes = 0;
        }

        // Atualização da Interface
        if (outGross) outGross.textContent = `${grossArea.toFixed(2)} m²`;
        if (outDeductions) outDeductions.textContent = `${deductions.toFixed(2)} m²`;
        if (outNet) outNet.textContent = `${netArea.toFixed(2)} m²`;
        if (outTotal) outTotal.textContent = `${totalAreaWithWaste.toFixed(2)} m²`;
        if (outBoxes) outBoxes.textContent = calculatedBoxes;

        return {
            grossArea,
            deductions,
            netArea,
            totalAreaWithWaste,
            calculatedBoxes
        };
    }

    // 8. Aplicação ao Carrinho e PDP (RF031)
    if (btnApply) {
        btnApply.addEventListener('click', () => {
            const results = calculate();
            const boxes = results.calculatedBoxes;

            if (boxes <= 0) {
                alert('Por favor, informe dimensões válidas para calcular a quantidade de caixas.');
                return;
            }

            if (pdpQtyInput) {
                pdpQtyInput.value = boxes;
                
                // Feedback visual no campo da PDP
                pdpQtyInput.style.transition = 'all 0.3s ease';
                pdpQtyInput.style.borderColor = '#fc9003';
                pdpQtyInput.style.boxShadow = '0 0 0 4px rgba(252, 144, 3, 0.35)';
                
                setTimeout(() => {
                    pdpQtyInput.style.borderColor = '';
                    pdpQtyInput.style.boxShadow = '';
                }, 1800);
            }

            closeModal();
        });
    }

    // Inicialização do modo e cálculo inicial
    setMode('wall');
});
