# YouTube Viral Digest

Cloud Cursor Automation repo: twice daily (06:00 and 18:00), pick topics from `config/topics.yaml`, fetch top YouTube videos + transcripts, write Persian digests, then draft LinkedIn-style posts matching `config/voice-guide.md`.

## Layout

```
config/topics.yaml      # dynamic topics (default + per-date schedule)
config/voice-guide.md   # viral post pattern
digests/YYYY-MM-DD-HH/  # transcripts + Persian summaries
posts/YYYY-MM-DD-HH/    # generated social posts
AUTOMATION.md           # Cursor Automation prompt + setup
```

## Change topics for a day

Message your agent:

> فردا موضوعات: Feature Creep، Senior Engineer، Clean Code

It updates `config/topics.yaml` under `schedule."<date>"` and pushes. The next automation run reads that file.

## Promote to its own GitHub repo

Cloud agent tokens cannot create new repos. Do this once:

1. On GitHub, create empty private repo `ho3inmoradiii/youtube-viral-digest` (no README).
2. From this folder:

```bash
cd youtube-viral-digest
git init -b main
git add -A
git commit -m "Initial commit: YouTube viral digest automation scaffold"
git remote add origin https://github.com/ho3inmoradiii/youtube-viral-digest.git
git push -u origin main
```

3. Point the Cursor Automation at that repo (see [AUTOMATION.md](AUTOMATION.md)).

Until then, the automation can target `Forum-with-TDD` and only write under `youtube-viral-digest/`.
