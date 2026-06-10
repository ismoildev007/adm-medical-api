# Medical API — HRM Integration

HRM tizimidan sync qilingan ma'lumotlar uchun **read-only** endpointlar.

**Base URL**: `/api/hrm/`
**Auth header**: `Authorization: Bearer <token>`
**Middleware**: `auth:api`, `role`

> Barcha HRM endpointlar faqat `GET` — ma'lumotlar to'g'ridan-to'g'ri bu API orqali o'zgarmaydi, faqat `php artisan sync:hrm` buyrug'i orqali HRM bazasidan sync bo'ladi.

---

## Response format

Barcha endpointlar `ApiResponse` trait formatida javob qaytaradi:

```json
{
  "success": true,
  "message": "Success",
  "data": { ... }
}
```

Xato holatida:

```json
{
  "success": false,
  "message": "Department topilmadi.",
  "error_code": "NOT_FOUND"
}
```

---

## Pagination (list endpointlar)

`data` ichida pagination ma'lumotlari bo'ladi:

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "current_page": 1,
    "data": [ ... ],
    "first_page_url": "http://localhost:8000/api/hrm/departments?page=1",
    "from": 1,
    "last_page": 5,
    "last_page_url": "...",
    "next_page_url": "...",
    "path": "...",
    "per_page": 20,
    "prev_page_url": null,
    "to": 20,
    "total": 97
  }
}
```

**Query params (barcha list uchun umumiy):**

| Param | Type | Default | Tavsif |
|-------|------|---------|--------|
| `rows` | int | 20 | Sahifadagi yozuvlar soni |
| `page` | int | 1 | Sahifa raqami |

---

## Modellar

### Department object

```json
{
  "id": 12,
  "code": "DEP-001",
  "parent_id": null,
  "department_type_id": 1,
  "sequence": 1,
  "deleted_at": null,
  "created_at": "2026-06-08T10:00:00.000000Z",
  "updated_at": "2026-06-08T10:00:00.000000Z",
  "translations": [
    { "id": 1, "object_id": 12, "language_code": "uz", "name": "Texnologiyalar bo'limi" },
    { "id": 2, "object_id": 12, "language_code": "ru", "name": "Отдел технологий" },
    { "id": 3, "object_id": 12, "language_code": "en", "name": "Technology Department" },
    { "id": 4, "object_id": 12, "language_code": "uc", "name": "Технологиялар бўлими" }
  ]
}
```

### Position object

```json
{
  "id": 5,
  "code": "POS-005",
  "position_type_id": 2,
  "deleted_at": null,
  "created_at": "2026-06-08T10:00:00.000000Z",
  "updated_at": "2026-06-08T10:00:00.000000Z",
  "translations": [
    { "id": 9, "object_id": 5, "language_code": "uz", "name": "Muhandis" },
    { "id": 10, "object_id": 5, "language_code": "ru", "name": "Инженер" },
    { "id": 11, "object_id": 5, "language_code": "en", "name": "Engineer" },
    { "id": 12, "object_id": 5, "language_code": "uc", "name": "Муҳандис" }
  ]
}
```

### Staff object

```json
{
  "id": 101,
  "department_id": 12,
  "position_id": 5,
  "is_department_director": false,
  "deleted_at": null,
  "department": { "id": 12, "code": "DEP-001", "translations": [ ... ] },
  "position": { "id": 5, "code": "POS-005", "translations": [ ... ] }
}
```

### Employee object

```json
{
  "id": 2048,
  "first_name": "Akbar",
  "last_name": "Toshmatov",
  "middle_name": "Salimovich",
  "tabel": "T-00412",
  "inn": "123456789",
  "inps": "987654321",
  "phone": "+998901234567",
  "image": null,
  "is_active": true,
  "deleted_at": null,
  "full_name": "Toshmatov Akbar Salimovich"
}
```

### EmployeeStaff object

```json
{
  "id": 3001,
  "staff_id": 101,
  "employee_id": 2048,
  "department_id": 12,
  "position_id": 5,
  "main_staff": true,
  "deleted_at": null,
  "employee": { ... },
  "department": { "id": 12, "translations": [ ... ] },
  "position": { "id": 5, "translations": [ ... ] },
  "staff": { "id": 101, ... }
}
```

---

## 1. Departments

### 1.1 List Departments

- **URL**: `GET /api/hrm/departments`
- **Permission**: `department-index`

**Query params:**

| Param | Type | Tavsif |
|-------|------|--------|
| `s` | string | Bo'lim nomi bo'yicha qidirish (translations orqali) |
| `parent_id` | int | Faqat shu parent ostidagi bo'limlar |
| `department_type_id` | int | Bo'lim turi bo'yicha filter |
| `rows` | int | Sahifadagi yozuvlar (default: 20) |
| `page` | int | Sahifa raqami (default: 1) |

**Response (200):**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 12,
        "code": "DEP-001",
        "parent_id": null,
        "department_type_id": 1,
        "sequence": 1,
        "staff_count": 8,
        "translations": [ ... ]
      }
    ],
    "total": 24,
    "per_page": 20,
    "last_page": 2
  }
}
```

---

### 1.2 Show Department

- **URL**: `GET /api/hrm/departments/{id}`
- **Permission**: `department-show`

