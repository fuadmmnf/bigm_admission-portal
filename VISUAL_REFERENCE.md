# 📋 Admin Portal - Visual Reference Guide

## 🎨 UI/UX Overview

### Login Page Layout
```
┌────────────────────────────────────────────────────────┐
│                                                         │
│         ╔═══════════════════════════════════╗           │
│         ║                                   ║           │
│         ║       Admission Portal            ║           │
│         ║                                   ║           │
│         ║  ┌─────────────────────────────┐  ║           │
│         ║  │ Email: ________________      │  ║           │
│         ║  │                               │  ║           │
│         ║  │ Password: ________________   │  ║           │
│         ║  │                               │  ║           │
│         ║  │ ☑ Remember me              │  ║           │
│         ║  │                               │  ║           │
│         ║  │ [  Sign In  ]               │  ║           │
│         ║  │                               │  ║           │
│         ║  └─────────────────────────────┘  ║           │
│         ║                                   ║           │
│         ║ Admin/Moderator access only     ║           │
│         ║                                   ║           │
│         ╚═══════════════════════════════════╝           │
│                                                         │
└────────────────────────────────────────────────────────┘

Colors:
- Gradient: blue-50 to indigo-100 (background)
- Primary: indigo-600 (buttons, text)
- Input: gray-300 border, white fill
```

### Dashboard Layout
```
┌──────────────────────────────────────────────────────────────────────┐
│                         Admin Dashboard                       ✕      │
│                    Manage exams and applications              [👤]   │
├──────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐│
│  │   Total     │  │   Active    │  │   Total     │  │  Pending    ││
│  │   Exams     │  │   Exams     │  │ Applications│  │Applications ││
│  │     10      │  │      5      │  │     45      │  │      12     ││
│  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘│
│                                                                       │
├────────────────────────────────┬────────────────────────────────────┤
│                                │                                     │
│        Exams Section           │  Recent Applications Section        │
│                                │                                     │
│  [Create Exam] [↓]             │  ┌──────────────────────────────┐  │
│                                │  │ John Doe                     │  │
│  Search: [___________]         │  │ john@test.com • SSC 2026     │  │
│                                │  │ Status: Submitted ⚠️          │  │
│  Status: [All ▼] [×]          │  └──────────────────────────────┘  │
│                                │  ┌──────────────────────────────┐  │
│  ┌──────────────────────────┐  │  │ Jane Smith                   │  │
│  │ SSC Admission 2026       │  │  │ jane@test.com • HSC 2026     │  │
│  │ SSC • Status: Active ✓   │  │  │ Status: Submitted ⚠️          │  │
│  │ [📊 23 apps]             │  │  └──────────────────────────────┘  │
│  └──────────────────────────┘  │  ┌──────────────────────────────┐  │
│  ┌──────────────────────────┐  │  │ Ahmed Khan                   │  │
│  │ HSC Admission 2026       │  │  │ ahmed@test.com • SSC 2026    │  │
│  │ HSC • Status: Active ✓   │  │  │ Status: Approved ✓           │  │
│  │ [📊 22 apps]             │  │  └──────────────────────────────┘  │
│  └──────────────────────────┘  │                                     │
│                                │  ◀ Prev  1 2 3  Next ▶            │
│  ◀ Prev  1 2 3  Next ▶         │                                     │
│                                │                                     │
└────────────────────────────────┴────────────────────────────────────┘
```

---

## 📊 API Response Examples

