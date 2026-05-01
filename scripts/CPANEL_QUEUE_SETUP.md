# cPanel Queue Setup (Required for Admit Card Emails)

Admit card emails are dispatched to a **database queue** (`jobs` table) for reliable bulk sending.  
On cPanel, there are no persistent daemon workers — instead, the Laravel scheduler triggers a  
`queue:work --stop-when-empty` process every minute via a **single cron job**.

---

## One-Time Setup Steps

### 1. Run Migrations (already done if app is set up)
```bash
php artisan migrate
```
→ Creates the `jobs` and `failed_jobs` tables.

### 2. Set Environment Variables in `.env`
```dotenv
QUEUE_CONNECTION=database
ADMIT_CARD_MAIL_DRY_RUN=false   # Set to true to only simulate (log) without sending
```

### 3. Add cPanel Cron Job

In cPanel → **Cron Jobs**, add ONE entry:

| Field    | Value                                                                                  |
|----------|----------------------------------------------------------------------------------------|
| Minute   | `*`                                                                                    |
| Hour     | `*`                                                                                    |
| Day      | `*`                                                                                    |
| Month    | `*`                                                                                    |
| Weekday  | `*`                                                                                    |
| Command  | `cd /home/<username>/public_html && /usr/local/bin/php artisan schedule:run >> /dev/null 2>&1` |

Replace `/home/<username>/public_html` with your actual Laravel root directory.  
Use `/usr/local/bin/php` or whichever PHP binary cPanel provides (check via `which php` in SSH).

This single cron drives everything: the schedule automatically fires `queue:work --stop-when-empty`  
every minute to drain any queued emails.

---

## How It Works

```
Admin clicks "Send Admit Cards"
        │
        ▼
Mail::queue(AdmitCardMail)  ──►  jobs table  (instant, no delay)
                                      │
                                      │ (every ~1 min)
                                      ▼
                             cron: schedule:run
                                      │
                                      ▼
                          queue:work --stop-when-empty
                                      │
                                      ▼
                           Emails delivered via SMTP
```

---

## Dry Run Mode

When `ADMIT_CARD_MAIL_DRY_RUN=true`, no emails are sent — the controller only logs the  
intended recipients. Use this to verify recipient selection before going live.

---

## Troubleshooting

| Symptom | Cause | Fix |
|---------|-------|-----|
| Emails queued but not delivered | No cron / cron not set up | Add the cron job above |
| Jobs stuck in `jobs` table | Worker errors | Check `storage/logs/laravel.log` |
| Jobs in `failed_jobs` table | Mail config wrong / SMTP error | Run `php artisan queue:failed` and `queue:retry all` after fixing SMTP |
| `dry_run` still `true` | `.env` not updated or cached | Run `php artisan config:clear` after editing `.env` |

### Check pending jobs (SSH):
```bash
php artisan queue:listen --timeout=60
# or manually process once:
php artisan queue:work --stop-when-empty
```

### Retry failed jobs:
```bash
php artisan queue:retry all
php artisan queue:work --stop-when-empty
```

