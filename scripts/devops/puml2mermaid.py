#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
puml2mermaid.py
Converte diagramas PlantUML (.puml / .plantuml) para o formato Mermaid (.mmd).

Suporta:
- Detecção e conversão de diagramas de sequência (Sequence Diagrams)
- Conversão de elementos (actor, boundary, control, database, participant)
- Conversão de caixas/agrupamentos (box ... end box -> rect rgb(...) ... end)
- Divisores e seções (== ... ==)
- Ativação/desativação (activate, deactivate)
- Notas (note over / note left / note right)
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
    return "rgb(240, 240, 240)"


def traduzir_sequence_diagram(linhas):
    """Traduz o conteúdo de um diagrama de sequência PlantUML para Mermaid sequenceDiagram."""
    saida = ["sequenceDiagram"]
    in_box = False

    for linha in linhas:
        l = linha.strip()

        # Ignora linhas vazias ou comentários PlantUML
        if not l or l.startswith("'") or l.startswith("/'"):
            continue

        # Ignora skinparam, header, title, style, etc.
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

        # Box com ou sem cor: box "Nome" #HEX ou box "Nome"
        match_box_open = re.match(r'^box\s*(?:"([^"]+)"|([^\s#]+))?\s*(#[A-Fa-f0-9]{3,8})?', l)
        if match_box_open and not l.startswith("box "):
            pass

        if l.startswith("box"):
            m = re.match(r'^box(?:\s+"([^"]+)")?(?:\s+(#[A-Fa-f0-9]{3,8}))?', l)
            box_title = m.group(1) if m and m.group(1) else ""
            box_color = m.group(2) if m and m.group(2) else ""
            rgb_color = hex_to_rgb(box_color) if box_color else "rgb(245, 247, 250)"
            
            saida.append(f"    rect {rgb_color}")
            if box_title:
                saida.append(f"    note over: {box_title}")
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
            saida.append(f"    actor {alias} as {nome}")
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

        # Boundary, Control, Database, Participant -> Todos mapeados para participant/actor no Mermaid
        match_named_part = re.match(r'^(?:boundary|control|database|participant|entity|collections)\s+"([^"]+)"\s+as\s+([\w\.\-]+)', l)
        if match_named_part:
            nome, alias = match_named_part.groups()
            # Escapa quebras de linha em participantes
            nome_limpo = nome.replace(r"\n", " ").replace("\n", " ")
            saida.append(f"    participant {alias} as {nome_limpo}")
            continue

        match_simple_part = re.match(r'^(?:boundary|control|database|participant|entity|collections)\s+([\w\.\-]+)(?:\s+as\s+([\w\.\-]+))?', l)
        if match_simple_part:
            name1 = match_simple_part.group(1)
            name2 = match_simple_part.group(2)
            if name2:
                saida.append(f"    participant {name2} as {name1}")
            else:
                saida.append(f"    participant {name1}")
            continue

        # Divisores de fase: == Título ==
        match_divider = re.match(r"^==\s*(.*?)\s*==$", l)
        if match_divider:
            div_title = match_divider.group(1)
            if div_title:
                saida.append(f"    note over: {div_title}")
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

        # Notas: note over A, B: Texto | note left of A: Texto | note right of A: Texto
        match_note = re.match(r"^note\s+(over|left of|right of)\s+([\w\.\-,\s]+)(?:#[A-Fa-f0-9]+)?\s*:\s*(.*)$", l)
        if match_note:
            pos, targets, note_text = match_note.groups()
            targets = targets.strip()
            # Converte múltiplos alvos para formato Mermaid (A, B)
            targets_clean = ", ".join([t.strip() for t in targets.split(",") if t.strip()])
            saida.append(f"    note {pos} {targets_clean}: {note_text}")
            continue

        # Estruturas de controle: alt, else, opt, loop, par, critical
        match_block = re.match(r"^(alt|else|opt|loop|par|critical)(?:\s+(.*))?$", l)
        if match_block:
            kw = match_block.group(1)
            label = match_block.group(2) or ""
            if label:
                saida.append(f"    {kw} {label}")
            else:
                saida.append(f"    {kw}")
            continue

        if l == "end":
            saida.append("    end")
            continue

        # Mensagens / Setas
        # Trata quebras de linha com \n
        l_msg = l.replace(r"\n", "<br/>")

        # Conversão de setas PlantUML para Mermaid:
        # --> ou -->> (retorno dashed) -> -->>
        # ->> ou -> (chamada sólida síncrona) -> ->>
        # -x ou --x (falha/perda) -> -x
        l_msg = re.sub(r"\s+-->\s+", " -->> ", l_msg)
        l_msg = re.sub(r"\s+->\s+", " ->> ", l_msg)

        saida.append(f"    {l_msg}")

    return "\n".join(saida)


def traduzir_plantuml_para_mermaid(conteudo_puml):
    """Aplica regras de conversão de sintaxe de PlantUML para Mermaid."""
    # Remove blocos <style>...</style> inteiros antes de processar
    conteudo_sem_style = re.sub(r"<style>.*?</style>", "", conteudo_puml, flags=re.DOTALL)

    # Remove tags @startuml e @enduml
    conteudo_limpo = re.sub(r"^[ \t]*@startuml(?:[ \t]+.*)?$", "", conteudo_sem_style, flags=re.MULTILINE)
    conteudo_limpo = re.sub(r"^[ \t]*@enduml(?:[ \t]+.*)?$", "", conteudo_limpo, flags=re.MULTILINE)

    linhas = conteudo_limpo.splitlines()

    # Detecta se é diagrama de sequência
    # Se contém setas de sequência, participant, actor, etc.
    is_sequence = any(
        re.search(r"->|-->|actor\s|participant\s|boundary\s|control\s|database\s", linha)
        for linha in linhas
    )

    if is_sequence:
        return traduzir_sequence_diagram(linhas)

    # Caso geral / fallback: gera flowchart ou diagrama básico
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