### GET /api/exams - Successful Response
```json
HTTP/1.1 200 OK

{
  "data": [
    {
      "id": "01ARZ3NDEKTSV4RRFFQ69G5FAV",
      "name": "SSC Admission 2026",
      "description": "Secondary School Certificate...",
      "category": {
        "id": "01ARZ3NDEKTSV4RRFFQ64G5FAV",
        "name": "SSC",
        "type": "exam"
      },
      "status": "active",
      "start_date": "2026-05-01T00:00:00Z",
      "end_date": "2026-06-30T23:59:59Z",
      "created_at": "2026-04-25T10:30:00Z",
      "updated_at": "2026-04-25T10:30:00Z"
    },
    {
      "id": "01ARZ3NDEKTSV4RRFFQ69G5FAW",
      "name": "HSC Admission 2026",
      "description": "Higher Secondary Certificate...",
      "category": {
        "id": "01ARZ3NDEKTSV4RRFFQ64G5FAW",
        "name": "HSC",
        "type": "exam"
      },
      "status": "active",
      "start_date": "2026-07-01T00:00:00Z",
      "end_date": "2026-08-31T23:59:59Z",
      "created_at": "2026-04-25T10:35:00Z",
      "updated_at": "2026-04-25T10:35:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "per_page": 25,
    "to": 2,
    "total": 2
  }
}
```

### GET /api/applications - Successful Response
```json
HTTP/1.1 200 OK

{
  "data": [
    {
      "id": "01ARZT5EKTSV4RRFFQ69G5FAV",
      "exam_id": "01ARZ3NDEKTSV4RRFFQ69G5FAV",
      "exam_name": "SSC Admission 2026",
      "applicant_name": "John Doe",
      "applicant_email": "john@example.com",
      "applicant_phone": "01700000001",
      "applicant_id_number": "12345678901",
      "status": "submitted",
      "created_at": "2026-04-25T11:00:00Z",
      "updated_at": "2026-04-25T11:00:00Z"
    },
    {
      "id": "01ARZT5EKTSV4RRFFQ69G5FAW",
      "exam_id": "01ARZ3NDEKTSV4RRFFQ69G5FAV",
      "exam_name": "SSC Admission 2026",
      "applicant_name": "Jane Smith",
      "applicant_email": "jane@example.com",
      "applicant_phone": "01700000002",
      "applicant_id_number": "12345678902",
      "status": "approved",
      "created_at": "2026-04-25T11:05:00Z",
      "updated_at": "2026-04-25T11:10:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "per_page": 25,
    "to": 2,
    "total": 2
  }
}
```

### GET /api/exams - Unauthorized Response
```json
HTTP/1.1 401 Unauthorized

{
  "message": "Unauthenticated."
}
```

### GET /api/exams (admin without moderator permission) - Forbidden Response
```json
HTTP/1.1 403 Forbidden

{
  "message": "User does not have the right roles."
}
```

---

## 🔗 API Query Examples

| Request | Result |
|---------|--------|
| `GET /api/exams?filter[status]=active` | Only active exams |
| `GET /api/exams?filter[search]=SSC` | Exams with "SSC" in name |
| `GET /api/exams?sort=name` | Sorted by name (A-Z) |
| `GET /api/exams?sort=-created_at` | Sorted by newest first |
| `GET /api/exams?per_page=50` | 50 items per page |
| `GET /api/exams?page=2` | Get page 2 |
| `GET /api/applications?filter[status]=submitted` | Pending applications |
| `GET /api/applications?filter[exam_id]=ulid123` | Apps for specific exam |
| `GET /api/applications?filter[search]=Ahmed` | Search by applicant name |
| `GET /api/applications?sort=-created_at&per_page=100` | Combined filters |

---

## 💾 Database Query Examples

### Create New Exam
```php
$exam = Exam::create([
    'name' => 'University Entrance Exam 2026',
    'category_id' => 1,
    'description' => 'UE for undergraduates',
    'status' => 'draft',
    'start_date' => now()->addWeeks(2),
    'end_date' => now()->addMonths(1),
]);
```

### Retrieve Exams with Applications Count
```php
$exams = Exam::withCount('applications')
    ->where('status', 'active')
    ->get();
```

### Create Application
```php
$application = Application::create([
    'exam_id' => $exam->id,
    'applicant_name' => 'John Doe',
    'applicant_email' => 'john@test.com',
    'applicant_phone' => '01700000001',
    'applicant_id_number' => '12345678901',
    'status' => 'submitted',
]);
```

