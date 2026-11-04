import os
import glob
import re

cart_js_new = """        document.querySelectorAll('.add-to-cart').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                let id = this.getAttribute('data-id');
                let name = this.getAttribute('data-name');
                let price = this.getAttribute('data-price');
                let image = this.getAttribute('data-image');
                
                let cartItems = JSON.parse(localStorage.getItem('maggadit_cart')) || [];
                let existingItemIndex = cartItems.findIndex(item => item.id == id);
                
                if (existingItemIndex > -1) {
                    cartItems[existingItemIndex].quantity += 1;
                } else {
                    cartItems.push({
                        id: id,
                        name: name,
                        price: price,
                        quantity: 1,
                        image: image,
                        discount: 0
                    });
                }
                
                localStorage.setItem('maggadit_cart', JSON.stringify(cartItems));
                showToast(`✅ تم إضافة "${name}" إلى السلة`);
            });
        });"""

def process_file(filepath):
    with open(filepath, 'r') as f:
        content = f.read()
    
    # 1. Update the JS block
    # Pattern to match the old JS block
    pattern_js = r"document\.querySelectorAll\('\.add-to-cart'\)\.forEach\(btn => \{[\s\S]*?showToast\(`✅ تم إضافة \"\$\{name\}\" إلى السلة`\);\s*\}\);\s*\}\);"
    
    if "JSON.parse(localStorage.getItem('maggadit_cart'))" not in content:
        content = re.sub(pattern_js, cart_js_new, content)
        
        # fallback replace if the exact pattern failed
        pattern_js_alt = r"document\.querySelectorAll\('\.add-to-cart'\)\.forEach\(btn => \{[\s\S]*?e\.preventDefault\(\);\s*(const|let) name = this\.getAttribute\('data-name'\);\s*showToast\(`✅ تم إضافة \"\$\{name\}\" إلى السلة`\);\s*\}\);\s*\}\);"
        content = re.sub(pattern_js_alt, cart_js_new, content)
        
        # 2. Update the button data attributes
        # Find buttons that don't have data-id but have add-to-cart
        # we will use regex to find `<button class="add-to-cart ..."` and inject data-id and data-image
        # this is tricky with regex, let's just replace the specific lines if needed
        # Or look for `data-name="{{ $item->... }}"` and inject data-id before it.
        content = re.sub(
            r'(\s+)data-name="\{\{\s*(\$item->name[^}]*)\s*\}\}"(\s+)data-price',
            r'\1data-id="{{ $item->id }}"\1data-name="{{ \2 }}"\3data-price',
            content
        )
        content = re.sub(
            r'(\s+)data-price="\{\{\s*(\$item->price[^}]*)\s*\}\}"',
            r'\1data-price="{{ \2 }}"\1data-image="{{ $item->image ?? \'📦\' }}"',
            content
        )
        
        with open(filepath, 'w') as f:
            f.write(content)
        print(f"Updated {filepath}")

for root, _, files in os.walk('resources/views/app_android'):
    for file in files:
        if file.endswith('.blade.php'):
            process_file(os.path.join(root, file))