**Response (200):** [Department object](#department-object) + `staff` relation (with positions)

**Errors:**
- `404` — `{ "success": false, "message": "Department topilmadi.", "error_code": "NOT_FOUND" }`

---

## 2. Positions

### 2.1 List Positions

- **URL**: `GET /api/hrm/positions`
- **Permission**: `position-index`

**Query params:**

| Param | Type | Tavsif |
|-------|------|--------|
| `s` | string | Lavozim nomi bo'yicha qidirish (translations orqali) |
| `position_type_id` | int | Lavozim turi bo'yicha filter |
| `rows` | int | Sahifadagi yozuvlar (default: 20) |
| `page` | int | Sahifa raqami (default: 1) |

**Response (200):** Paginated [Position object](#position-object) list.

---

### 2.2 Show Position

- **URL**: `GET /api/hrm/positions/{id}`
- **Permission**: `position-show`

**Response (200):** [Position object](#position-object)

**Errors:**
- `404` — `{ "success": false, "message": "Position topilmadi.", "error_code": "NOT_FOUND" }`

---

## 3. Staff

### 3.1 List Staff

- **URL**: `GET /api/hrm/staff`
- **Permission**: `staff-index`

**Query params:**

| Param | Type | Tavsif |
|-------|------|--------|
| `department_id` | int | Bo'lim bo'yicha filter |
| `position_id` | int | Lavozim bo'yicha filter |
| `is_department_director` | bool | Faqat bo'lim boshliqlari: `true` / `false` |
| `rows` | int | Sahifadagi yozuvlar (default: 20) |
| `page` | int | Sahifa raqami (default: 1) |

**Response (200):** Paginated [Staff object](#staff-object) list (with `department.translations`, `position.translations`).

---

### 3.2 Show Staff

- **URL**: `GET /api/hrm/staff/{id}`
- **Permission**: `staff-show`

**Response (200):** [Staff object](#staff-object) + `employeeStaff.employee`

**Errors:**
- `404` — `{ "success": false, "message": "Staff topilmadi.", "error_code": "NOT_FOUND" }`

---

## 4. Employees

### 4.1 List Employees

- **URL**: `GET /api/hrm/employees`
- **Permission**: `employee-index`

**Query params:**

| Param | Type | Tavsif |
|-------|------|--------|
| `s` | string | Ism, familiya, tabel bo'yicha qidirish |
| `is_active` | bool | Faol/nofaol xodimlar: `true` / `false` |
| `department_id` | int | Shu bo'limda ishlaydiganlar |
| `rows` | int | Sahifadagi yozuvlar (default: 20) |
| `page` | int | Sahifa raqami (default: 1) |

**Response (200):** Paginated [Employee object](#employee-object) list.

---

### 4.2 Show Employee

- **URL**: `GET /api/hrm/employees/{id}`
- **Permission**: `employee-show`

**Response (200):** [Employee object](#employee-object) + `employeeStaff` (with `department.translations`, `position.translations`, `staff`)

**Errors:**
- `404` — `{ "success": false, "message": "Xodim topilmadi.", "error_code": "NOT_FOUND" }`

---

## 5. Employee-Staff

Xodim va shtat birligi orasidagi bog'liqlik jadvali.

### 5.1 List Employee-Staff

- **URL**: `GET /api/hrm/employee-staff`
- **Permission**: `employee_staff-index`

**Query params:**

| Param | Type | Tavsif |
|-------|------|--------|
| `employee_id` | int | Xodim ID si bo'yicha filter |
| `department_id` | int | Bo'lim bo'yicha filter |
| `staff_id` | int | Shtat birligi bo'yicha filter |
| `main_staff` | bool | Asosiy shtat: `true` / `false` |
| `rows` | int | Sahifadagi yozuvlar (default: 20) |
| `page` | int | Sahifa raqami (default: 1) |

**Response (200):** Paginated [EmployeeStaff object](#employeestaff-object) list.

---

### 5.2 Show Employee-Staff

- **URL**: `GET /api/hrm/employee-staff/{id}`
- **Permission**: `employee_staff-show`

**Response (200):** [EmployeeStaff object](#employeestaff-object)

**Errors:**
- `404` — `{ "success": false, "message": "Employee staff topilmadi.", "error_code": "NOT_FOUND" }`

---

## Permission jadvali

| Endpoint | Permission |
|----------|-----------|
| `GET /api/hrm/departments` | `department-index` |
| `GET /api/hrm/departments/{id}` | `department-show` |
| `GET /api/hrm/positions` | `position-index` |
| `GET /api/hrm/positions/{id}` | `position-show` |
| `GET /api/hrm/staff` | `staff-index` |
| `GET /api/hrm/staff/{id}` | `staff-show` |
| `GET /api/hrm/employees` | `employee-index` |
| `GET /api/hrm/employees/{id}` | `employee-show` |
| `GET /api/hrm/employee-staff` | `employee_staff-index` |
| `GET /api/hrm/employee-staff/{id}` | `employee_staff-show` |

> `superadmin` roli barcha permissionlarni bypass qiladi.

---

## HRM Sync

Ma'lumotlarni HRM bazasidan local bazaga sync qilish uchun:

```bash
# Oddiy sync (faqat o'zgarganlarni upsert qiladi)
php artisan sync:hrm

# To'liq yangilash (departments va positions truncate qilinadi)
php artisan sync:hrm --fresh
```

**Sync tartibi:**
1. `departments` (+ `department_translations`)
2. `employee_staff` (HRM: `employee_work`)
3. `staff` (HRM: `staff_list`)
4. `positions` (+ `position_translations`, HRM: `staff_position`)
5. `employees` (HRM: `employee`)