### Get Applications for Exam
```php
$exam = Exam::find($examId);
$applications = $exam->applications()
    ->where('status', 'submitted')
    ->paginate(20);
```

### Update Application Status
```php
$application->update(['status' => 'approved']);
```

### Get Statistics
```php
$stats = [
    'total_exams' => Exam::count(),
    'active_exams' => Exam::where('status', 'active')->count(),
    'total_applications' => Application::count(),
    'pending_applications' => Application::where('status', 'submitted')->count(),
    'approved_applications' => Application::where('status', 'approved')->count(),
];
```

---

## 🔐 User Roles & Permissions

### Admin User
```php
$admin = User::find(1);
$admin->assignRole('admin');

// Can access:
// - GET /api/exams
// - GET /api/applications
// - GET /admin/login
// - GET /admin/dashboard
```

### Moderator User
```php
$moderator = User::find(2);
$moderator->assignRole('moderator');

// Can access:
// - GET /api/exams
// - GET /api/applications
// - GET /admin/login
// - GET /admin/dashboard
```

### Regular User
```php
$user = User::find(3);
// NO ROLE

// Can access:
// - GET /admin/login (but will be rejected)
// - CANNOT access /admin/dashboard
// - CANNOT access /api/exams
// - CANNOT access /api/applications
```

---

## 📱 Responsive Breakpoints

**Login Page:**
- Mobile (< 640px): Full-width form
- Tablet (640px - 1024px): Centered, max-width-md
- Desktop (> 1024px): Centered, max-width-md

**Dashboard:**
- Mobile (< 768px): Single column layout
- Tablet (768px - 1024px): Grid-cols-1
- Desktop (> 1024px): Grid-cols-2 (exams left, applications right)

---

## ⚙️ Status Enums

### Exam Statuses
| Status | Meaning |
|--------|---------|
| `draft` | Exam is being prepared, not public |
| `active` | Exam is open for applications |
| `closed` | Exam registration period ended |

### Application Statuses
| Status | Meaning |
|--------|---------|
| `draft` | Application started but incomplete |
| `submitted` | Application submitted, awaiting review |
| `approved` | Application approved by admin/moderator |
| `rejected` | Application rejected |

---

## 🎯 Feature Checklist

```
Authentication:
  ✅ Admin/moderator-only login
  ✅ Role-based access control
  ✅ Session management
  ✅ Email verification (Jetstream)
  ✅ Remember me functionality

Dashboard:
  ✅ Statistics cards
  ✅ Exam management interface
  ✅ Live search
  ✅ Status filtering
  ✅ Application listing
  ✅ Pagination
  ✅ Responsive design

Database:
  ✅ Exams table with relationships
  ✅ Applications table with relationships
  ✅ Soft deletes on both tables
  ✅ ULID identifiers
  ✅ JSON fields for extensibility

API:
  ✅ Exam listing endpoint
  ✅ Application listing endpoint
  ✅ Advanced filtering
  ✅ Sorting capabilities
  ✅ Pagination
  ✅ Role-based access

Testing:
  ✅ Model tests for Exam
  ✅ Model tests for Application
  ✅ Relationship tests
  ✅ ULID generation tests

Documentation:
  ✅ Implementation guide
  ✅ Quick start guide
  ✅ Deliverables summary
  ✅ Visual reference guide
```

---

## 📞 Quick Reference

**Admin Login:** `http://admission-portal.test/admin/login`  
**Admin Dashboard:** `http://admission-portal.test/admin/dashboard`  
**API Exams:** `http://admission-portal.test/api/exams`  
**API Applications:** `http://admission-portal.test/api/applications`  

**Migrations:** `database/migrations/2026_04_25_*.php`  
**Models:** `app/Models/{Exam,Application}.php`  
**Controllers:** `app/Http/Controllers/Api/{Exam,Application}IndexController.php`  
**Resources:** `app/Http/Resources/{Exam,Application}Resource.php`  
**Views:** `resources/views/pages/{admin-login,admin-dashboard}.blade.php`  

---

Created: April 25, 2026

