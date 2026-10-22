from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED
root = Path(__file__).resolve().parents[1]
with ZipFile(root / 'photo-academy-wordpress.zip', 'w', ZIP_DEFLATED) as z:
    for name in ['wordpress/photo-academy.php', 'index.html', 'assets/style.css', 'assets/app.js']:
        target = 'photo-academy/' + ('photo-academy.php' if name.startswith('wordpress/') else name)
        z.write(root / name, target)
print('Archive WordPress créée : photo-academy-wordpress.zip')
