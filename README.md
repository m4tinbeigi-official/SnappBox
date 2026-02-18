# SnappBox WooCommerce Delivery

Official integration for SnappBox delivery services.

### Contributors
*   SnappBox Team (@snappbox)
*   @samooel
*   @matinbeigi
*   Rick Sanchez

### 🚀 Version 1.2.0 - The Professional Transformation
This major update introduces a complete architectural overhaul and visual standard alignment.

#### 🏗️ Core Architecture
- **Namespace Standardization**: Migrated the entire codebase to `Snappbox` PSR-4 namespaces.
- **Bootstrap Layer**: Introduced `Snappbox\Core\App` to handle plugin lifecycle.
- **Singleton Management**: Ensures only one instance of core components exists.
- **Professional Autoloader**: Pure PSR-4 compliant class loading.

#### �️ Security & Professionalism
- **HPOS Compatibility**: Fully compatible with WooCommerce High-Performance Order Storage.
- **Enhanced Security**: Mandatory nonce and capability checks for all administrative and front-end actions.
- **Input Sanitization**: 100% coverage on all user-submitted fields.
- **Composer Integration**: Added `composer.json` for modern dependency management.

#### � Visual Standards (SnappBox DNA)
- **Brand Alignment**: Colors, shadows, and geometry now perfectly match the official SnappBox website.
- **Wizard Redesign**: Reimagined the Quick Setup Wizard with mesh gradients and glassmorphism.
- **UX Polish**: Improved transitions, map interactivity, and mobile responsiveness.

#### ⚡ Performance
- **Smart Caching**: Implemented a 1-hour transient cache for expensive API calls (Wallet Balance, Nearby Bikers).
- **Asset Optimization**: Version controlled CSS/JS assets to prevent browser caching issues after updates.

### Requirements
*   WooCommerce 7.0+
*   PHP 7.4+
