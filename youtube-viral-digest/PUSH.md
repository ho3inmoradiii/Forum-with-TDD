# Push this package to the dedicated GitHub repo

Target (created): https://github.com/ho3inmoradiii/youtube-viral-digest

The cloud agent cannot push to this repo (GitHub App only has access to Forum-with-TDD). Push once from your machine:

```bash
# Option A — from this folder after cloning Forum-with-TDD on branch cursor/youtube-viral-digest-0b54
cd youtube-viral-digest
rm -rf .git
git init -b main
git add -A
git commit -m "Initial commit: YouTube viral digest automation scaffold"
git remote add origin https://github.com/ho3inmoradiii/youtube-viral-digest.git
git push -u origin main
```

Recommended rename (GitHub → Settings → General → Repository name):
`youtube-viral-digest` (drop the leading `-`), then update the Automation repo field to match.

Also grant **Cursor GitHub App** access to this repo (GitHub → Settings → Applications → Cursor → Repository access) so future cloud runs can commit digests/posts.
