
# 📦 Related Posts Block – User Guide

Display a beautifully styled list of related posts in your WordPress site using a simple block or shortcode. Easily customize the block title and enjoy a responsive, modern design out of the box.

---

## 🔧 Features

- Adds a **Gutenberg block** to display related posts
- Supports a **custom title** via block settings or shortcode
- Automatically styled using modern, responsive cards
- Optionally insert using `[related_posts]` shortcode

---

## 📥 Installation

You can install the plugin manually:

### ✅ 1. Upload via WordPress Admin

1. Download the plugin ZIP or clone it:
   ```bash
   git clone https://github.com/himanshukhanna0/wp-related-post.git
   ```
2. Zip the folder (if not already zipped)
3. Go to your WordPress dashboard → **Plugins > Add New**
4. Click **Upload Plugin** and select the ZIP file
5. Click **Install Now** → then **Activate**

---

### ✅ 2. Upload via FTP/SFTP

1. Upload the plugin folder to:
   ```
   /wp-content/plugins/related-posts-block/
   ```
2. Go to **Plugins > Installed Plugins** in your WP admin
3. Click **Activate**

---

## 🧱 How to Use the Block

Once activated, you’ll find a block named:

> 🔹 **Related Posts** (under the *Widgets* category)

### 🖋️ Inserting the block:

1. Open any post in the block editor
2. Click the ➕ icon and search for **Related Posts**
3. Click to insert the block
4. In the right sidebar under **Settings**, you’ll see:
   - A **Title** field where you can change the heading

The block will render styled posts on the frontend.

---

## 💬 Shortcode Usage

You can also use a shortcode if you prefer classic editor or template use.

---

### Syntax:

```
[related_posts title="something"]
```

- `Title` is optional
- The shortcode output is styled exactly like the block

---

## ⚡ Caching

This plugin includes optional caching to improve performance by storing the rendered output of related posts. This avoids running repeated database queries on every page load.

### How It Works

- The rendered HTML is cached using WordPress transients or an internal cache layer.
- The cache is typically keyed by the post ID or query context.
- Cached output is reused unless invalidated manually or after expiration.

### When Cache Refreshes

- The cache will automatically refresh:
  - When a post is updated
  - When the transient (default: 12 hours) expires
  - If you manually clear it

### How to Clear the Cache

To force a refresh:
- Update the post/page where the block is placed
- Or, in WordPress Admin → **Settings > Related Posts Block**, click “Clear Cache” 
If you're a developer, you can clear it using this code snippet:
```php
delete_transient('rpb_related_posts_cache_' . get_the_ID());
```
---


## 🎨 Styling & Layout

The plugin uses a custom `style.css` with:

- Responsive card layout
- Hover animations
- Clean typography

You can override styles via your theme or child theme by targeting classes like `.rpb-related-posts`, `.rpb-title`, etc.

---

## 🛠️ Developer Notes

- The block is rendered using a PHP `render_callback` for dynamic output.
- The block is registered manually (no block.json).

---

## 🐞 Troubleshooting

- If the block doesn’t render preview:
  - Make sure you’ve activated the plugin and cleared your browser cache

---

## 📄 License

This plugin is licensed under the [MIT License](LICENSE) (or WordPress GPLv2 if preferred). Use, modify, or distribute as needed.

---

## 🙋 Support & Contributions

Feel free to open an issue or pull request on [GitHub](https://github.com/himanshukhanna0/wp-related-post) for improvements, bug fixes, or questions.
