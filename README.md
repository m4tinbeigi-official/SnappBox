<p align="center">
  <img src="assets/img/sb-log.svg" alt="SnappBox Logo" width="200" />
</p>

# SnappBox Elite v1.2.0
> **The Enterprise-Grade WooCommerce Delivery Integration.**

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D%207.4-777bb4?style=for-the-badge&logo=php)](https://php.net)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-%3E%3D%207.0-96588a?style=for-the-badge&logo=woocommerce)](https://woocommerce.com)
[![License](https://img.shields.io/badge/License-GPL%20v2-lightgrey?style=for-the-badge)](LICENSE)
[![Build System](https://img.shields.io/badge/Build-Vite%20%2B%20React-646cff?style=for-the-badge&logo=vite)](https://vitejs.dev)

Welcome to the **SnappBox Elite** repository. This is not just a plugin; it is a high-performance, container-driven engineering masterpiece designed for the modern WordPress ecosystem.

---

## 🚀 Key Advantages

- **💎 Elite Architecture**: Powered by **PHP-DI**, ensuring a decoupled, testable, and scalable codebase.
- **⚡ Supercharged Frontend**: A lightning-fast **React + TypeScript** admin interface bundled with **Vite**.
- **🎨 Modern Design**: Utilizing **Tailwind CSS v4** for a premium, high-contrast User Experience.
- **📡 Remote Broadcast (CMS)**: Manage your community directly from your GitHub repository using our native Remote Broadcast system.
- **🔒 Security First**: Strict typing (`strict_types=1`), non-ces, and capability checks are baked into every layer.

---

## 🏗️ Technical Stack

| Component | Technology | Description |
| :--- | :--- | :--- |
| **Core Logic** | PHP 7.4+ | Strict-typed, Container-driven logic |
| **DI Container** | [PHP-DI](https://php-di.org/) | Enterprise dependency management |
| **Frontend** | React 18 / TS | Modern, stateful UI development |
| **Styling** | [Tailwind CSS v4](https://tailwindcss.com/) | Next-gen utility-first styling |
| **Build Tool** | [Vite 6](https://vitejs.dev/) | Optimized asset bundling & HMR |

---

## 🛠️ Developer Installation

For those who want to contribute or build upon this elite foundation:

1. **Clone the Repository**
   ```bash
   git clone https://github.com/m4tinbeigi-official/SnappBox.git
   ```

2. **Install PHP Dependencies**
   ```bash
   composer install --optimize-autoloader
   ```

3. **Install Frontend Dependencies**
   ```bash
   npm install
   ```

4. **Development (HMR)**
   ```bash
   npm run dev
   ```

6. **Elite Sync (Optional)**
   - Simply type `push` in the chat to automatically build, lint, update documentation, and push to GitHub.

---

## 📡 Remote Broadcast Management

You can update the announcements shown in the plugin admin panel by modifying the `broadcast.json` file in this repository.

```json
{
  "active": true,
  "type": "success",
  "title": "Elite Update Released!",
  "content": "Explore the new React-based setup wizard now.",
  "image": "https://...",
  "button": { "text": "Learn More", "url": "..." }
}
```

---

## 🤝 Contribution & Standards

We maintain an enterprise standard. All code must pass:
- **PHPCS**: WordPress Coding Standards.
- **PHPStan**: Static Analysis Level 5+.
- **Prettier**: Consistent frontend formatting.

---

<p align="center">
  Driven by Excellence • Engineered by Professionals
  <br />
  <b>© 2026 SnappBox Team</b>
</p>
