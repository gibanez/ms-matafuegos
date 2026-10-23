"""Genera la imagen de vista previa (Open Graph) del sitio.

Se usa al compartir el link por WhatsApp, redes o clientes de correo. El
archivo sale en site/public/brand/og-image.png, que Vite copia a
public/brand/og-image.png al compilar el sitio.

Uso: python scripts/generate-og-image.py
"""

from pathlib import Path

from PIL import Image, ImageChops, ImageDraw, ImageFilter, ImageFont

WIDTH = 1200
HEIGHT = 630
BACKGROUND = (11, 15, 20)
BRAND = (200, 25, 35)

ROOT = Path(__file__).resolve().parent.parent
LOGO = ROOT / 'site' / 'src' / 'assets' / 'ms-matafuegos-light.png'
OUTPUT = ROOT / 'site' / 'public' / 'brand' / 'og-image.png'

FONT_BOLD = Path('C:/Windows/Fonts/seguisb.ttf')
FONT_REGULAR = Path('C:/Windows/Fonts/segoeui.ttf')

HEADLINE = 'Recarga, venta y mantenimiento de matafuegos'
SUBLINE = 'Servicio a domicilio en San Miguel de Tucumán'


def load_font(path: Path, size: int) -> ImageFont.FreeTypeFont:
    if path.exists():
        return ImageFont.truetype(str(path), size)

    return ImageFont.load_default(size)


def build_glow() -> Image.Image:
    glow = Image.new('RGB', (WIDTH, HEIGHT), (0, 0, 0))
    draw = ImageDraw.Draw(glow)
    draw.ellipse((720, -260, 1520, 420), fill=(140, 12, 24))
    draw.ellipse((-260, 380, 380, 900), fill=(50, 12, 20))

    return glow.filter(ImageFilter.GaussianBlur(190))


def main() -> None:
    canvas = Image.new('RGB', (WIDTH, HEIGHT), BACKGROUND)
    canvas = ImageChops.add(canvas, build_glow())

    logo = Image.open(LOGO).convert('RGBA')
    logo_width = 620
    logo_height = round(logo.height * logo_width / logo.width)
    logo = logo.resize((logo_width, logo_height), Image.LANCZOS)
    canvas.paste(logo, ((WIDTH - logo_width) // 2, 110), logo)

    draw = ImageDraw.Draw(canvas)
    draw.text(
        (WIDTH / 2, 468),
        HEADLINE,
        font=load_font(FONT_BOLD, 40),
        fill=(236, 239, 243),
        anchor='mm',
    )
    draw.text(
        (WIDTH / 2, 518),
        SUBLINE,
        font=load_font(FONT_REGULAR, 30),
        fill=(150, 160, 172),
        anchor='mm',
    )
    draw.rectangle((0, HEIGHT - 12, WIDTH, HEIGHT), fill=BRAND)

    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    canvas.save(OUTPUT, 'PNG', optimize=True)
    print(f'{OUTPUT} ({OUTPUT.stat().st_size} bytes, {WIDTH}x{HEIGHT})')


if __name__ == '__main__':
    main()
