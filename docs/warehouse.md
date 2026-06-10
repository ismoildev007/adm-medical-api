# Medical API — Warehouse (Sklad)

Dori omborini boshqarish uchun CRUD API.

**Base URL**: `/api/warehouse/`
**Auth header**: `Authorization: Bearer <token>`
**Middleware**: `auth:api`, `role`

---

## Response format

```json
{
  "success": true,
  "message": "Success",
  "data": { ... }
}
```

Xato:

```json
{
  "success": false,
  "message": "Dori topilmadi.",
  "error_code": "NOT_FOUND"
}
```

Validation xatosi `422`:

```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "name": ["The name field is required."]
  }
}
```

---

## Pagination

```json
{
  "success": true,
  "message": "Success",
  "data": {
    "current_page": 1,
    "data": [ ... ],
    "from": 1,
    "last_page": 3,
    "per_page": 20,
    "to": 20,
    "total": 58,
    "next_page_url": "...",
    "prev_page_url": null
  }
}
```

---

## Modellar

### Medicine object

```json
{
  "id": 5,
  "name": "Amoksitsillin 500mg",
  "unit": "dona",
  "quantity": 240,
  "expire_date": "2027-03-01",
  "description": "Keng spektrli antibiotik",
  "is_active": true,
  "created_at": "2026-06-10T09:00:00.000000Z",
  "updated_at": "2026-06-10T09:00:00.000000Z"
}
```

### MedicineTransaction object

```json
{
  "id": 18,
  "medicine_id": 5,
  "type": "income",
  "quantity": 100,
  "comment": "Yangi partiya keldi",
  "created_by": 1,
  "created_at": "2026-06-10T10:00:00.000000Z",
  "updated_at": "2026-06-10T10:00:00.000000Z",
  "medicine": {
    "id": 5,
    "name": "Amoksitsillin 500mg",
    "unit": "dona",
    "quantity": 240
  },
  "created_by": {
    "id": 1,
    "firstname": "Ali",
    "lastname": "Valiyev"
  }
}
```

**Tranzaksiya turlari:**

| type | Ta'sir | Tavsif |
|------|--------|--------|
| `income` | `quantity` **oshadi** | Omborga kirim |
| `outcome` | `quantity` **kamayadi** | Bemor/shifokorga berildi |
| `write_off` | `quantity` **kamayadi** | Muddati o'tgan, yaroqsiz |

---

## 1. Medicines

### 1.1 List Medicines

- **URL**: `GET /api/warehouse/medicines`
- **Permission**: `medicine-index`

**Query params:**

| Param | Type | Misol | Tavsif |
|-------|------|-------|--------|
| `s` | string | `?s=aspirin` | Nom bo'yicha qidirish |
| `sort_by` | string | `?sort_by=quantity` | `name` \| `quantity` \| `expire_date` (default: `name`) |
| `sort_dir` | string | `?sort_dir=desc` | `asc` \| `desc` (default: `asc`) |
| `quantity` | string | `?quantity=10\|500` | Miqdor oralig'i `min\|max` |
| `expire_date` | string | `?expire_date=2026-01-01\|2026-12-31` | Srok oralig'i `dan\|gacha` |
| `is_active` | bool | `?is_active=true` | Faol/nofaol |
| `rows` | int | `?rows=20` | Sahifadagi yozuvlar (default: 20) |
| `page` | int | `?page=1` | Sahifa raqami (default: 1) |

