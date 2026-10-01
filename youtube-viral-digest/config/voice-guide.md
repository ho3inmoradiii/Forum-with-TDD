# Voice guide — viral LinkedIn / social posts (Persian)

Use this pattern when turning video insights into posts. Match tone and structure; do not copy sample posts verbatim.

## Structure (every post)

1. **Hook (1–2 lines)** — sharp, opinionated, slightly provocative. Contrast or irony works well.
2. **Lived story** — a concrete team/project scene (meeting, teammate, manager, rival). First-person. Specific details beat abstractions.
3. **Twist** — what looked smart failed (or the opposite). Name the real cost (time, maintainability, market, resume theater).
4. **Named concept / quote** — one memorable label or authority (Knuth, Uncle Bob, Reid Hoffman, Martin Fowler, Will Larson, etc.).
5. **Lesson** — one clear takeaway in plain Persian.
6. **CTA** — a direct question inviting comments.
7. **Hashtags** — mix of Persian and English specialty tags (no `#` spam walls; 5–8 is enough). Write them as normal hashtags.

## Tone rules

- Conversational Persian; short paragraphs; occasional emoji only when it earns the line (do not overdo).
- Sound like a senior engineer who has been burned — not a guru lecture.
- Prefer concrete numbers and scenes ("۱۰ دقیقه ادیت", "۶ ماه پولیش دکمه") over vague advice.
- End with honesty: ask the reader to confess or choose sides.

## Theme DNA from reference viral posts (style only)

| Theme | Core tension |
|-------|----------------|
| Premature optimization | Tiny speed win vs. team readability / maintenance cost |
| Feature creep / MVP | Polishing perfection vs. ugly shipping competitor winning the market |
| Resume-driven development | Fancy architecture for the resume vs. tiny real traffic needs |
| Fake seniority | Years of ticket closing vs. business-value thinking |

## Output format per post file

```markdown
# <short title>

<full post body ready to paste>

---
source_videos:
  - title: ...
    url: ...
topic: ...
generated_at: ...
```
