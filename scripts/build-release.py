#!/usr/bin/env python3
"""Build reproducible theme and companion-plugin zip archives."""
from pathlib import Path
import re
import zipfile

root = Path(__file__).resolve().parents[1]
version_match = re.search(r"define\( 'ZC_VERSION', '([^']+)' \);", (root / "functions.php").read_text())
if not version_match:
    raise SystemExit("ZC_VERSION not found")
version = version_match.group(1)
out = root / ".release"
out.mkdir(exist_ok=True)

skip_dirs = {".git", ".github", ".arena", ".wp-env", ".release", "node_modules", "vendor", "tests", "__pycache__"}
skip_files = {
    "composer.json", "package.json", "phpcs.xml.dist", "phpstan.neon.dist", "phpunit.xml.dist",
    ".wp-env.json", ".gitignore", ".distignore",
}

def add_tree(archive: zipfile.ZipFile, source: Path, prefix: str, release_theme: bool = False) -> None:
    for path in sorted(source.rglob("*")):
        rel = path.relative_to(source)
        if any(part in skip_dirs for part in rel.parts) or path.is_dir():
            continue
        if release_theme and (rel.as_posix() in skip_files or rel.as_posix().startswith("scripts/build-release") or rel.as_posix() == "scripts/update-pot.py"):
            continue
        info = zipfile.ZipInfo(f"{prefix}/{rel.as_posix()}", date_time=(2026, 1, 1, 0, 0, 0))
        info.compress_type = zipfile.ZIP_DEFLATED
        info.external_attr = (0o644 & 0xFFFF) << 16
        archive.writestr(info, path.read_bytes())

for old in out.glob("*.zip"):
    old.unlink()

with zipfile.ZipFile(out / f"zarincode-{version}.zip", "w", compresslevel=9) as zf:
    add_tree(zf, root, "zarincode", release_theme=True)
with zipfile.ZipFile(out / f"zarincode-core-{version}.zip", "w", compresslevel=9) as zf:
    add_tree(zf, root / "companion-plugin" / "zarincode-core", "zarincode-core")

for path in sorted(out.glob("*.zip")):
    print(f"Built: {path} ({path.stat().st_size:,} bytes)")
