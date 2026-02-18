---
description: Automated deep-tissue debugging and self-healing
---
// turbo-all
1. Access the Enterprise Logger (`includes/Core/Diagnostics/Logger.php`) and inspect for recent errors.
2. Verify API connectivity by running the `HealthCheck` service.
3. Perform a full static analysis on the `includes/` directory:
```bash
vendor/bin/phpstan analyze --level 6 includes
```
4. Run a full WPCS audit:
```bash
composer lint
```
5. Based on any identified issues (logs, health check, or linting failures), provide a detailed technical diagnosis.
6. Automatically apply fixes for any high-confidence issues.
7. Run the production build and verify the fix.
