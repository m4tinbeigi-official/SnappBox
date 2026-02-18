---
description: Comprehensive security and performance audit
---
// turbo-all
1. Perform a project-wide security scan focusing on:
   - Missing `current_user_can` checks.
   - Unsanitized inputs in `$_POST`, `$_GET`, and `$_REQUEST`.
   - Missing nonce verification in AJAX and form handlers.
   - Improperly escaped outputs (`echo` without `esc_*`).
2. Run a performance audit focusing on:
   - Expensive API calls without transient caching.
   - Resource-intensive functions in `ShippingMethod`.
   - Large asset footprints in `assets/dist`.
3. Generate a "Hardening Report" in the brain directory.
4. Automatically apply critical security patches and performance optimizations.
5. Execute the `push` workflow to finalize the audit.
