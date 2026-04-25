# Quick Start Guide - Admin Portal

## 🚀 Get Started in 5 Minutes

### Step 1: Run Migrations
```bash
# From the admission_portal root directory
php artisan migrate:fresh --seed
# or if using Laradock:
cd laradock && docker compose exec -T workspace php artisan migrate:fresh --seed
```

### Step 2: Create Admin Account
```bash
php artisan tinker

# Inside tinker:
>>> use App\Models\User;
>>> $admin = User::factory()->create(['email' => 'admin@example.com', 'password' => bcrypt('admin123')])->assignRole('admin');
>>> exit
```

### Step 3: Login
- Open your browser
- Go to: `http://admission-portal.test/admin/login`
- Email: `admin@example.com`
- Password: `admin123`

### Step 4: Explore Dashboard
- View statistics (active exams, applications count)
- Browse exams with live search
- Filter applications by status
- See real-time pagination

---

## 🔗 API Usage

### Test Exam List (with cURL)
```bash
# Get token (if using Sanctum token auth)
curl -X GET http://admission-portal.test/api/exams \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Filter Examples
```bash
# Get active exams only
?filter[status]=active

# Search for specific exam
?filter[search]=SSC

# Get applications for exam (by ULID)
GET /api/applications?filter[exam_id]=01ARZ3NDEKTSV4RRFFQ69G5FAV

# Custom pagination
?per_page=50

# Sort by newest first
?sort=-created_at
```

---

## 📊 Database Queries (Manual Testing)

### Check Created Tables
```bash
php artisan tinker
>>> DB::table('exams')->count();
>>> DB::table('applications')->count();
```

### Create Test Data
```bash
php artisan tinker
>>> $exam = Exam::factory()->create(['name' => 'SSC 2026']);
>>> Application::factory(5)->create(['exam_id' => $exam->id]);
```

---

## 🎯 Directory Reference

| File | Purpose |
|------|---------|
| `app/Models/Exam.php` | Exam model with relationships |
| `app/Models/Application.php` | Application model |
| `resources/views/pages/admin-login.blade.php` | Login UI (Volt) |
| `resources/views/pages/admin-dashboard.blade.php` | Dashboard UI (Volt) |
| `app/Http/Controllers/Api/ExamIndexController.php` | Exam API endpoint |
| `app/Http/Controllers/Api/ApplicationIndexController.php` | Application API endpoint |
| `routes/web.php` | Web routes (login & dashboard) |
| `routes/api.php` | API routes |

---

## 🐛 Troubleshooting

### "Model not found" error?
```bash
# Clear autoloader cache
composer dump-autoload
```

### "Table doesn't exist"?
```bash
# Run migrations again
php artisan migrate --force
```

### "Unauthorized" on API route?
- Ensure user has `admin` or `moderator` role
- Check `config/permission.php` for role configuration

### Volt components not rendering?
```bash
# Clear view cache
php artisan view:clear
```

---

## 📱 UI Features Explained

### Dashboard Stats Cards
- **Total Exams**: All exams in database
- **Active Exams**: Exams with status = "active"
- **Total Applications**: All submitted applications
- **Pending Applications**: Applications awaiting approval

### Exam Filters
- **Search**: Real-time search by exam name
- **Status**: Filter by draft/active/closed

### Application List
- Shows applicant name, email, exam name
- Color-coded status badges (yellow=submitted, gray=other)
- Latest applications displayed first

---

## ✨ Advanced Features

### Authentication Flow
1. User visits `/admin/login`
2. Enters credentials
3. System verifies role (admin/moderator)
4. Redirects to `/admin/dashboard` on success
5. Session maintained via `auth:sanctum`

### API Authentication
```php
// Protected by:
Route::middleware(['auth:sanctum', 'role:admin|moderator'])->group(function () {
    // endpoints here
});
```

### QueryBuilder Features
```php
// Built-in capabilities:
- Filtering: ?filter[status]=active&filter[search]=SSC
- Sorting: ?sort=name or ?sort=-created_at
- Pagination: ?page=2&per_page=50
- All combined: ?filter[status]=active&sort=name&page=1&per_page=25
```

---

## 📞 Support

For detailed documentation, see: `ADMIN_PORTAL_IMPLEMENTATION.md`

Main components:
- Migrations: `database/migrations/2026_04_25_*.php`
- Models: `app/Models/{Exam,Application}.php`
- Tests: `tests/Feature/Models/{ExamTest,ApplicationTest}.php`

---

Last Updated: April 25, 2026

