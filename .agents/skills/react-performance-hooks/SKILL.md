---
name: react-performance-hooks
description: Triggers when writing React functional components or Custom Hooks. Enforces strict memoization, prevents unnecessary re-renders, and eliminates memory leaks.
---

# ReactJS Performance Standards

## When to use this skill
- Trigger when creating, refactoring, or optimizing React components, Custom Hooks, state management, or effect lifecycles.

## CRITICAL PERFORMANCE CONSTRAINTS (STRICTLY ENFORCED)
- **STRICT MEMOIZATION:**
  - Heavy calculations or complex data transformations MUST be wrapped in `useMemo`.
  - Any callback/handler passed as a prop to child components MUST be wrapped in `useCallback` to prevent breaking child memoization (`React.memo`).
- **MANDATORY CLEANUP:** Every `useEffect` that establishes subscriptions, event listeners, intervals, timeouts, or abortable async requests (AbortController) MUST return a cleanup function to eliminate memory leaks.
- **AVOID PROP DRILLING:** Never drill props deeper than 3 component layers. Enforce Component Composition (`children` pattern) or React Context API / state stores.
- **STABLE DEPENDENCY ARRAYS:** Declare exhaustive dependencies for `useEffect`, `useMemo`, and `useCallback` without omitting references. Avoid inline object/array references inside dependency arrays.
- **NO TERMINAL CALLS:** Extract the current system date directly from context; do NOT execute terminal/bash commands to fetch the date.

## Output Requirements
- Provide concise, production-ready React (JavaScript/TypeScript) code.
- Omit basic introductory concepts and conversational fluff.
- Prepend every component or custom hook with the mandatory header comment:
  `// [YYYY-MM-DD] - @author: Kelvin - [Giải thích cơ chế chống re-render của hook/component]`

## Expected Output Example

```javascript
// 2026-06-17 - @author: Kelvin - Custom hook debounce giá trị input, sử dụng useEffect cleanup để tránh memory leak và giảm request thừa
import { useState, useEffect } from 'react';

export const useDebounce = (value, delay = 500) => {
    const [debouncedValue, setDebouncedValue] = useState(value);

    useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedValue(value);
        }, delay);

        // Cleanup function hủy timeout cũ nếu user tiếp tục gõ
        return () => {
            clearTimeout(handler);
        };
    }, [value, delay]);

    return debouncedValue;
};