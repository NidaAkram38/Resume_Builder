# Lynxperio Resume Builder

A clean, professional, ATS-friendly resume builder. No payments, no watermarks — free forever.

## Structure
```
frontend/
  index.html        — Landing page
  templates.html    — Template picker (5 designs)
  builder.html      — Main resume builder (live preview + PDF export)
  login.html        — Sign in
  register.html     — Create account
  dashboard.html    — Manage saved resumes

backend/
  config.php        — DB connection
  login.php         — Auth
  register.php      — Registration
  logout.php        — Sign out
  save_resume.php   — Save to DB
  get_resumes.php   — List user resumes
  delete_resume.php — Delete resume
  download_pdf.php  — PDF via FPDF

database.sql        — MySQL schema
```

## Setup
1. Import `database.sql` into MySQL
2. Edit `backend/config.php` with DB credentials
3. Serve from a PHP-enabled web server (Apache/Nginx)
4. Open `frontend/index.html`

## Templates
- **Classic** — Traditional, Georgia serif, B&W print-ready
- **Executive** — Navy accent, authoritative layout
- **Modern** — Dark header, clean body, tech/startup
- **Minimal** — Garamond, max whitespace, timeless
- **Creative** — Two-column sidebar, bold orange accent

All templates are ATS-optimized: clean HTML structure, no tables, proper heading hierarchy.
