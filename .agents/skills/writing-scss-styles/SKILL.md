---
name: writing-scss-styles
description: Triggers whenever the user asks to write, generate, or refactor SCSS/CSS code. Enforces BEM methodology, performance constraints, and standardized comments.
---

# Writing SCSS Styles Standards

## When to use this skill
- Trigger when creating, editing, or refactoring `.scss` files in the `style/` directory or within component folders.

## CRITICAL CONSTRAINTS (STRICTLY ENFORCED)
- **DO NOT RUN BUILD SCRIPTS:** Never execute terminal or CLI commands to compile SCSS to CSS (e.g., strictly forbid running `sass`, `npm run ...`, `yarn build`, `gulp`, `webpack`, or `npx`).
- **DO NOT MODIFY CSS FILES:** Only create or edit the target `.scss` files. Never create, overwrite, or touch compiled `.css` or `.css.map` files.
- Immediately stop and return the output once the `.scss` code is written/updated.

## Instructions
1. **Header Comment & Date:**
   - Always place the function description and the current date at the very top of each SCSS block:
     `// [YYYY-MM-DD]: [Block/Module description]`
   - *(Note: Use the current date provided in the environment/context; do not trigger bash commands just to get the date).*

2. **SCSS Architecture:**
   - **Configuration Split:** Maintain separate configuration files: `_define.scss` (variables, mixins) and `_responsive.scss` (responsive mixins).
   - **Imports:** Always `@import` (or `@use`) both configuration files inside the component stylesheet.
   - **Media Queries:** Nest `@include respond(breakpoint)` directly inside the relevant selector. Do not write detached media queries outside the selector blocks.

3. **Code Quality & Performance:**
   - **BEM Naming:** Strictly follow the `.block__element--modifier` naming convention.
   - **Max Nesting Depth:** Maximum 3 levels of nesting to avoid specificity issues and optimize browser CSSOM parsing.
   - **Mobile-First:** Use SCSS variables for design tokens (colors, typography) and write media queries following a Mobile-first strategy.

4. **Deliverable Scope:**
   - Output only the requested SCSS code (or relevant template code if explicitly asked). Avoid generating boilerplate or unnecessary files.