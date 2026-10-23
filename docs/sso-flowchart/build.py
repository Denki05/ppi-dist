from PIL import Image, ImageDraw, ImageFont, ImageOps
import os

BASE = os.path.dirname(os.path.abspath(__file__))

# A3 portrait at 150 dpi: 297 x 420 mm.
PAGE_W, PAGE_H = 1754, 2480
MARGIN = 34
FOOTER = 56

ITEMS = [
    ("render-a1.png", "A1-Pendaftaran.png", "A1. Cara Pendaftaran"),
    ("render-a2.png", "A2-Verifikasi.png", "A2. Verifikasi Administrator/Developer"),
    ("render-a3.png", "A3-Login.png", "A3. Cara Pengguna Login"),
]


def crop_content(filename, pad=28):
    image = Image.open(os.path.join(BASE, filename)).convert("RGB")
    mask = image.convert("L").point(lambda value: 0 if value > 245 else 255)
    box = mask.getbbox()
    if not box:
        return image
    box = (
        max(0, box[0] - pad),
        max(0, box[1] - pad),
        min(image.width, box[2] + pad),
        min(image.height, box[3] + pad),
    )
    return image.crop(box)


def footer_font(size=22):
    for name in ("segoeui.ttf", "arial.ttf", "calibri.ttf"):
        path = os.path.join(os.environ.get("WINDIR", r"C:\Windows"), "Fonts", name)
        if os.path.exists(path):
            return ImageFont.truetype(path, size)
    return ImageFont.load_default()


def make_page(image, label, page_no, total):
    page = Image.new("RGB", (PAGE_W, PAGE_H), "white")

    available = (PAGE_W - MARGIN * 2, PAGE_H - MARGIN - FOOTER - MARGIN)
    fitted = ImageOps.contain(image, available, Image.LANCZOS)

    x = (PAGE_W - fitted.width) // 2
    y = MARGIN + (available[1] - fitted.height) // 2
    page.paste(fitted, (x, y))

    draw = ImageDraw.Draw(page)
    line_y = PAGE_H - FOOTER + 10
    draw.line((MARGIN, line_y, PAGE_W - MARGIN, line_y), fill=(203, 213, 225), width=2)
    draw.text(
        (PAGE_W // 2, PAGE_H - FOOTER // 2 + 10),
        f"{label}  |  Halaman {page_no} dari {total}",
        font=footer_font(),
        fill=(71, 85, 105),
        anchor="mm",
    )
    return page


def main():
    pages = []
    for page_no, (source, final_name, label) in enumerate(ITEMS, start=1):
        image = crop_content(source)
        image.save(os.path.join(BASE, final_name))
        pages.append(make_page(image, label, page_no, len(ITEMS)))
        print(final_name, image.size)

    pdf_path = os.path.join(BASE, "sso-flowchart.pdf")
    pages[0].save(
        pdf_path,
        "PDF",
        resolution=150,
        save_all=True,
        append_images=pages[1:],
    )
    print("sso-flowchart.pdf", os.path.getsize(pdf_path), "bytes")


if __name__ == "__main__":
    main()