**Response (200):** Paginated [Medicine](#medicine-object) list.

---

### 1.2 Show Medicine

- **URL**: `GET /api/warehouse/medicines/{id}`
- **Permission**: `medicine-show`

**Response (200):** [Medicine object](#medicine-object)

**Errors:**
- `404` — `{"success": false, "message": "Dori topilmadi.", "error_code": "NOT_FOUND"}`

---

### 1.3 Create Medicine

- **URL**: `POST /api/warehouse/medicines`
- **Permission**: `medicine-store`
- **Body**:
  ```json
  {
    "name": "Amoksitsillin 500mg",
    "unit": "dona",
    "quantity": 0,
    "expire_date": "2027-03-01",
    "description": "Keng spektrli antibiotik",
    "is_active": true
  }
  ```

| Field | Type | Rules |
|-------|------|-------|
| `name` | string | required, max:255 |
| `unit` | string | required, max:50 (`dona`, `quti`, `ml`, `mg` ...) |
| `quantity` | int | required, min:0 |
| `expire_date` | date | nullable (`YYYY-MM-DD`) |
| `description` | string | nullable |
| `is_active` | bool | nullable (default: `true`) |

**Response (201):** Yaratilgan [Medicine object](#medicine-object)

---

### 1.4 Update Medicine

- **URL**: `PUT /api/warehouse/medicines/{id}`
- **Permission**: `medicine-update`
- **Body**: `store` bilan bir xil

**Response (200):** Yangilangan [Medicine object](#medicine-object)

**Errors:**
- `404` — dori topilmadi
- `422` — validation

---

### 1.5 Delete Medicine

- **URL**: `DELETE /api/warehouse/medicines/{id}`
- **Permission**: `medicine-destroy`

**Response (200)**:
```json
{
  "success": true,
  "message": "Dori o'chirildi.",
  "data": null
}
```

---

## 2. Medicine Transactions

Har bir tranzaksiya dori miqdoriga (`quantity`) ta'sir qiladi. `PUT` yo'q — yozuvlar o'zgartirilmaydi. `DELETE` qilganda miqdor teskari qaytariladi.

### 2.1 List Transactions

- **URL**: `GET /api/warehouse/transactions`
- **Permission**: `medicine_transaction-index`

**Query params:**

| Param | Type | Tavsif |
|-------|------|--------|
| `medicine_id` | int | Dori bo'yicha filter |
| `type` | string | `income` \| `outcome` \| `write_off` |
| `sort_dir` | string | `asc` \| `desc` (default: `desc`) |
| `rows` | int | Sahifadagi yozuvlar (default: 20) |
| `page` | int | Sahifa raqami (default: 1) |

**Response (200):** Paginated [MedicineTransaction](#medicinetransaction-object) list.

---

### 2.2 Show Transaction

- **URL**: `GET /api/warehouse/transactions/{id}`
- **Permission**: `medicine_transaction-show`

**Response (200):** [MedicineTransaction object](#medicinetransaction-object)

---

### 2.3 Create Transaction

- **URL**: `POST /api/warehouse/transactions`
- **Permission**: `medicine_transaction-store`
- **Body**:
  ```json
  {
    "medicine_id": 5,
    "type": "income",
    "quantity": 100,
    "comment": "Yangi partiya keldi"
  }
  ```

| Field | Type | Rules |
|-------|------|-------|
| `medicine_id` | int | required, exists:medicines |
| `type` | string | required: `income` \| `outcome` \| `write_off` |
| `quantity` | int | required, min:1 |
| `comment` | string | nullable |

**Response (201):** Yaratilgan [MedicineTransaction object](#medicinetransaction-object)

**Errors:**
- `422` — omborda yetarli dori yo'q:
  ```json
  { "success": false, "message": "Omborda yetarli dori yo'q. Mavjud: 30" }
  ```

---

### 2.4 Delete Transaction

- **URL**: `DELETE /api/warehouse/transactions/{id}`
- **Permission**: `medicine_transaction-destroy`

> Tranzaksiya o'chirilganda miqdor teskari qaytariladi.

**Response (200)**:
```json
{
  "success": true,
  "message": "Tranzaksiya bekor qilindi.",
  "data": null
}
```

---

## Permission jadvali

| Endpoint | Permission |
|----------|-----------|
| `GET /api/warehouse/medicines` | `medicine-index` |
| `GET /api/warehouse/medicines/{id}` | `medicine-show` |
| `POST /api/warehouse/medicines` | `medicine-store` |
| `PUT /api/warehouse/medicines/{id}` | `medicine-update` |
| `DELETE /api/warehouse/medicines/{id}` | `medicine-destroy` |
| `GET /api/warehouse/transactions` | `medicine_transaction-index` |
| `GET /api/warehouse/transactions/{id}` | `medicine_transaction-show` |
| `POST /api/warehouse/transactions` | `medicine_transaction-store` |
| `DELETE /api/warehouse/transactions/{id}` | `medicine_transaction-destroy` |