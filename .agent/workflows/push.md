---
description: Automated build, lint, dynamic commit, and GitHub push
---
// turbo-all
1. Run the production build to ensure all assets are updated:
```bash
npm run build
```

2. Perform a final linting check to maintain elite standards:
```bash
composer lint
```

3. Update the `README.md` if any architectural or feature changes were made during this session.

4. Add all changes to git:
```bash
git add .
```

5. Analyze all staged changes (`git diff --cached`) and the recent task progress in `task.md`.
6. Generate a professional, enterprise-grade commit message following conventional commits (e.g., `feat:`, `fix:`, `chore:`).
7. The commit message must include a concise subject line and a bulleted body explaining the key technical changes.
8. Commit the changes:
```bash
git commit -m "[Subject]" -m "[Detailed Body]"
```

9. Push to the main branch:
```bash
git push origin main
```
