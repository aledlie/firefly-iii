# MCP Server Submodule Setup

Enhanced Firefly III MCP Server registered as a git submodule with fork-based development.

## Repository Structure

```
firefly-finance/
├── mcp-server/                 # Submodule → aledlie/mcp-server-firefly-iii (enhanced branch)
│   ├── index.enhanced.js       # Enhanced MCP server entry point
│   ├── src/
│   │   ├── config.js           # Configuration & env var handling
│   │   ├── errors.js           # Structured error handling (toMCPErrorResult)
│   │   ├── rate-limiter.js     # Client-side rate limiting via p-queue
│   │   ├── resource-metadata.js # Token cost, caching, rate limit hints
│   │   ├── response-cache.js   # Response caching layer
│   │   ├── retry.js            # Retry logic for transient failures
│   │   ├── validation.js       # Zod-based input validation
│   │   └── tools/              # Tool definitions (6 categories)
│   ├── docs/
│   │   ├── roadmap/            # Implementation status & Phase 2 plans
│   │   ├── BACKLOG.md          # Tracked backlog items
│   │   └── TESTING.md          # Test documentation
│   ├── CLAUDE_INTEGRATION.md   # Usage guide for Claude
│   ├── ENHANCEMENTS.md         # Technical overview
│   └── README_CLAUDE.md        # Quick reference
└── .gitmodules                 # Submodule configuration
```

## Remotes

**mcp-server submodule:**
- **origin:** `https://github.com/fabianonetto/mcp-server-firefly-iii.git` (upstream)
- **fork:** `https://github.com/aledlie/mcp-server-firefly-iii.git` (your fork)

**Current branch:** `enhanced` (tracks fork/enhanced)

## Workflow

### Clone This Repository
```bash
# HTTPS (works everywhere)
git clone --recurse-submodules https://github.com/aledlie/firefly-finance.git

# Or SSH if SSH key is configured
git clone --recurse-submodules git@github.com:aledlie/firefly-finance.git

# If already cloned, initialize submodules:
git submodule update --init --recursive
```

### Initial Remote Setup
```bash
cd mcp-server

# Add fork remote if not using fork-based clone
git remote add fork https://github.com/aledlie/mcp-server-firefly-iii.git
# Or for SSH: git remote add fork git@github.com:aledlie/mcp-server-firefly-iii.git

# (origin automatically points to upstream from .gitmodules)
```

### Update Submodule
```bash
cd mcp-server
git fetch fork
git pull fork enhanced
```

### Make Changes to MCP Server
```bash
cd mcp-server
git checkout enhanced
git add <files>
git commit -m "feat: ..."
git push fork enhanced
```

### Sync with Upstream (Optional)
To pull latest upstream master and merge enhancements:

```bash
cd mcp-server

# Fetch upstream
git fetch origin

# Create temporary branch from upstream master
git checkout -b sync-upstream origin/master

# Merge enhanced changes
git merge enhanced

# Resolve conflicts if needed
git add .
git commit -m "chore: merge enhanced into upstream master"

# Push to fork for review
git push fork sync-upstream

# On GitHub: Open PR from sync-upstream → upstream master
```

Then the upstream maintainer can review and merge your enhancements.

### Update Submodule Reference in Parent Repo
After commits in the submodule:

```bash
# From firefly-finance root
cd mcp-server
git pull fork enhanced

# Parent repo now sees the updated submodule commit
cd ..
git add mcp-server
git commit -m "chore: update mcp-server submodule to latest enhanced"
git push origin main
```

## Branch Strategy

| Branch | Purpose | Push To | Track |
|--------|---------|---------|-------|
| `enhanced` | Claude ecosystem enhancements | fork | fork/enhanced |
| `master` | Upstream source (read-only) | — | origin/master |
| `sync-upstream` | For PRs back to upstream | fork | — |

## Key Features in Enhanced Branch

### Core Infrastructure

- **Zod-based validation** (`src/validation.js`) — Runtime input safety, auto JSON schema generation
- **Structured errors** (`src/errors.js`) — Unified `toMCPErrorResult()` utility, error codes with retry guidance
- **Resource metadata** (`src/resource-metadata.js`) — Token cost estimation, cacheability hints, warn-once tracking (12/66 tools with full metadata)
- **Response caching** (`src/response-cache.js`) — Caching layer for repeated queries
- **Rate limiting** (`src/rate-limiter.js`) — Client-side rate limiting via p-queue (matches server-side tiers)
- **Retry logic** (`src/retry.js`) — Transient failure recovery
- **Tool organization** (`src/tools/`) — 6 categories with dependency resolver and workflow optimization

### PHP API Enhancements (Parent Repo)

