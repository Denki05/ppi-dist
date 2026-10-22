from PIL import Image
import os

base = r"C:\xampp\htdocs\ppi-dist\docs\sso-flowchart"


def crop_content(filename, pad=30):
    im = Image.open(os.path.join(base, filename))
    mask = im.convert("L").point(lambda x: 0 if x > 245 else 255)
    box = mask.getbbox()
    if not box:
        return im
    box = (
        max(0, box[0] - pad),
        max(0, box[1] - pad),
        min(im.size[0], box[2] + pad),
        min(im.size[1], box[3] + pad),
    )
    return im.crop(box)


page1 = crop_content("page1.png")
page2 = crop_content("page2.png")

page1.save(os.path.join(base, "halaman-1.png"))
page2.save(os.path.join(base, "halaman-2.png"))

# PDF: two pages with a uniform large page so both diagrams stay readable.
PAGE_W, PAGE_H = 1800, 4800
MARGIN = 40


def fit_page(im):
    canvas = Image.new("RGB", (PAGE_W, PAGE_H), "white")
    copy = im.copy()
    copy.thumbnail((PAGE_W - MARGIN * 2, PAGE_H - MARGIN * 2), Image.LANCZOS)
    x = (PAGE_W - copy.width) // 2
    y = (PAGE_H - copy.height) // 2
    canvas.paste(copy, (x, y))
    return canvas


pages = [fit_page(page1), fit_page(page2)]
pdf_path = os.path.join(base, "sso-flowchart.pdf")
pages[0].save(
    pdf_path,
    "PDF",
    resolution=150,
    save_all=True,
    append_images=pages[1:],
)

print("halaman-1.png", page1.size)
print("halaman-2.png", page2.size)
print("sso-flowchart.pdf", os.path.getsize(pdf_path), "bytes")
