# Medical API — Users

This document describes user management endpoints. All routes require authentication unless stated otherwise.

**Base URL**: `/api/`
**Auth header**: `Authorization: Bearer <token>`
**Auth driver**: Laravel Passport (OAuth2)

> Role and permission checks are enforced by `RoleMiddleware`. Users with the `superadmin` role bypass all permission checks. Other users need the matching permission for each route.

---

## User object

```json
{
  "id": 1,
  "firstname": "Ali",
  "lastname": "Valiyev",
  "username": "ali_valiyev",
  "project_permission": ["project-a", "project-b"],
  "created_by": null,
  "updated_by": null,
  "deleted_by": null,
  "deleted_at": null,
  "created_at": "2026-06-02T10:00:00.000000Z",
  "updated_at": "2026-06-02T10:00:00.000000Z"
}
```

> `password` is never returned in any response. `project_permission` is a JSON array of project name strings.

---

## 1. List Users

Returns all users. No pagination applied — returns the full collection.

- **URL**: `GET /api/users`
- **Auth required**: Yes
- **Middleware**: `auth:api`, `role`

- **Response (200)**:
  ```json
  [
    {
      "id": 1,
      "firstname": "Ali",
      "lastname": "Valiyev",
      "username": "ali_valiyev",
      "project_permission": ["project-a"],
      "created_at": "2026-06-02T10:00:00.000000Z",
      "updated_at": "2026-06-02T10:00:00.000000Z"
    },
    { ... }
  ]
  ```

---

## 2. Create User

Admin creates a new user and assigns roles and project permissions.

- **URL**: `POST /api/users`
- **Auth required**: Yes
- **Middleware**: `auth:api`, `role`
- **Body**:
  ```json
  {
    "firstname": "Sardor",
    "lastname": "Toshmatov",
    "username": "sardor_t",
    "roles": ["doctor", "nurse"],
    "projects": ["project-a"]
  }
  ```

| Field | Type | Rules |
|-------|------|-------|
| `firstname` | string | required, max:100 |
| `lastname` | string | required, max:100 |
| `username` | string | required, max:100, unique |
| `password` | string | nullable, min:6 |
| `roles` | array | required, each must exist in `roles.name` |
| `projects` | array | optional |

- **Response (201)**:
  ```json
  {
    "id": 5,
    "firstname": "Sardor",
    "lastname": "Toshmatov",
    "username": "sardor_t",
    "project_permission": ["project-a"],
    "created_by": 1,
    "created_at": "2026-06-02T11:00:00.000000Z",
    "updated_at": "2026-06-02T11:00:00.000000Z"
  }
  ```

- **Errors**:
  - `422` — validation fails (e.g. username already taken, role not found)

- **Side effect**: Creation is automatically recorded in the audit log by the `Auditable` trait (`event: created`).

---

## 3. Get Single User

- **URL**: `GET /api/users/{id}`
- **Auth required**: Yes
- **Middleware**: `auth:api`, `role`

- **Response (200)**: Returns the [User object](#user-object).

- **Errors**:
  - `404` — user not found

---

## 4. Update User

Updates user fields, roles, and project permissions. `superadmin` can only have their password changed — roles and other fields are protected.

- **URL**: `PUT /api/users/{id}` or `PATCH /api/users/{id}`
- **Auth required**: Yes
- **Middleware**: `auth:api`, `role`
- **Body**:
  ```json
  {
    "firstname": "Sardor",
    "lastname": "Toshmatov",
    "username": "sardor_t",
    "password": "new_password",
    "roles": ["doctor"],
    "projects": ["project-b"]
  }
  ```

| Field | Type | Rules |
|-------|------|-------|
| `firstname` | string | required, max:100 |
| `lastname` | string | required, max:100 |
| `username` | string | required, max:100, unique (excluding current user) |
| `password` | string | nullable, min:6 |
| `roles` | array | required, each must exist in `roles.name` |
| `projects` | array | nullable |

- **Response (200)**: Returns the updated [User object](#user-object).

- **Errors**:
  - `404` — user not found
  - `422` — validation fails

- **Special rule for `superadmin`**: Only `password` can be changed. All other fields and role assignments are ignored.

- **Side effect**: Changes are automatically recorded in the audit log by the `Auditable` trait (`event: updated`, with `old_values` and `new_values`).

---

## 5. Delete User

Soft-deletes the user. The record remains in the database with `deleted_at` set.

- **URL**: `DELETE /api/users/{id}`
- **Auth required**: Yes
- **Middleware**: `auth:api`, `role`

- **Response (204)**: No content.

- **Errors**:
  - `403` — attempting to delete the `superadmin` user
  - `404` — user not found

- **Side effect**: Deletion is automatically recorded in the audit log (`event: deleted`).

---

## 6. Find User from LDAP

Searches for a user in the Active Directory (LDAP) by their AD username. Used by the Admin Panel to fetch user details before creating them locally.

- **URL**: `POST /api/users/find-from-ldap`
- **Auth required**: Yes
- **Middleware**: `auth:api`, `role`
- **Body**:
  ```json
  {
    "username": "ali_valiyev"
  }
  ```

| Field | Type | Rules |
|-------|------|-------|
| `username` | string | required |

- **Response (200)**:
  ```json
  {
    "username": "ali_valiyev",
    "firstname": "Ali",
    "lastname": "Valiyev",
    "cn": "Ali Valiyev",
    "name": "Ali Valiyev"
  }
  ```

- **Errors**:
  - `404` — `{"message": "user_not_found"}` if the user does not exist in Active Directory.
  - `422` — validation fails (username missing).

---

## Permission names (for `RoleMiddleware`)

| Route | Permission name checked |
|-------|------------------------|
| `GET /api/users` | `users-index` |
| `POST /api/users` | `users-store` |
| `GET /api/users/{id}` | `users-show` |
| `PUT /api/users/{id}` | `users-update` |
| `DELETE /api/users/{id}` | `users-destroy` |

> Permission names are derived from the route name by replacing `.` and `_` with `-`. Gates are registered at boot time from the `permissions` table (cached for 1 hour).