- **Tiered rate limiting** — 3-tier Laravel throttle: `api-read` (120/min), `api-write` (60/min), `api-analytics` (20/min); configurable via env vars; scoped per user ID or IP
- **Redis cache with tag-based invalidation** — Default driver switched to Redis; `TaggableCache` helper with domain-specific tags (`config`, `preferences`, `exchange_rates`, `user:{id}`); graceful fallback for file driver
- **Cursor-based pagination** — Optional `?cursor=` on transaction endpoints; returns `next_cursor`/`prev_cursor` metadata; backward compatible with existing offset pagination
- **Spatie QueryBuilder** — Account and Budget list endpoints support `?filter[name]=`, `?filter[active]=`, `?filter[iban]=` (Account only), and DB-level `?sort=` params; falls back to existing repository path when no filters present

### Quality & Security

- **Hallucination vulnerability fixes** — Removed fabricated tool names, fixed tool name mismatches, corrected false provenance claims, marked incomplete enum subsets
- **Code review fixes** — Session map leak prevention, warn-once metadata tracking, unified error surfaces
- **Documentation** (`docs/roadmap/`) — Implementation status, Phase 2 roadmap, known gaps

## Environment Variables

```bash
# MCP Server
FIREFLY_URL=http://localhost:8080        # Firefly III instance
FIREFLY_TOKEN=your-api-token             # API token (keep secret!)
PORT=3000                                # Optional: enable SSE mode (default: stdio only)
DEBUG=*                                  # Optional: enable debug logging
NODE_ENV=production                      # Suppress dev warnings

# PHP API (Parent Repo)
API_RATE_LIMIT_READ=120                  # Read endpoint rate limit (req/min, default: 120)
API_RATE_LIMIT_WRITE=60                  # Write endpoint rate limit (req/min, default: 60)
API_RATE_LIMIT_ANALYTICS=20              # Analytics endpoint rate limit (req/min, default: 20)
CACHE_DRIVER=redis                       # Cache driver (redis recommended; file supported)
```

## Running Enhanced Server

### Via NPM
```bash
cd mcp-server

# Enhanced server (stdio)
npm run start:enhanced

# Web API mode
npm run start:web  # http://localhost:3000
```

### Via Node
```bash
NODE_ENV=test node index.enhanced.js
```

## Testing

```bash
# Install dependencies
npm install

# Run tests
npm test

# Syntax check
node -c index.enhanced.js
```

## Contributing Back to Upstream

To propose enhancements to the original project:

1. **Ensure `sync-upstream` branch is ready:**
   ```bash
   cd mcp-server
   git checkout sync-upstream
   git push fork sync-upstream
   ```

2. **Open PR on upstream repository:**
   - Fork: `aledlie/mcp-server-firefly-iii/sync-upstream`
   - Base: `fabianonetto/mcp-server-firefly-iii/master`
   - Include:
     - Summary of enhancements
     - Reference to `ENHANCEMENTS.md`
     - Testing evidence

3. **Track PR status:**
   - GitHub PR page or CLI: `gh pr view <url>`

## Roadmap Visibility

See `mcp-server/docs/roadmap/` for:
- **CHANGELOG.md** — All completed work (Phase 1 + Phase 2)
- **PHASE_2_ROADMAP.md** — Phase 2 complete; deferred items listed for Phase 3
- **KNOWN_GAPS.md** — Unaddressed items + future work
- **BACKLOG.md** — Tracked backlog items (`mcp-server/docs/BACKLOG.md`)

## Troubleshooting

### Submodule not cloned
```bash
git submodule update --init --recursive
```

### Submodule branch out of sync
```bash
cd mcp-server
git fetch fork
git reset --hard fork/enhanced
```

### Detached HEAD in submodule
```bash
cd mcp-server
git checkout enhanced
git pull fork enhanced
```

### Fork remote not found
```bash
cd mcp-server
git remote add fork https://github.com/aledlie/mcp-server-firefly-iii.git
git fetch fork
```

### Authentication issues
If using SSH and key is not configured:
```bash
# Option 1: Use HTTPS instead
git remote set-url fork https://github.com/aledlie/mcp-server-firefly-iii.git

# Option 2: Configure SSH key
ssh-keygen -t ed25519 -C "your_email@example.com"
ssh-add ~/.ssh/id_ed25519
# Add public key to GitHub settings
```

## References

- **Upstream:** https://github.com/fabianonetto/mcp-server-firefly-iii
- **Fork:** https://github.com/aledlie/mcp-server-firefly-iii
- **MCP Protocol:** https://modelcontextprotocol.io/
- **Firefly III API:** https://api-docs.firefly-iii.org/

---

For current commit references:
```bash
cd mcp-server && git log --oneline -1
cd .. && git log --oneline -1
```
