# MCP Server Submodule Setup

Enhanced Firefly III MCP Server registered as a git submodule with fork-based development.

## Repository Structure

```
firefly-finance/
├── mcp-server/                 # Submodule → aledlie/mcp-server-firefly-iii (enhanced branch)
│   ├── index.enhanced.js       # Enhanced MCP server entry point
│   ├── src/
│   │   ├── validation.js       # Zod-based input validation
│   │   ├── errors.js           # Structured error handling
│   │   └── resource-metadata.js # Token cost, caching, rate limits
│   ├── docs/roadmap/           # Implementation status & Phase 2 plans
│   ├── CLAUDE_INTEGRATION.md   # Usage guide for Claude
│   ├── ENHANCEMENTS.md         # Technical overview
│   └── README_CLAUDE.md        # Quick reference
└── .gitmodules                 # Submodule configuration
```

## Remotes

**mcp-server submodule:**
- **origin:** `https://github.com/fabianonetto/mcp-server-firefly-iii.git` (upstream)
- **fork:** `git@github.com:aledlie/mcp-server-firefly-iii.git` (your fork)

**Current branch:** `enhanced` (tracks fork/enhanced)

## Workflow

### Clone This Repository
```bash
git clone --recurse-submodules git@github.com:aledlie/firefly-finance.git
# or if already cloned:
git submodule update --init --recursive
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

✅ **Zod-based validation** (`src/validation.js`)
- Runtime input safety
- Auto JSON schema generation

✅ **Structured errors** (`src/errors.js`)
- Error codes with retry guidance
- Context preservation

✅ **Resource metadata** (`src/resource-metadata.js`)
- Token cost estimation
- Cacheability hints
- Rate limit tracking

✅ **Tool organization** (6 categories)
- Dependency resolver
- Workflow optimization

✅ **Documentation** (`docs/roadmap/`)
- Implementation status
- Phase 2 roadmap
- Known gaps

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

# Run tests (Phase 1 complete; Phase 2 tests pending)
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
- **IMPLEMENTATION_STATUS.md** — Phase 1 complete items
- **PHASE_2_ROADMAP.md** — Next priorities (pagination, rate limiting, etc.)
- **KNOWN_GAPS.md** — Unaddressed items + future work

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

### Authentication issues
```bash
# Ensure SSH key configured
ssh -T git@github.com

# Or use HTTPS (less recommended)
git remote set-url fork https://github.com/aledlie/mcp-server-firefly-iii.git
```

## References

- **Upstream:** https://github.com/fabianonetto/mcp-server-firefly-iii
- **Fork:** https://github.com/aledlie/mcp-server-firefly-iii
- **MCP Protocol:** https://modelcontextprotocol.io/
- **Firefly III API:** https://api-docs.firefly-iii.org/

---

**Setup Date:** April 2, 2026  
**Enhanced Branch Commit:** 8c8fd1b (refactor: convert ESM to CommonJS)  
**Parent Commit:** 25e524e737 (feat: register mcp-server as submodule)
