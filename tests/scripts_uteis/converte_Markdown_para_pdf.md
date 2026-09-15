
### 🚀 Principais Melhorias Implementadas

1. **Busca Recursiva na pasta `docs/`**:
   - Localiza automaticamente todos os arquivos `.md` dentro de [docs/](/docs) e suas subpastas de forma segura (tratando espaços e caracteres especiais em nomes de arquivos).
   - Preserva o arquivo `.md` original e gera o `.pdf` correspondente no mesmo diretório.

2. **Compilação Incremental Inteligente**:
   - Se o arquivo `.pdf` já existir e for mais recente que o `.md`, a conversão é **pulada**, economizando tempo de execução.
   - Pode ser forçada a qualquer momento com o parâmetro `-f` / `--force`.

3. **Extração Automática de Metadados e Formatação A4**:
   - Extrai o primeiro título (`# Título`) do markdown para definir o título oficial nos metadados do documento PDF (eliminando avisos do Pandoc).
   - Configurado para saída em formato padrão **A4** com margens de `20mm`.
   - Suprime ruídos de CSS do WeasyPrint via `--pdf-engine-opt=-q` para um terminal limpo (com flag `-v` para modo detalhado).

4. **Filtros e Alvos Flexíveis**:
   - Suporta informar subpastas específicas, padrões de arquivo (ex: `"UC_CLI_*.md"`) ou arquivos individuais diretamente via linha de comando.

5. **Espelhamento Opcional (`--mirror`)**:
   - Inclui suporte à flag `-m` / `--mirror` para replicar a árvore de PDFs gerados em `~/Documentos/AlphaEngine/docs`, mantendo o mesmo fluxo do script [converte_puml_para_svg_e_pdf.sh](/tests/scripts_uteis/converte_puml_para_svg_e_pdf.sh).

6. **Modo de Limpeza (`--clean`)**:
   - Permite remover facilmente os PDFs gerados a partir de arquivos `.md`.

---

### 📖 Como Usar

Você pode rodar o script a partir da raiz do projeto ou dentro de `tests/scripts_uteis/`:

```bash
# 1. Converter todos os arquivos .md em docs/ (recursivo e incremental):
./tests/scripts_uteis/converte_Markdown_para_pdf.sh

# 2. Converter apenas uma subpasta específica:
./tests/scripts_uteis/converte_Markdown_para_pdf.sh docs/business/use-cases/customer

# 3. Converter apenas arquivos específicos por padrão (ex: casos de uso de cliente):
./tests/scripts_uteis/converte_Markdown_para_pdf.sh "UC_CLI_*.md"

# 4. Converter um único arquivo:
./tests/scripts_uteis/converte_Markdown_para_pdf.sh docs/catalog_progress.md

# 5. Forçar a reconversão de todos os arquivos (mesmo já atualizados):
./tests/scripts_uteis/converte_Markdown_para_pdf.sh -f

# 6. Converter e espelhar os PDFs para ~/Documentos/AlphaEngine/docs:
./tests/scripts_uteis/converte_Markdown_para_pdf.sh --mirror

# 7. Limpar os arquivos .pdf gerados:
./tests/scripts_uteis/converte_Markdown_para_pdf.sh --clean
```