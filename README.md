# Windows 95 WordPress Theme

A WordPress theme that faithfully recreates the Windows 95 desktop experience. Draggable, resizable windows. A working Start menu and taskbar. Cascading window positioning. A built-in PDF viewer styled like Acrobat Reader. Desktop icons. The whole deal.

![Windows 95 Theme](https://img.shields.io/badge/WordPress-5.0%2B-blue) ![License](https://img.shields.io/badge/license-GPL--2.0-green)

## Features

### Desktop Environment
- **Draggable & resizable windows** from all edges and corners
- **Authentic cascade positioning** using the real Win95 `CW_USEDEFAULT` algorithm (22px offset, 75% work area sizing, cascade reset)
- **Taskbar** with Start button, Quick Launch bar, window buttons, and a system clock
- **Start menu** with configurable navigation (uses WordPress nav menus or auto-generates from pages)
- **Desktop icons** showing your published Pages with customizable "My Blog" folder
- **Multi-window support** — clicking desktop icons spawns new windows (up to 5), with AJAX page loading
- **Window controls** — minimize, maximize, close, double-click title bar to maximize

### Content Display
- **Blog listing** styled as Internet Explorer with address bar, menu bar (File/Edit/View/Help), and status bar
- **Single posts/pages** open as Notepad-style windows
- **PDF viewer** — any PDF link automatically opens in a Win95 Acrobat Reader window with toolbar
- **Comments** rendered as an embedded window with comment count in the title bar
- **404 page** styled as a Blue Screen of Death

### Social Links
- Configurable social window (bottom-right) with beveled icon buttons
- Supports 15 networks: Twitter/X, Discord, GitHub, YouTube, Twitch, Instagram, Facebook, LinkedIn, Mastodon, Reddit, TikTok, Bluesky, Email, RSS, Website
- Quick Launch shortcut in the taskbar
- Configured via **Appearance → Customize → Social Links**

### Styling
- **W95FA pixel font** for authentic text rendering
- CSS-only beveled borders, sunken panels, and raised buttons via `box-shadow`
- Custom scrollbars (WebKit)
- Visited link colors suppressed on UI chrome elements
- Responsive breakpoints (icons hidden < 768px, taskbar buttons hidden < 480px)

## Installation

1. Download the `windows95-wp.zip` from this repo
2. In WordPress admin: **Appearance → Themes → Add New → Upload Theme**
3. Upload the zip and activate

Or clone directly into your themes directory:
```bash
cd wp-content/themes/
git clone https://github.com/ajbr0wn/windows95-wp.git
```

## Configuration

### Social Links
**Appearance → Customize → Social Links** — enter URLs for any networks you want displayed. Leave blank to hide.

### Blog Folder Label
**Appearance → Customize → Social Links → Blog Folder Label** — customize the desktop icon text (default: "My Blog").

### Navigation Menus
- **Start Menu** — assign a menu to the "Start Menu" location for custom Start menu items
- **Quick Launch Bar** — assign a menu to the "Quick Launch Bar" location

### Desktop Icons
Desktop icons are auto-generated from your published Pages (up to 6, sorted by menu order). The "My Computer" icon links to your homepage, and "My Blog" links to your Posts page.

### Static Homepage
For the best experience, set a static homepage (**Settings → Reading**) and assign a separate Posts page.

## File Structure

```
windows95-wp/
├── assets/
│   ├── css/
│   │   ├── win95.css          # Core Win95 UI component library
│   │   ├── theme.css          # WordPress layout & theme styles
│   │   └── fonts.css          # W95FA font face declaration
│   ├── js/
│   │   └── win95.js           # Window management, Start menu, drag/resize, PDF viewer
│   ├── fonts/
│   │   ├── w95fa.woff
│   │   └── w95fa.woff2
│   ├── icons/                 # 16x16 and 32x32 raster PNG icons
│   └── icons.svg              # SVG sprite fallback
├── header.php                 # Desktop, icons grid, main window chrome
├── footer.php                 # Status bar, social window, taskbar, Start menu
├── index.php                  # Blog post listing
├── single.php                 # Single post template
├── page.php                   # Page template
├── archive.php                # Archive/category listing
├── search.php                 # Search results
├── comments.php               # Comments as embedded window
├── 404.php                    # Blue Screen of Death
├── functions.php              # Theme setup, Customizer, social links, icon helper
└── style.css                  # Theme metadata
```

## Credits

- **Icons**: Raster PNGs sourced from [AlexBSoft/win95.css](https://github.com/AlexBSoft/win95.css)
- **Font**: [W95FA](https://fontsarena.com/w95fa-by-alina-sava/) by Alina Sava (SIL Open Font License)
- **Inspiration**: [Brutalist Themes W95](https://brutalistthemes.com/downloads/w95/)

## License

GPL-2.0 — same as WordPress.
