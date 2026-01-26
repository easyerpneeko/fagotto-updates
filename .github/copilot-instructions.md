# Fagotto ERP - AI Coding Agent Instructions

## Architecture Overview

**Hybrid Electron + Vue.js + PHP Application**: This is a desktop ERP system built with Electron (v13) and Vue 2, with a Laravel PHP backend for API services.

- **Frontend**: Electron app with Vue 2 renderer process ([src/renderer/](../src/renderer/))
- **Electron Main Process**: [src/main/index.js](../src/main/index.js) - handles window management, IPC, and auto-updates
- **PHP Backend**: Laravel-based API in [app/](../app/) directory
- **Database**: MySQL with migrations in [database/](../database/)

## Critical Project Structure

```
src/
  main/                  # Electron main process
  renderer/              # Vue.js frontend
    components/          # Vue components (UpdateDialog.vue, etc.)
    helpers/            # Core utilities (ConfigHelper, Connection, EchoHelper)
    store/              # Vuex modules (products, sells, cafeteria, etc.)
    views/              # Page components
    router/             # Vue Router with turno (shift) guards
app/                    # Laravel PHP models & controllers
database/               # SQL migrations
.electron-vue/          # Webpack configs for Electron
```

## Configuration & Authentication System

### Application Serial-Based Authentication
- **aplication.json**: Stores application serial (`Serial` field) that identifies the franchise/location
- **authorization.json**: Stores user JWT token after login
- **ConfigHelper.js**: Manages app initialization and serial verification
  - `InitializeAplication()` - Reads aplication.json and validates with backend
  - `ConfigHandler()` - Manages app config state
- **Connection.js**: HTTP client with Bearer token auth
  - `fillHeaders(token)` - Sets Bearer auth
  - `fillAppHeader(serial)` - Sets App-Key header with serial
- **BaseUrl.js**: Central API URL management via Vuex store (`store.state.apiUrl`)

### Authentication Flow
1. App reads `aplication.json` for Serial
2. Validates Serial with backend API (`api/getApp`)
3. User logs in → JWT stored in `authorization.json`
4. All requests include both `Authorization: Bearer {token}` and `App-Key: {serial}`

### Multi-Tenant Data Isolation
**CRITICAL**: Each franchise/location (negocio) must only see its own data:
- Backend uses `CurrentApp::getApp()` to get current `app_id` from App-Key header
- All queries MUST filter by `app_id` or `id_negocio` field
- Example: `->where('app_id', CurrentApp::getApp())`
- Admin endpoints (suffix `...Admin`) bypass this filter to see all data

## Router & Navigation Guards

**Turno (Work Shift) System**: [src/renderer/router/index.js](../src/renderer/router/index.js)
- Routes are protected by `verificarTurnoActivo()` guard
- Checks localStorage for `turnoActivo`, `fechaTurno` (must match current date)
- Auto-clears shifts from previous days
- Users must start a shift before accessing most pages

## Vuex Store Architecture

**Modular Vuex Store**: [src/renderer/store/index.js](../src/renderer/store/index.js)
- Uses `vuex-electron` for persistence and IPC sync
- Key modules:
  - `main` - Global state, API URL, version
  - `products` - Product catalog
  - `sells` - Sales management
  - `cafeteria` - Table/waiter management
  - `reposteria` - Bakery-specific features
  - `requests` - Order management

## Real-time Communication

**Laravel Echo + Pusher**: [src/renderer/helpers/EchoHelper.js](../src/renderer/helpers/EchoHelper.js)
- Private channel broadcasting with Pusher
- Custom authorizer sends `App-Key` + `authorization` headers
- Handles real-time updates for orders, kitchen displays, etc.

## Electron IPC Patterns

**Main ↔ Renderer Communication**: See [src/main/index.js](../src/main/index.js:338-850)

Common IPC channels:
- `app_version` - Get app version
- `check_for_updates` - Trigger update check
- `install_update` - Install downloaded update
- `restart_app` - Restart application
- `navigate-to` - Navigate to route via menu

Auto-updater events (renderer receives):
- `update_available` - New version found
- `download_progress` - Download progress updates
- `download_completed` - Download finished
- `update_error` - Error occurred

## Auto-Update System

