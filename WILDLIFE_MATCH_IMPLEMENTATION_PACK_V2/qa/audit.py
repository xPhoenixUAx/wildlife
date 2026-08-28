from html.parser import HTMLParser
from pathlib import Path
import re
import sys

PACKAGE = Path(__file__).resolve().parents[1]
ROOT = Path(__file__).resolve().parents[2]
SITE = ROOT
PAGES = [
    "index.html", "wildlife-removal.html", "attic-cleanup-restoration.html",
    "entry-point-sealing-prevention.html", "privacy.html", "terms.html", "cookie-policy.html"
]
REQUIRED = PAGES + [
    "handler.php", "favicon.svg", "README.md", "IMAGE_CREDITS.md", "config/site-config.js",
    "css/base.css", "css/home.css", "css/service.css", "css/legal.css", "css/animations.css",
    "js/main.js", "js/animations.js"
]

class AuditParser(HTMLParser):
    def __init__(self):
        super().__init__(); self.h1 = 0; self.ids = set(); self.refs = []; self.anchors = []; self.forms = []; self.inputs = []
    def handle_starttag(self, tag, attrs):
        values = dict(attrs)
        if tag == "h1": self.h1 += 1
        if values.get("id"): self.ids.add(values["id"])
        if tag in {"a", "link"} and values.get("href"): self.refs.append((tag, values["href"])); self.anchors.append(values.get("href")) if tag == "a" else None
        if tag in {"img", "script"} and values.get("src"): self.refs.append((tag, values["src"]))
        if tag == "form": self.forms.append(values)
        if tag in {"input", "select", "textarea"}: self.inputs.append((tag, values))

errors = []
for item in REQUIRED:
    if not (SITE / item).is_file(): errors.append(f"missing required file: {item}")
for folder in ["img/home", "img/services"]:
    if not (SITE / folder).is_dir(): errors.append(f"missing image folder: {folder}")

parsed = {}
for page_name in PAGES:
    parser = AuditParser(); parser.feed((SITE / page_name).read_text(encoding="utf-8")); parsed[page_name] = parser
    if parser.h1 != 1: errors.append(f"{page_name}: expected one H1, found {parser.h1}")
    for tag, ref in parser.refs:
        if ref.startswith(("http://", "https://", "mailto:", "#", "data:")): continue
        path, _, fragment = ref.partition("#")
        target = (SITE / path.split("?")[0]).resolve()
        if not target.is_file(): errors.append(f"{page_name}: unresolved {tag} reference {ref}")
        elif fragment and target.suffix == ".html":
            other = parsed.get(path)
            if other is None:
                other = AuditParser(); other.feed(target.read_text(encoding="utf-8"))
            if fragment not in other.ids: errors.append(f"{page_name}: missing target #{fragment} in {path}")
    for href in parser.anchors:
        if href == "": errors.append(f"{page_name}: empty anchor")

for page_name, parser in parsed.items():
    for href in [value for tag, value in parser.refs if tag == "a" and value.startswith("#")]:
        if href[1:] not in parser.ids: errors.append(f"{page_name}: missing same-page target {href}")

form_pages = ["index.html"]
for page_name in form_pages:
    parser = parsed[page_name]
    if len(parser.forms) != 1: errors.append(f"{page_name}: expected one form")
    names = {attrs.get("name") for _, attrs in parser.inputs}
    for required in {"name", "email", "zip", "service", "message", "photos[]", "consent", "website", "csrf_token"}:
        if required not in names: errors.append(f"{page_name}: missing field {required}")
    if any(attrs.get("type") == "tel" or attrs.get("name") == "phone" for _, attrs in parser.inputs): errors.append(f"{page_name}: phone field found")

service_pages = ["wildlife-removal.html", "attic-cleanup-restoration.html", "entry-point-sealing-prevention.html"]
for page_name in service_pages:
    parser = parsed[page_name]
    source = (SITE / page_name).read_text(encoding="utf-8", errors="replace")
    if parser.forms: errors.append(f"{page_name}: service page must not contain a form")
    if 'class="service-cta"' not in source: errors.append(f"{page_name}: missing service CTA")

text_files = [
    p for p in SITE.rglob("*")
    if p.is_file()
    and PACKAGE not in p.parents
    and p.suffix.lower() in {".html", ".css", ".js", ".php", ".md", ".svg"}
]
all_text = "\n".join(p.read_text(encoding="utf-8", errors="replace") for p in text_files)
for forbidden in ["tel:", "lorem ipsum", "TODO", "INSTRUCTION:", "our technicians", "our wildlife experts", "we remove", "we inspect", "we seal", "trusted providers", "guaranteed match", "free estimate", "same-day service"]:
    if forbidden.lower() in all_text.lower(): errors.append(f"forbidden text found: {forbidden}")
if re.search(r"(?:\+?1[-. ]?)?\(?\d{3}\)?[-. ]\d{3}[-. ]\d{4}", all_text): errors.append("phone-number pattern found")
if "gradient" in all_text.lower(): errors.append("gradient found")

print(f"Audited {len(PAGES)} pages, {len(text_files)} text files and {len(list((SITE / 'img').rglob('*.webp')))} WebP images")
if errors:
    print("FAIL")
    print("\n".join(f"- {error}" for error in errors))
    sys.exit(1)
print("PASS")
