#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
puml2mermaid.py
Converte diagramas PlantUML (.puml / .plantuml) para o formato Mermaid (.mmd).

Suporta:
- Detecção e conversão de diagramas de sequência (Sequence Diagrams)
- Conversão de elementos (actor, boundary, control, database, queue, cloud, entity, collections, participant)
- Conversão de caixas/agrupamentos (box ... end box -> rect rgb(...) ... end)
  com identificação dos participantes da caixa para ancoragem válida da nota de título
- Divisores e seções (== ... ==) ancorados nos participantes para sintaxe válida Mermaid
- Ativação/desativação (activate, deactivate)
- Notas de negócio (note over / left of / right of) com escape de quebras e ponto-e-vírgula (#59;)
- Estruturas de controle (alt, else, opt, loop, par, critical)
- Suporte a caminhos recursivos (diretórios ou arquivos únicos) via CLI
"""

import os
import sys
import re
import argparse


def hex_to_rgb(hex_str):
    """Converte código hex (#RRGGBB ou #RGB) para string rgb(r, g, b) suportada por Mermaid rect."""
    hex_str = hex_str.lstrip("#").strip()
    if len(hex_str) == 3:
        hex_str = "".join([c * 2 for c in hex_str])
    if len(hex_str) == 6:
        try:
            r = int(hex_str[0:2], 16)
            g = int(hex_str[2:4], 16)
            b = int(hex_str[4:6], 16)
            return f"rgb({r}, {g}, {b})"
        except ValueError:
            pass
    return "rgb(245, 247, 250)"


def escapar_caracteres_mermaid(texto):
    """Escapa caracteres problemáticos para o parser do Mermaid."""
    # Mermaid trata ponto-e-vírgula não escapado como terminador de comando
    texto = texto.replace(";", "#59;")
    # Converte quebras de linha literais \n em tags <br/>
    texto = texto.replace(r"\n", "<br/>").replace("\n", "<br/>")
    return texto


PARTICIPANT_TYPES = r"(?:actor|boundary|control|database|queue|cloud|participant|entity|collections)"


def extrair_todos_participantes(linhas):
    """Faz uma primeira passagem para coletar a lista de alias de todos os participantes/atores."""
    participantes = []
    for linha in linhas:
        l = linha.strip()
        if not l or l.startswith("'") or l.startswith("/'"):
            continue

        # actor "Nome" as Alias ou boundary/control/database/queue/participant "Nome" as Alias
        m_named = re.match(
            rf"^{PARTICIPANT_TYPES}\s+\"[^\"]+\"\s+as\s+([\w\.\-]+)",
            l,
        )
        if m_named:
            participantes.append(m_named.group(1))
            continue

        # actor Alias ou participant Alias (sem aspas)
        m_simple = re.match(
            rf"^{PARTICIPANT_TYPES}\s+([\w\.\-]+)(?:\s+as\s+([\w\.\-]+))?",
            l,
        )
        if m_simple:
            alias = m_simple.group(2) if m_simple.group(2) else m_simple.group(1)
            participantes.append(alias)
            continue

    return participantes


def coletar_participantes_da_caixa(linhas, start_idx):
    """Coleta os alias dos participantes contidos em um bloco box até o correspondente end box."""
    box_participants = []
    for i in range(start_idx + 1, len(linhas)):
        l = linhas[i].strip()
        if l == "end box" or l == "end":
            break
        m_named = re.match(
            rf"^{PARTICIPANT_TYPES}\s+\"[^\"]+\"\s+as\s+([\w\.\-]+)",
            l,
        )
        if m_named:
            box_participants.append(m_named.group(1))
            continue
        m_simple = re.match(
            rf"^{PARTICIPANT_TYPES}\s+([\w\.\-]+)(?:\s+as\s+([\w\.\-]+))?",
            l,
        )
        if m_simple:
            alias = m_simple.group(2) if m_simple.group(2) else m_simple.group(1)
            box_participants.append(alias)
            continue
    return box_participants


def traduzir_sequence_diagram(linhas):
    """Traduz o conteúdo de um diagrama de sequência PlantUML para Mermaid sequenceDiagram."""
    todos_participantes = extrair_todos_participantes(linhas)
    primeiro_participante = todos_participantes[0] if todos_participantes else "A"
    ultimo_participante = todos_participantes[-1] if todos_participantes else primeiro_participante

    saida = ["sequenceDiagram"]
    in_box = False
    bloco_stack = []

    for idx, linha in enumerate(linhas):
        l = linha.strip()

        # Ignora linhas vazias ou comentários PlantUML
        if not l or l.startswith("'") or l.startswith("/'"):
            continue

        # Ignora skinparam, header, title, style, includes, etc.
        if (
            l.startswith("skinparam")
            or l.startswith("header")
            or l.startswith("title")
            or l.startswith("<style>")
            or l.startswith("</style>")
            or l.startswith("!")
        ):
            continue

        # autonumber
        if l.startswith("autonumber"):
            saida.append("    autonumber")
            continue

        # Box com ou sem cor: box "Nome" #HEX
        if l.startswith("box"):
            m = re.match(r'^box(?:\s+"([^"]+)")?(?:\s+(#[A-Fa-f0-9]{3,8}))?', l)
            box_title = m.group(1) if m and m.group(1) else ""
            box_color = m.group(2) if m and m.group(2) else ""
            rgb_color = hex_to_rgb(box_color) if box_color else "rgb(245, 247, 250)"

            box_parts = coletar_participantes_da_caixa(linhas, idx)
            saida.append(f"    rect {rgb_color}")

            # Ancoragem válida da nota de cabeçalho do box
            if box_title:
                title_escaped = escapar_caracteres_mermaid(box_title)
                if len(box_parts) >= 2:
                    saida.append(f"    note over {box_parts[0]}, {box_parts[-1]}: {title_escaped}")
                elif len(box_parts) == 1:
                    saida.append(f"    note over {box_parts[0]}: {title_escaped}")
                else:
                    saida.append(f"    note over {primeiro_participante}: {title_escaped}")

            in_box = True
            continue

        if l == "end box" or (in_box and l == "end"):
            saida.append("    end")
            in_box = False
            continue

        # Atores: actor "Nome" as Alias ou actor Alias
        match_actor = re.match(r'^actor\s+"([^"]+)"\s+as\s+([\w\.\-]+)', l)
        if match_actor:
            nome, alias = match_actor.groups()
            nome_limpo = nome.replace(r"\n", " ").replace("\n", " ")
            saida.append(f"    actor {alias} as {nome_limpo}")
            continue

        match_actor_simple = re.match(r'^actor\s+([\w\.\-]+)(?:\s+as\s+([\w\.\-]+))?', l)
        if match_actor_simple:
            name1 = match_actor_simple.group(1)
            name2 = match_actor_simple.group(2)
            if name2:
                saida.append(f"    actor {name2} as {name1}")
            else:
                saida.append(f"    actor {name1}")
            continue

        # Boundary, Control, Database, Queue, Cloud, Participant -> Mapeados para participant no Mermaid
        match_named_part = re.match(
            rf"^{PARTICIPANT_TYPES}\s+\"([^\"]+)\"\s+as\s+([\w\.\-]+)",
            l,
        )
        if match_named_part:
            nome, alias = match_named_part.groups()
            nome_limpo = nome.replace(r"\n", " ").replace("\n", " ")
            saida.append(f"    participant {alias} as {nome_limpo}")
            continue

        match_simple_part = re.match(
            rf"^{PARTICIPANT_TYPES}\s+([\w\.\-]+)(?:\s+as\s+([\w\.\-]+))?",
            l,
        )
        if match_simple_part:
            name1 = match_simple_part.group(1)
            name2 = match_simple_part.group(2)
            if name2:
                saida.append(f"    participant {name2} as {name1}")
            else:
                saida.append(f"    participant {name1}")
            continue

        # Divisores de fase / seções: == Título ==
        match_divider = re.match(r"^==\s*(.*?)\s*==$", l)
        if match_divider:
            div_title = match_divider.group(1)
            if div_title:
                div_title_escaped = escapar_caracteres_mermaid(div_title)
                # Ancoragem válida sobre todos os participantes (ou primeiro participante)
                if primeiro_participante != ultimo_participante:
                    saida.append(
                        f"    note over {primeiro_participante}, {ultimo_participante}: {div_title_escaped}"
                    )
                else:
                    saida.append(f"    note over {primeiro_participante}: {div_title_escaped}")
            continue

        # Ativações e desativações
        match_act = re.match(r"^activate\s+([\w\.\-]+)", l)
        if match_act:
            saida.append(f"    activate {match_act.group(1)}")
            continue

        match_deact = re.match(r"^deactivate\s+([\w\.\-]+)", l)
        if match_deact:
            saida.append(f"    deactivate {match_deact.group(1)}")
            continue

        # Notas: note over A, B #HEX: Texto | note left of A: Texto | note right of A: Texto
        match_note = re.match(
            r"^note\s+(over|left of|right of)\s+([\w\.\-,\s]+?)(?:\s+#[A-Fa-f0-9]{3,8})?\s*:\s*(.*)$",
            l,
        )
        if match_note:
            pos, targets, note_text = match_note.groups()
            targets_clean = ", ".join([t.strip() for t in targets.split(",") if t.strip()])
            note_escaped = escapar_caracteres_mermaid(note_text)
            saida.append(f"    note {pos} {targets_clean}: {note_escaped}")
            continue

        # Início de bloco de controle
        match_block_open = re.match(r"^(group|alt|opt|loop|par|critical)(?:\s+(.*))?$", l)
        if match_block_open:
            kw = match_block_open.group(1)
            label = match_block_open.group(2) or ""
            label_escaped = escapar_caracteres_mermaid(label) if label else ""

            # PlantUML 'group' mapeia para Mermaid 'critical'
            mermaid_kw = "critical" if kw == "group" else kw
            bloco_stack.append(mermaid_kw)

            if label_escaped:
                saida.append(f"    {mermaid_kw} {label_escaped}")
            else:
                saida.append(f"    {mermaid_kw}")
            continue

        # Ramo alternativo: else
        match_else = re.match(r"^else(?:\s+(.*))?$", l)
        if match_else:
            label = match_else.group(1) or ""
            # Remove código hex se presente em else #HEX [Label]
            label = re.sub(r"^#[A-Fa-f0-9]{3,8}\s*", "", label).strip()
            label_escaped = escapar_caracteres_mermaid(label) if label else ""

            # Se o bloco atual na pilha for 'critical', o ramo secundário no Mermaid é 'option'
            current_block = bloco_stack[-1] if bloco_stack else "alt"
            kw = "option" if current_block == "critical" else "else"

            if label_escaped:
                saida.append(f"    {kw} {label_escaped}")
            else:
                saida.append(f"    {kw}")
            continue

        if l == "end":
            if bloco_stack:
                bloco_stack.pop()
            saida.append("    end")
            continue

        # Mensagens / Setas entre participantes
        # Exemplo: A -> B : Mensagem; ou A --> B : Mensagem
        match_msg = re.match(r"^([\w\.\-]+)\s*(-->>|-->|->>|->|-x|--x)\s*([\w\.\-]+)\s*:\s*(.*)$", l)
        if match_msg:
            origem_actor, arrow, destino_actor, msg_text = match_msg.groups()
            # Normaliza tipo de seta para Mermaid
            if "-->" in arrow or "-->>" in arrow:
                mermaid_arrow = "-->>"
            elif "-x" in arrow or "--x" in arrow:
                mermaid_arrow = "-x"
            else:
                mermaid_arrow = "->>"

            msg_escaped = escapar_caracteres_mermaid(msg_text)
            saida.append(f"    {origem_actor} {mermaid_arrow} {destino_actor}: {msg_escaped}")
            continue

        # Linha genérica
        l_escaped = escapar_caracteres_mermaid(l)
        saida.append(f"    {l_escaped}")

    return "\n".join(saida)


def traduzir_er_diagram(conteudo_limpo):
    """Converte diagramas de Entidade-Relacionamento do PlantUML para Mermaid erDiagram."""
    linhas_saida = ["erDiagram"]

    # 1. Extração dos Relacionamentos
    # Ex: Product "1" ||-right-|{ ProductDescription : "described by"
    # ou: Zone "1" ||-[#0000FF]right-|{ City : "contains cities"
    rel_pattern = re.compile(
        r'^([\w\.\-]+)\s*(?:\"[^\"]*\")?\s*'
        r'(\|o|\|\||\}o|\|\{|o\|)\-+(?:\[[^\]]+\])?(?:up|down|left|right)?\-+(\|o|\|\||\}o|\|\{|o\|)\s*'
        r'(?:\"[^\"]*\")?\s*([\w\.\-]+)\s*:\s*\"([^\"]*)\"',
        re.MULTILINE
    )

    card_map = {
        "||": "||",
        "|o": "|o",
        "o|": "o|",
        "|{": "|{",
        "}o": "}o",
    }

    relacionamentos = []
    for match in rel_pattern.finditer(conteudo_limpo):
        ent1 = match.group(1).strip()
        c1 = card_map.get(match.group(2).strip(), match.group(2).strip())
        c2 = card_map.get(match.group(3).strip(), match.group(3).strip())
        ent2 = match.group(4).strip()
        label = match.group(5).strip().replace('"', '')
        relacionamentos.append(f'    {ent1} {c1}--{c2} {ent2} : "{label}"')

    if relacionamentos:
        linhas_saida.extend(relacionamentos)
        linhas_saida.append("")

    # 2. Extração das Entidades e Atributos
    entity_regex = re.compile(r'entity\s+([\w\.\-]+)(?:\s+#[0-9a-fA-F]+)?\s*\{([^}]+)\}', re.MULTILINE)
    for match in entity_regex.finditer(conteudo_limpo):
        entity_name = match.group(1).strip()
        body = match.group(2)
        linhas_saida.append(f"    {entity_name} {{")

        for raw_line in body.splitlines():
            line = raw_line.strip()
            if not line or line == "--" or line.startswith("'"):
                continue

            # Ex: * id : BIGINT <<PK>>
            # ou: * product_id : int <<PK, FK>>
            # ou: * name : varchar(255) <<FULLTEXT idx_ft_product_search>>
            # ou: * model : varchar(64)
            attr_match = re.match(r'^\*?\s*([\w\.\-]+)\s*:\s*([^<]+?)(?:\s*<<([^>]+)>>)?$', line)
            if attr_match:
                attr_name = attr_match.group(1).strip()
                attr_type = attr_match.group(2).strip()
                modifier_raw = attr_match.group(3)

                keys = []
                comment = ""
                if modifier_raw:
                    parts = [p.strip() for p in modifier_raw.split(",")]
                    non_keys = []
                    for p in parts:
                        p_upper = p.upper()
                        if "PK" in p_upper:
                            if "PK" not in keys:
                                keys.append("PK")
                        if "FK" in p_upper:
                            if "FK" not in keys:
                                keys.append("FK")
                        if "PK" not in p_upper and "FK" not in p_upper:
                            non_keys.append(p)
                    if non_keys:
                        comment = f' "{", ".join(non_keys)}"'

                key_str = f" {', '.join(keys)}" if keys else ""
                linhas_saida.append(f"        {attr_type} {attr_name}{key_str}{comment}")
            else:
                linhas_saida.append(f"        {line}")

        linhas_saida.append("    }")
        linhas_saida.append("")

    return "\n".join(linhas_saida).rstrip() + "\n"


def traduzir_plantuml_para_mermaid(conteudo_puml):
    """Aplica regras de conversão de sintaxe de PlantUML para Mermaid."""
    # Remove blocos <style>...</style> inteiros antes de processar
    conteudo_sem_style = re.sub(r"<style>.*?</style>", "", conteudo_puml, flags=re.DOTALL)

    # Remove tags @startuml e @enduml
    conteudo_limpo = re.sub(r"^[ \t]*@startuml(?:[ \t]+.*)?$", "", conteudo_sem_style, flags=re.MULTILINE)
    conteudo_limpo = re.sub(r"^[ \t]*@enduml(?:[ \t]+.*)?$", "", conteudo_limpo, flags=re.MULTILINE)

    linhas = conteudo_limpo.splitlines()

    # Detecta se é diagrama de Entidade-Relacionamento
    is_er = any(re.search(r"\bentity\s+[\w\.\-]+", linha) for linha in linhas)
    if is_er:
        return traduzir_er_diagram(conteudo_limpo)

    # Detecta se é diagrama de sequência
    is_sequence = any(
        re.search(r"->|-->|actor\s|participant\s|boundary\s|control\s|database\s|queue\s", linha)
        for linha in linhas
    )

    if is_sequence:
        return traduzir_sequence_diagram(linhas)

    # Caso geral / fallback
    linhas_saida = ["flowchart TD"]
    for linha in linhas:
        l = linha.strip()
        if not l or l.startswith("'"):
            continue
        linhas_saida.append(f"    {l}")

    return "\n".join(linhas_saida)


def coletar_arquivos_puml(origem):
    """Coleta todos os arquivos .puml ou .plantuml recursivamente ou arquivo único."""
    arquivos = []
    if os.path.isfile(origem):
        if origem.endswith((".puml", ".plantuml")):
            arquivos.append(origem)
    elif os.path.isdir(origem):
        for root, _, filenames in os.walk(origem):
            for fn in sorted(filenames):
                if fn.endswith((".puml", ".plantuml")) and not fn.startswith("_"):
                    arquivos.append(os.path.join(root, fn))
    return arquivos


def converter_lote(pasta_origem, pasta_destino=None):
    """Varre a origem e converte os arquivos PlantUML encontrados."""
    arquivos = coletar_arquivos_puml(pasta_origem)

    if not arquivos:
        print(f"Nenhum arquivo PlantUML encontrado em: {pasta_origem}")
        return

    print(f"Processando {len(arquivos)} diagrama(s)...")

    for caminho_origem in arquivos:
        base_name = os.path.splitext(os.path.basename(caminho_origem))[0]
        nome_saida = base_name + ".mmd"

        if pasta_destino:
            os.makedirs(pasta_destino, exist_ok=True)
            caminho_destino = os.path.join(pasta_destino, nome_saida)
        else:
            # Salva no mesmo diretório do arquivo .puml correspondente
            caminho_destino = os.path.join(os.path.dirname(caminho_origem), nome_saida)

        try:
            with open(caminho_origem, "r", encoding="utf-8", errors="replace") as f:
                conteudo_puml = f.read()

            conteudo_mermaid = traduzir_plantuml_para_mermaid(conteudo_puml)

            with open(caminho_destino, "w", encoding="utf-8") as f:
                f.write(conteudo_mermaid + "\n")

            print(f"✓ Convertido com sucesso: {caminho_origem} -> {caminho_destino}")
        except Exception as e:
            print(f"✕ Erro ao converter {caminho_origem}: {str(e)}", file=sys.stderr)


# --- Execução CLI ---
if __name__ == "__main__":
    parser = argparse.ArgumentParser(
        description="Converte diagramas PlantUML (*.puml) em diagramas Mermaid (*.mmd)."
    )
    parser.add_argument(
        "origem",
        nargs="?",
        default="docs/workflows/sequence_diagrams",
        help="Caminho do arquivo ou diretório contendo os diagramas .puml (padrão: docs/workflows/sequence_diagrams)",
    )
    parser.add_argument(
        "-o",
        "--out",
        dest="destino",
        default=None,
        help="Diretório de saída (padrão: mesma pasta de cada .puml)",
    )

    args = parser.parse_args()

    print("Iniciando a conversão...")
    converter_lote(args.origem, args.destino)
    print("Processo concluído!")
