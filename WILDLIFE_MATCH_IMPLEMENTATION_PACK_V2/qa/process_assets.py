from pathlib import Path
from PIL import Image, ImageEnhance, ImageOps

ROOT = Path(__file__).resolve().parents[1]
GEN = Path(r"C:\Users\User\.codex\generated_images\01a047df-40c1-7c43-8b54-0f0973f6fdd9")
HOME = ROOT / "starter" / "img" / "home"
SERVICES = ROOT / "starter" / "img" / "services"

def save(source: Path, target: Path, size=(1400, 1000), mirror=False, warmth=1.0):
    image = Image.open(source).convert("RGB")
    if mirror:
        image = ImageOps.mirror(image)
    image = ImageOps.fit(image, size, method=Image.Resampling.LANCZOS)
    if warmth != 1.0:
        image = ImageEnhance.Color(image).enhance(warmth)
    target.parent.mkdir(parents=True, exist_ok=True)
    image.save(target, "WEBP", quality=80, method=6)

new_home = {
    "exec-e1de3881-fbf5-41b5-aef6-879bcbc4d19f.png": "prepare-table.webp",
    "exec-1342a997-ea33-47ee-afb4-acb562b7e2d1.png": "prepare-safe-photo.webp",
    "exec-a9397982-6dc5-4211-b14e-5290c9df0e4a.png": "about-conversation.webp",
    "exec-b2d28d18-68a0-44fa-b19f-66914cab3b4c.png": "request-garden.webp",
}
for source, target in new_home.items():
    save(GEN / source, HOME / target, (1400, 1000) if "safe" not in target and "request" not in target else (1000, 1400))

save(HOME / "hero-garden.webp", HOME / "matters-wildlife.webp", (1200, 900), mirror=True, warmth=1.07)
save(HOME / "service-attic.webp", HOME / "matters-attic.webp", (1200, 900), mirror=True, warmth=.92)
save(HOME / "service-sealing.webp", HOME / "matters-edge.webp", (1200, 800), mirror=True, warmth=1.05)

page_assets = {
    "wildlife-hero.webp": ("service-wildlife.webp", (900, 1200), False, 1.0),
    "wildlife-exterior.webp": ("signs-exterior.webp", (1400, 900), True, .96),
    "wildlife-squirrel.webp": ("hero-garden.webp", (1000, 700), True, 1.1),
    "wildlife-roof.webp": ("hero-roof.webp", (1000, 700), False, .95),
    "attic-hero.webp": ("service-attic.webp", (1000, 1200), True, 1.02),
    "attic-space.webp": ("matters-attic.webp", (1400, 900), False, .9),
    "attic-material.webp": ("prepare-table.webp", (1000, 700), False, .9),
    "attic-exterior.webp": ("hero-wall.webp", (1000, 700), True, 1.03),
    "sealing-hero.webp": ("service-sealing.webp", (1000, 1200), True, 1.0),
    "sealing-roof.webp": ("signs-vent.webp", (1400, 900), False, .95),
    "sealing-soffit.webp": ("signs-nesting.webp", (1000, 700), True, .88),
    "sealing-exterior.webp": ("signs-exterior.webp", (1000, 700), False, 1.08),
}
for target, (source, size, mirror, warmth) in page_assets.items():
    save(HOME / source, SERVICES / target, size, mirror, warmth)

print(f"Prepared {len(new_home) + 3 + len(page_assets)} optimized WebP assets")
