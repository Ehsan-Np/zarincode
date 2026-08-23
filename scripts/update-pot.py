#!/usr/bin/env python3
"""Append gettext literals missing from languages/zarincode.pot."""
from pathlib import Path
import json
import re

root = Path(__file__).resolve().parents[1]
pot_path = root / "languages" / "zarincode.pot"
gettext = r"(?:__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e)"
pattern = re.compile(
    rf"(?<![A-Za-z0-9_]){gettext}\s*\(\s*('(?:\\.|[^'\\])*'|\"(?:\\.|[^\"\\])*\")\s*,\s*['\"]zarincode['\"]",
    re.S,
)

def php_string(raw: str) -> str:
    quote, body = raw[0], raw[1:-1]
    if quote == "'":
        return body.replace("\\'", "'").replace("\\\\", "\\")
    replacements = {r"\n": "\n", r"\r": "\r", r"\t": "\t", r'\"': '"', r"\\": "\\", r"\$": "$"}
    for old, new in replacements.items():
        body = body.replace(old, new)
    return body

def existing_msgids(content: str) -> set[str]:
    values: set[str] = set()
    lines = content.splitlines()
    i = 0
    while i < len(lines):
        if lines[i].startswith("msgid "):
            chunks = [lines[i][6:].strip()]
            i += 1
            while i < len(lines) and lines[i].startswith('"'):
                chunks.append(lines[i].strip())
                i += 1
            try:
                values.add("".join(json.loads(chunk) for chunk in chunks))
            except (json.JSONDecodeError, TypeError):
                pass
            continue
        i += 1
    return values

content = pot_path.read_text()
known = existing_msgids(content)
found: dict[str, set[str]] = {}
for path in sorted(root.rglob("*.php")):
    if any(part in {"vendor", "node_modules", ".git", ".arena"} for part in path.parts):
        continue
    source = path.read_text(errors="ignore")
    for match in pattern.finditer(source):
        text = php_string(match.group(1))
        if not text or text in known:
            continue
        line = source.count("\n", 0, match.start()) + 1
        found.setdefault(text, set()).add(f"{path.relative_to(root).as_posix()}:{line}")

if found:
    blocks = ["", "# Added by scripts/update-pot.py"]
    for text in sorted(found):
        blocks.append("#: " + " ".join(sorted(found[text])))
        blocks.append("msgid " + json.dumps(text, ensure_ascii=False))
        blocks.append('msgstr ""')
        blocks.append("")
    content = content.rstrip() + "\n" + "\n".join(blocks) + "\n"

content = re.sub(r"Project-Id-Version: Zarincode [^\\]+", "Project-Id-Version: Zarincode 3.36.0", content, count=1)
pot_path.write_text(content)
print(f"Added {len(found)} missing translation strings")
