import os
import re
import shutil

def slugify(text):
    text = text.lower()
    text = re.sub(r'[^\w\s-]', '', text)
    text = re.sub(r'[\s_]+', '-', text)
    return text.strip('-')

# Load game names
game_names = {}
if os.path.exists('games_list.txt'):
    with open('games_list.txt', 'r') as f:
        for line in f:
            line = line.strip()
            if not line: continue
            match = re.match(r'^(\d+), (.*)$', line)
            if match:
                game_names[int(match.group(1))] = match.group(2)

# Load target images from target_images.txt
images = []
if os.path.exists('target_images.txt'):
    with open('target_images.txt', 'r') as f:
        content = f.read()
        matches = re.findall(r"\((\d+),\s*(\d+),\s*(\d+),\s*'([^']+)'\)", content)
        for m in matches:
            images.append({
                'id': int(m[0]),
                'game_id': int(m[1]),
                'is_cover': int(m[2]),
                'filename': m[3].lstrip('/')
            })

print(f"Loaded {len(game_names)} games and {len(images)} image records.")

os.makedirs('images/games', exist_ok=True)

all_dirs = [d for d in os.listdir('images') if os.path.isdir(os.path.join('images', d))]

# Process
for img in images:
    target_path = img['filename']
    target_dir = os.path.dirname(target_path)
    game_id = img['game_id']
    game_name = game_names.get(game_id)
    
    if os.path.exists(target_path):
        continue

    # 1. Handle images/games/ structure
    if target_dir == 'images/games':
        if not game_name:
            continue
            
        found_source = None
        for d in all_dirs:
            full_d = os.path.join('images', d)
            if d.lower() == game_name.lower() or \
               d.lower().replace(':', '') == game_name.lower().replace(':', '') or \
               slugify(d) == slugify(game_name):
                found_source = full_d
                break
        
        if found_source:
            for f in os.listdir(found_source):
                if f.lower().startswith('cover.') or f.lower().endswith(('.jpg', '.jpeg', '.png')):
                    src_file = os.path.join(found_source, f)
                    print(f"Copying {src_file} -> {target_path}")
                    shutil.copy2(src_file, target_path)
                    break
        continue

    # 2. Handle kebab-case structure
    if not os.path.exists(target_dir):
        if not game_name:
            continue
            
        found_dir = None
        target_slug = os.path.basename(target_dir)
        for d in all_dirs:
            if slugify(d) == target_slug:
                found_dir = os.path.join('images', d)
                break
        
        if found_dir:
            print(f"Renaming directory: {found_dir} -> {target_dir}")
            os.makedirs(os.path.dirname(target_dir), exist_ok=True)
            os.rename(found_dir, target_dir)
            # Update all_dirs since we renamed one
            all_dirs = [d for d in os.listdir('images') if os.path.isdir(os.path.join('images', d))]
    
    # Check for image file inside the target_dir
    if os.path.exists(target_dir):
        if not os.path.exists(target_path):
            for f in os.listdir(target_dir):
                if f.lower().startswith('cover.') and f.lower().endswith(('.jpeg', '.jpg', '.png', '.webp')):
                    source_image = os.path.join(target_dir, f)
                    print(f"Renaming image: {source_image} -> {target_path}")
                    os.rename(source_image, target_path)
                    break
