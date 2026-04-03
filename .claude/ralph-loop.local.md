---
active: true
iteration: 1
session_id: 
max_iterations: 0
completion_promise: "ALL BACKLOG ITEMS ADDRESSED"
started_at: "2026-04-03T06:38:28Z"
---

You are running inside a Ralph loop. Your job is to iteratively work through all backlog items in mcp-server/docs/BACKLOG.md until every item is marked Done.

WORKFLOW PER ITERATION:

1. READ STATE: Check mcp-server/docs/BACKLOG.md for items NOT marked Done/[x]. If all items are marked Done, output <promise>ALL BACKLOG ITEMS ADDRESSED</promise> to complete the loop.

2. CONTEXT CHECK: Before starting work, check your context utilization. If it is at or above 35%, output the following EXACTLY (no other text) to trigger compaction:
   /compact

3. PICK ONE ITEM: Select the highest priority unmitigated item (P0 > P1 > P2 > P3).

4. IMPLEMENT: Use the backlog-implementer skill workflow:
   - Read source files relevant to the item
   - Implement the minimal fix described in the backlog
   - Run: npm test (in mcp-server/ directory) if tests exist
   - Stage relevant files only and commit with conventional commit message including the issue ID
   - Example: git commit -m "fix(validation): handle Zod v4 issues field (#1)"

5. HALLUCINATION CHECK: After each commit, invoke the hallucination-checker agent on the changed files:
   - Use the Task tool or Agent tool to run hallucination-checker on the committed files
   - If the checker finds new issues, append them to mcp-server/docs/BACKLOG.md as new backlog items (P1 or P2 as appropriate)

6. MARK DONE: Update the item in mcp-server/docs/BACKLOG.md — check all its acceptance criteria checkboxes and add "**Status: Done**" line.

7. LOOP: The stop hook will feed this same prompt back. On the next iteration, pick the next unmitigated item and repeat.

IMPORTANT RULES:
- Work in /Users/alyshialedlie/code/is-internal/firefly-finance/mcp-server/
- One backlog item per iteration
- Never mark an item Done unless all acceptance criteria are checked
- Never output <promise>ALL BACKLOG ITEMS ADDRESSED</promise> unless every single item in the backlog is marked Done
- Use git commits inside mcp-server/ submodule (cd mcp-server && git commit)
- If an item requires a design decision (marked "Decision Required"), skip it and note it as deferred
- Run npm test before committing if package.json has a test script that works
