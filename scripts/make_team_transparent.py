from pathlib import Path

from PIL import Image
from rembg import new_session, remove

src = Path(r'C:\Users\eGen\.cursor\projects\c-xampp-htdocs-Hospital-management-system1-main\assets\hero-medical-team-whitebg.jpg')
if not src.exists():
    src = Path(r'c:\xampp\htdocs\Hospital-management-system1-main\public\images\hero-medical-team.jpg')

img = Image.open(src).convert('RGBA')
session = new_session('u2net')
cutout = remove(img, session=session)

bbox = cutout.getbbox()
if bbox:
    left, top, right, bottom = bbox
    pad = 16
    cutout = cutout.crop((
        max(0, left - pad),
        max(0, top - pad),
        min(cutout.width, right + pad),
        min(cutout.height, bottom + pad),
    ))

out = Path(r'c:\xampp\htdocs\Hospital-management-system1-main\public\images\hero-medical-team.png')
cutout.save(out, 'PNG')
print(f'saved {out} ({out.stat().st_size} bytes) size={cutout.size}')
