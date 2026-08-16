import os
import re
from collections import Counter
# Verificaquantas vezes um estilo inline está sendo usado e em quantos arquivos
views_dir = '/var/www/html/agsonhos/resources/views'
style_pattern = re.compile(r'style="([^"]*)"', re.IGNORECASE)

styles = []
file_styles = {}

for root, dirs, files in os.walk(views_dir):
    for file in files:
        if file.endswith('.twig'):
            path = os.path.join(root, file)
            with open(path, 'r', encoding='utf-8') as f:
                content = f.read()
            found = style_pattern.findall(content)
            if found:
                file_styles[path] = found
                styles.extend(found)

counter = Counter(styles)
print(f"Total inline styles found: {len(styles)}")
print(f"Unique styles count: {len(counter)}")
print("\nMost common styles:")
for style, count in counter.most_common(30):
    print(f"{count:3d}x | {style}")
