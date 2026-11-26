#!/usr/bin/env python3
"""Regenerate the app icons and in-app logo from the dashboard icon.

Usage (from the repository root, with Pillow installed):

    python3 mobile/customer_app/tool/generate_brand_assets.py [source.png]

The source is the square icon shipped with the web dashboard
(`public/icon.png`). The script locates the monogram at the top of that image,
keys out the slate background and writes:

* `assets/images/logo_tile.png` — monogram on the slate canvas (splash/login)
* `assets/images/logo_mark.png` — monogram only, transparent background
* Android `mipmap-*/ic_launcher.png`, the iOS `AppIcon.appiconset`, and the
  web `icons/*.png` + `favicon.png`
"""

import sys
from pathlib import Path

from PIL import Image

APP_DIR = Path(__file__).resolve().parents[1]
DEFAULT_SOURCE = APP_DIR.parents[1] / "public" / "icon.png"
SLATE = (75, 76, 81, 255)

IOS_SIZES = {
    "20x20@1x": 20, "20x20@2x": 40, "20x20@3x": 60,
    "29x29@1x": 29, "29x29@2x": 58, "29x29@3x": 87,
    "40x40@1x": 40, "40x40@2x": 80, "40x40@3x": 120,
    "60x60@2x": 120, "60x60@3x": 180,
    "76x76@1x": 76, "76x76@2x": 152, "83.5x83.5@2x": 167,
    "1024x1024@1x": 1024,
}

ANDROID_SIZES = {
    "mipmap-mdpi": 48, "mipmap-hdpi": 72, "mipmap-xhdpi": 96,
    "mipmap-xxhdpi": 144, "mipmap-xxxhdpi": 192,
}


def find_mark(image):
    """Crop the monogram: the bounding box of every non-background pixel above the title."""
    width, height = image.size
    background = image.getpixel((2, 2))[:3]
    pixels = image.load()
    bounds = [width, height // 2, 0, 0]
    for y in range(height // 2):
        for x in range(width):
            r, g, b = pixels[x, y][:3]
            if abs(r - background[0]) + abs(g - background[1]) + abs(b - background[2]) > 40:
                bounds = [min(bounds[0], x), min(bounds[1], y), max(bounds[2], x), max(bounds[3], y)]
    left, top, right, bottom = bounds
    return image.crop((left, top, right + 1, bottom + 1))


def key_out_background(mark):
    out = mark.copy()
    pixels = out.load()
    for y in range(out.size[1]):
        for x in range(out.size[0]):
            r, g, b, _ = pixels[x, y]
            distance = abs(r - SLATE[0]) + abs(g - SLATE[1]) + abs(b - SLATE[2])
            if distance < 26:
                pixels[x, y] = (0, 0, 0, 0)
            elif distance < 70:
                pixels[x, y] = (r, g, b, int(255 * (distance - 26) / 44))
    return out


def tile(mark, size, pad_ratio=0.66):
    canvas = Image.new("RGBA", (size, size), SLATE)
    inner = max(1, int(size * pad_ratio))
    scaled = mark.copy()
    scaled.thumbnail((inner, inner), Image.LANCZOS)
    canvas.alpha_composite(scaled, ((size - scaled.width) // 2, (size - scaled.height) // 2))
    return canvas


def main():
    source = Path(sys.argv[1]) if len(sys.argv) > 1 else DEFAULT_SOURCE
    icon = Image.open(source).convert("RGBA")
    mark = find_mark(icon)

    images = APP_DIR / "assets" / "images"
    images.mkdir(parents=True, exist_ok=True)
    tile(mark, 512).save(images / "logo_tile.png", optimize=True)
    key_out_background(mark).save(images / "logo_mark.png", optimize=True)

    for folder, size in ANDROID_SIZES.items():
        target = APP_DIR / "android/app/src/main/res" / folder / "ic_launcher.png"
        target.parent.mkdir(parents=True, exist_ok=True)
        tile(mark, size, 0.60).convert("RGB").save(target, optimize=True)

    ios = APP_DIR / "ios/Runner/Assets.xcassets/AppIcon.appiconset"
    ios.mkdir(parents=True, exist_ok=True)
    for name, size in IOS_SIZES.items():
        tile(mark, size).convert("RGB").save(ios / f"Icon-App-{name}.png", optimize=True)

    web = APP_DIR / "web"
    (web / "icons").mkdir(parents=True, exist_ok=True)
    tile(mark, 192).save(web / "icons/Icon-192.png", optimize=True)
    tile(mark, 512).save(web / "icons/Icon-512.png", optimize=True)
    tile(mark, 192, 0.48).save(web / "icons/Icon-maskable-192.png", optimize=True)
    tile(mark, 512, 0.48).save(web / "icons/Icon-maskable-512.png", optimize=True)
    tile(mark, 32).convert("RGB").save(web / "favicon.png", optimize=True)

    print(f"brand assets regenerated from {source}")


if __name__ == "__main__":
    main()
