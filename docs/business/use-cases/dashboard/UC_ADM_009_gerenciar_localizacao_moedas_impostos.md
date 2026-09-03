# UC_ADM_009 - Gerenciar Localização, Moedas & Impostos

## 📋 Informações do Caso de Uso

| Atributo | Detalhe |
| :--- | :--- |
| **Identificador** | `UC_ADM_009` |
| **Nome** | Gerenciar Localização, Idiomas, Moedas e Classes de Impostos |
| **Módulo** | Painel Administrativo - Operações do Sistema |
| **Atores Primários** | Administrador Geral (*Admin*) |
| **Atores Secundários** | Sistema Alpha Engine |
| **Tipo** | Condução / Parametrização Fiscal & Local |
| **Frequência de Uso** | Baixa |
| **Rastreabilidade** | **RF:** [RF020](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Faturamento/NF-e e tributação), [RF023](file:///var/www/html/agsonhos/docs/requirements/functional/functional_requirements.yaml) (Gestão administrativa)<br>**RN:** [RN002](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Unidades de peso/medida para frete), [RN012](file:///var/www/html/agsonhos/docs/requirements/business_rules/business_rules.yaml) (Emissão fiscal)<br>**RNF:** [RNF003](file:///var/www/html/agsonhos/docs/requirements/non_functional/non_functional_requirements.yaml) (Conformidade tributária) |

---

## 1. 🎯 Descrição Sumária
Permite ao Administrador Geral configurar os parâmetros de localização geográfica (Países, Estados/UFs brasileiras e Zonas Geográficas para cálculo de frete), taxas de câmbio de moedas (BRL Real Brasileiro como moeda padrão), pacotes de idiomas do sistema e a matriz de Classes de Impostos e Alíquotas Tributárias (ICMS, IPI, PIS, COFINS e NCM) aplicadas na formação do preço e emissão das notas fiscais.

---

## 2. ⚡ Pré-Condições
- Administrador Geral autenticado no painel.

---

## 3. ✅ Pós-Condições
- Regras tributárias, alíquotas estaduais de ICMS e zonas geográficas atualizadas nas tabelas `tbkk_tax_rate`, `tbkk_tax_class`, `tbkk_geo_zone`, `tbkk_currency`.

---

## 4. 🚀 Gatilho (Trigger)
O Administrador acessa "Sistema > Localização > Impostos / Moedas / Zonas Geográficas".

---

## 5. 🔄 Fluxo Principal (Configurar Alíquota de ICMS Interestadual)

1. **Ator:** Acessa "Sistema > Localização > Taxas de Impostos".
2. **Ator:** Clica em "Adicionar Nova Taxa de Imposto".
3. **Sistema:** Exibe o formulário de configuração:
   - Nome da Taxa (ex: *"ICMS Padrão SP - 18%"*);
   - Alíquota Percentual (ex: `18.0000`);
   - Tipo de Cálculo: Percentual sobre o valor da mercadoria;
   - Grupo de Clientes Aplicável (Varejo e/ou Atacado);
   - Zona Geográfica (ex: *Zona Sudeste - Estado de São Paulo*).
4. **Ator:** Salva a taxa e vincula à Classe Tributária de "Materiais de Construção Básica".
5. **Sistema:** Grava as regras no banco de dados e sincroniza o motor de cálculo tributário do checkout e faturamento da NF-e (RF020).

---

## 6. 🔀 Fluxos Alternativos

- **FA01 - Gerenciamento de Unidades de Medida e Peso:**
  1. O administrador acessa "Classes de Peso" e "Classes de Medida".
  2. Define o Quilograma (kg) e o Centímetro (cm) como unidades padrão do motor de cubagem para transportadoras (RN002).

---

## 7. ⚠️ Fluxos de Exceção

- **FE01 - Tentativa de Excluir a Moeda Padrão do Sistema (BRL):**
  1. O administrador tenta deletar a moeda configurada como base de cálculo.
  2. O sistema bloqueia a exclusão e exibe: *"Não é possível excluir a moeda padrão do sistema (Real Brasileiro - BRL)."*

---

## 8. 📜 Regras de Negócio Aplicadas

- **RN002 & RN012:** Garantia da correta padronização de unidades métricas e conformidade com a legislação fiscal da SEFAZ.

---

## 9. 🖥️ Interface & Campos de Entrada/Saída

### Entradas:
- Tabelas de alíquotas fiscais, zonas geográficas, moedas e idiomas.

### Saídas:
- Matriz de cálculo tributário e regional configurada e ativa no sistema.
