---
description: API Sentinel - Autonomous Documentation Sync
---
// turbo-all
1. Access the official SnappBox API documentation:
   `https://snapp-box.com/api-doc`
2. Compare the current documentation against the local reference:
   `includes/Core/Automation/API_SNAPSHOT.json`
3. Identify new endpoints, new parameters in existing endpoints, or updated authentication schemes.
4. If changes are detected:
   - **Step 4a**: Use the `factory` workflow to generate new Service classes in `includes/API`.
   - **Step 4b**: Update `API_SNAPSHOT.json` with the new version and endpoint mapping.
   - **Step 4c**: Use the `add-feature` workflow to register the new services.
   - **Step 4d**: Trigger the `debug` workflow to verify the generated code.
   - **Step 4e**: Trigger the `audit` workflow to ensure security and performance optimization (caching).
5. Finalize the sync by executing the `push` workflow with an automated commit message detailing the API updates.
