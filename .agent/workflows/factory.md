---
description: Elite factory for boilerplate generation
---
// turbo-all
1. Identify the intended architecture for the new component (Service, Entity, Controller, or Repository).
2. Generate the appropriate PHP class/interface with strict types and PSR-4 namespacing.
3. Automatically implement foundational unit tests in the `tests/` directory (if applicable).
4. Register the new component in the central `App.php` bootstrap.
5. If the component requires a UI settings field, automatically update `AdminPage.php` and `SetupWizard.php` registries using the established `AUTO_APPEND` hooks.
6. Verify the entire project build.
