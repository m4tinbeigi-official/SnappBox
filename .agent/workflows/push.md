---
description: Automated build, documentation sync, and GitHub push
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

5. Commit changes with a professional message:
```bash
git commit -m "chore: automated sync and documentation update"
```

6. Push to the main branch:
```bash
git push origin main
```
