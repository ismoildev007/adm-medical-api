# Medical API — Documentation

Base URL: `/api/`
Auth driver: **Laravel Passport** (OAuth2 Bearer tokens)

---

## Sections

| # | Section | File | Description |
|---|---------|------|-------------|
| 1 | Authentication | [auth.md](auth.md) | Register, login, logout, token management |
| 2 | Users | [user.md](user.md) | User CRUD, role & project permission assignment |

---

## Quick start

```bash
# 1. Login
POST /api/login
{ "username": "admin", "password": "secret" }

# 2. Use the returned access_token in all further requests
Authorization: Bearer <access_token>
```

---

## Global rules

- All `POST`/`PUT`/`PATCH` requests must include `Content-Type: application/json`
- Validation errors return HTTP `422` with an `errors` object
- Auth errors return HTTP `401`
- Permission errors return HTTP `403`
- Soft-deleted users return HTTP `404` (treated as not found)
- `superadmin` bypasses all permission checks

---

## Adding new docs

When a new controller is added, create a matching `docs/<section>.md` file and add a row to the table above. Follow the same structure: object shape → endpoints → field table → error list → audit side-effects.