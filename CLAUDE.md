# Firefly Finance Project Guidelines

## Overview
This is a monorepo containing a Firefly III universal AI bridge (MCP server) as a submodule, plus the parent Firefly Finance project.

## Code Quality Standards

### MCP Server (`mcp-server/`)
The MCP server is the primary focus for AI integration. Recent improvements (v3.0.0):

#### Architecture & Error Handling
- **Error Surfaces**: All error responses use `toMCPErrorResult()` utility (defined in `src/errors.js`)
- **Session Management**: SSE transports use session-keyed Map to prevent connection overwrites
  - Cleanup handlers are attached to both `close` and `error` events
  - Failed `mcpServer.connect()` calls are caught; sessions are cleaned up in finally block
- **Tool Not Found**: Returns graceful MCP error response (not exception); includes available tools list

#### Resource Metadata & Logging
- **Metadata Coverage**: Phase 2 incomplete (54/66 tools catalogued)
  - Missing metadata warnings are logged once per tool per session (using `warnedMissingMetadata` Set)
  - Prevents console spam on repeated calls to uncatalogued tools
- **Token Cost Estimation**: `estimateTokenCost()` in `src/resource-metadata.js`
  - Returns 0 for tools without metadata; warns non-repeatedly

#### Schema & Validation
- **JSON Schema Defaults**: Removed misleading `default:` properties (MCP SDK ignores them)
  - Defaults applied in handler logic (e.g., `args.limit || 10`)
  - Documentation updated to reflect true behavior
- **Validation**: Zod validation on `index.enhanced.js`; graceful error responses for invalid input

### Parent Repo (PHP/Laravel)
- Standard Laravel API controller patterns
- Minor tweaks in recent commits focused on response formatting and error handling

## Working with the Submodule

### Pulling Updates
```bash
git submodule update --remote mcp-server
cd mcp-server && npm ci && npm test
```

### Making Changes
1. **In `mcp-server/`**: Create PR in submodule repo; merge and tag
2. **In parent**: Update submodule reference: `git add mcp-server` (auto-updates `.gitmodules`)
3. **Commit**: Use conventional format: `bump: mcp-server submodule to <tag>`

### Testing
```bash
cd mcp-server
npm test              # Unit tests
npm run lint          # (if configured)
npx tsc --noEmit      # Type check (if TypeScript present)
```

## Recent Fixes (Session Latest)

### Code Quality Improvements
| Issue | Severity | Fix |
|-------|----------|-----|
| Session map leaks on error | MEDIUM | Error cleanup handlers + try-catch in SSE route |
| Tool metadata spam warnings | MEDIUM | Warn-once tracking with Set |
| Duplicate error formatting | HIGH | Unified via `toMCPErrorResult()` utility |

### Files Modified
- `mcp-server/index.js` — Session cleanup, error unification
- `mcp-server/index.enhanced.js` — Session cleanup
- `mcp-server/src/resource-metadata.js` — Warn-once implementation

## Development Workflow

### Adding a New Tool
1. Define in `mcp-server/src/tools/<category>.js`
2. Register in `mcp-server/src/tools/registry.js`
3. Add metadata to `src/resource-metadata.js` (Phase 2 requirement)
4. Update `docs/API.md` and `docs/IMPLEMENTATION_STATUS.md`

### Debugging
- **Stdio mode**: `node index.js` (outputs debug info to stderr)
- **SSE mode**: `PORT=3000 node index.js` (requires HTTP client)
- **Logs**: Check `src/config.js` for `DEBUG` env var support

## Known Limitations

### Phase 2 Coverage
- 54 of 66 tools still need full metadata (resource hints, token estimates)
- These tools work but return estimated tokens = 0
- Track progress in `docs/IMPLEMENTATION_STATUS.md`

### Rate Limiting
- Firefly III API now enforces 3-tier rate limiting (April 2026):
  - `api-read`: 120 req/min (all GET endpoints)
  - `api-write`: 60 req/min (POST/PUT/DELETE — defined, ready for per-route use)
  - `api-analytics`: 20 req/min (chart, insight, summary endpoints)
- Limits configurable via `API_RATE_LIMIT_READ`, `API_RATE_LIMIT_WRITE`, `API_RATE_LIMIT_ANALYTICS` env vars
- Scoped per authenticated user ID or client IP
- Laravel's `throttle` middleware automatically emits `X-RateLimit-*` and `Retry-After` headers
- MCP server `rateLimitHint` values remain as conservative client-side recommendations via p-queue

### Caching (PHP API)
- Default cache driver: `redis` (configurable via `CACHE_DRIVER` env var; `file` still supported)
- Tag-based invalidation via `TaggableCache` helper (`app/Support/TaggableCache.php`)
- Tags: `config`, `preferences`, `exchange_rates`, `user:{id}`
- Exchange rate changes flush only `exchange_rates` tag (not entire cache)
- Graceful fallback: file driver skips tagging (`flushTags()` is a no-op — stale data persists until natural expiry)

### Cursor Pagination (PHP API)
- Transaction list endpoints support optional `?cursor=` parameter
- Returns `meta.cursor_pagination` with `next_cursor`, `prev_cursor`, `has_more`
- Omitting `?cursor=` uses existing offset pagination (backward compatible)
- Implemented on `Account/ListController@transactions` via `GroupCollector::getCursorPaginatedGroups()`

### Query Builder (PHP API)
- Account and Budget list endpoints support Spatie QueryBuilder filters:
  - `?filter[name]=...` (partial match), `?filter[active]=1` (exact), `?filter[iban]=...` (Account only)
  - `?sort=name`, `?sort=-name` (DB-level sorts)
- Falls back to existing repository path when no `?filter[]` params present

## Environment Variables

```bash
FIREFLY_URL=http://localhost:8080        # Firefly III instance
FIREFLY_TOKEN=your-api-token             # API token (keep secret!)
PORT=3000                                 # Optional: enable SSE mode (default: stdio only)
DEBUG=*                                   # Optional: enable debug logging
NODE_ENV=production                       # Suppress dev warnings
API_RATE_LIMIT_READ=120                   # Read endpoint rate limit (req/min)
API_RATE_LIMIT_WRITE=60                   # Write endpoint rate limit (req/min)
API_RATE_LIMIT_ANALYTICS=20               # Analytics endpoint rate limit (req/min)
CACHE_DRIVER=redis                        # Cache driver (redis recommended; file supported)
```

## Testing Standards

- **Unit Tests**: Run with `npm test` (Jest)
- **Integration**: Not currently automated; test with live Firefly III instance
- **Coverage**: Focus on error handling paths and validation

## References

- [Firefly III API Docs](https://api-docs.firefly-iii.org/)
- [MCP Protocol Spec](https://modelcontextprotocol.io/docs/)
- [Error Handling Guide](mcp-server/docs/ERRORS.md) (if present)