**GitHub Private Repo Updates**: Custom implementation in [src/main/index.js](../src/main/index.js:300-400)
- Uses GitHub API with PAT (Personal Access Token)
- Token stored in `gh_token.json` or `GH_TOKEN` env var
- Manual download handling with `customFetchBinary()` for private releases
- Component: [UpdateDialog.vue](../src/renderer/components/UpdateDialog.vue) shows update UI

**⚠️ Security Note**: GitHub token is hardcoded in multiple places - consider environment variables only.

## Build & Development Commands

```bash
# Development with hot reload
npm run dev

# Build Windows installer
npm run build                    # Standard build
npm run build:production         # Production mode
npm run build:jenkins-windows    # CI/CD (32-bit)

# Deploy to GitHub Releases
npm run deploy                   # Publish always
npm run deploy-never             # Build without publish

# Package management
npm run pack                     # Webpack both processes
npm run pack:main                # Main process only
npm run pack:renderer            # Renderer process only
```

**Webpack Configs**: `.electron-vue/webpack.{main,renderer}.config.js`

## Module & Settings System

**Dynamic Module Configuration**: [aplication.json](../aplication.json)
- Contains `Modules` array with submodules and settings
- Each module has versioned features that can be enabled/disabled
- `Entorno` object defines environment-specific settings (printer, SII, etc.)
- `TypeUsers` defines role-based permissions
- Example: Cafeteria module enables table management, waiter additions, kitchen mode

## Printing System

**Custom Node Printer**: [src/renderer/helpers/IMNodePrinter.js](../src/renderer/helpers/IMNodePrinter.js)
- Direct printer access via Electron
- Thermal receipt printing for tickets/orders
- Configurable via `settings_printer` in aplication.json

## Database Migrations

**SQL Files**: [database/](../database/)
- Migration files for all schema changes
- Naming pattern: `add_{feature}_{tables}.sql`
- Applied manually or via backend deployment

## Conventions

1. **Vue Components**: PascalCase files, kebab-case usage
2. **Helpers**: Singleton classes with static methods
3. **IPC**: Use descriptive event names with underscores (`check_for_updates`)
4. **API Calls**: Always through `Connection.request()` with BaseUrl
5. **Permissions**: Check via `aplication.json` → `Permisos` and `TypeUsers`
6. **Console Logs**: Extensive logging with emojis (🔍, ✅, ❌) for visibility

## Common Patterns

**Fetching Data (Frontend)**:
```javascript
import Connection from '@/helpers/Connection';
import BaseUrl from '@/helpers/baseUrl';

const response = await Connection.request('GET', BaseUrl.getUrl('api/products'));
if (response.success) {
  // Use response.data
}
```

**Filtering by Negocio (Backend)**:
```php
use App\Helpers\CurrentApp;

// Get current app_id from App-Key header
$appId = CurrentApp::getApp();

// Filter query by current negocio
$data = DB::table('table_name')
    ->where('app_id', $appId)
    ->get();
```

**IPC Communication**:
```javascript
// Renderer → Main
const { ipcRenderer } = require('electron');
ipcRenderer.send('check_for_updates');

// Main → Renderer
mainWindow.webContents.send('update_available', updateInfo);
```

## Known Issues & Gotchas

- **Cache**: Main window clears cache on startup ([src/main/index.js](../src/main/index.js:45-52))
- **Node Integration**: Enabled (security consideration for production)
- **GitHub Token**: Exposed in source - rotate periodically
- **32-bit Builds**: Jenkins targets ia32 architecture
- **Shift System**: Must be active to access most features

## Testing & Debugging

- **DevTools**: Enabled in development mode
- **Electron DevTools**: Auto-installed in dev
- **Version Tracking**: `VersionTracker.js` logs version usage
- **Test Updater**: `npm run test-updater` for update testing

## External Integrations

- **Uber Eats**: [src/renderer/uber-eats/](../src/renderer/uber-eats/), config in [app/UberEatsConfig.php](../app/UberEatsConfig.php)
- **SII (Chile Tax)**: Facturas/boletas via [app/Factura.php](../app/Factura.php)
- **Payment Methods**: Configured per-location in Modules settings (Sodexo, Amipass, Rappi, etc.)

## When Adding Features

1. Check if module/submodule needs to be added to `aplication.json`
2. Update Vuex store if managing new state
3. Add IPC handlers if Electron main process interaction needed
4. Ensure turno (shift) guards don't block legitimate access
5. Respect permission system in `aplication.json` → `Permisos`
6. Test with different Serial configurations (different franchises)
