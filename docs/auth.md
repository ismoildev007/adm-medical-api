# Medical API — Authentication

This document describes authentication endpoints, token management, and global response conventions for the Medical API.

---

## Global Response Format

All endpoints return a consistent JSON structure.

**Successful response:**
```json
{
  "access_token": "eyJ...",
  "token_type": "Bearer",
  "user": { ... }
}
```

**Validation error (422):**
```json
{
  "success": false,
  "message": "Validation errors",
  "errors": {
    "username": ["The username field is required."]
  }
}
```

**Authentication error (401):**
```json
{
  "message": "The provided credentials are incorrect."
}
```

**Authorization error (403):**
```json
{
  "message": "Bu amalni bajarish uchun ruxsatingiz yo'q."
}
```

**Not found (404):**
```json
{
  "message": "No query results for model [App\\Models\\User]."
}
```

---

## Token format

All protected routes require a `Bearer` token in the `Authorization` header:
```
Authorization: Bearer <access_token>
```

Tokens are issued by **Laravel Passport** (OAuth2). Each token is a long-lived access token tied to a named client (`auth_token`). To invalidate a token, call the logout endpoint.

---

## Base URL

```
/api/
```

---

## 1. Register

Creates a new user account. Roles can be assigned at registration time.

- **URL**: `POST /api/register`
- **Auth required**: No
- **Body**:
  ```json
  {
    "firstname": "Ali",
    "lastname": "Valiyev",
    "username": "ali_valiyev",
    "roles": ["doctor"],
    "projects": ["project-a", "project-b"]
  }
  ```

| Field | Type | Rules |
|-------|------|-------|
| `firstname` | string | required, max:100 |
| `lastname` | string | required, max:100 |
| `username` | string | required, max:100, unique |
| `password` | string | nullable, min:6 |
| `roles` | array | required, each must exist in `roles.name` |
| `projects` | array | optional, array of project name strings |

- **Response (201)**:
  ```json
  {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "firstname": "Ali",
      "lastname": "Valiyev",
      "username": "ali_valiyev",
      "project_permission": ["project-a", "project-b"],
      "created_by": null,
      "created_at": "2026-06-02T10:00:00.000000Z",
      "updated_at": "2026-06-02T10:00:00.000000Z"
    }
  }
  ```

- **Errors**:
  - `422` — validation fails (e.g. username already taken, role not found)

---

## 2. Login

Authenticates an existing user by username and password.

- **URL**: `POST /api/login`
- **Auth required**: No
- **Body**:
  ```json
  {
    "username": "ali_valiyev",
    "password": "secret123"
  }
  ```

| Field | Type | Rules |
|-------|------|-------|
| `username` | string | required |
| `password` | string | required |

- **LDAP Note**: If the application is configured to authenticate with LDAP, it will verify credentials against the Active Directory. However, **users can only log in if they have already been added to the local database** by an administrator (via the `/users/find-from-ldap` flow). If an LDAP user successfully authenticates but does not exist in the local database, they will receive a `403 Forbidden` error.

- **Roles**: Upon successful login, the user's `roles` are loaded and returned. The frontend should check these roles. If no roles are attached, the user is considered a normal user. If roles are present, they are a system user (doctor, admin, superadmin, etc). If necessary, the frontend can deny access or route differently based on this.

- **Response (200)**:
  ```json
  {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "firstname": "Ali",
      "lastname": "Valiyev",
      "username": "ali_valiyev",
      "project_permission": ["project-a"],
      "created_at": "2026-06-02T10:00:00.000000Z",
      "updated_at": "2026-06-02T10:00:00.000000Z",
      "roles": [
        {
          "id": 1,
          "name": "doctor"
        }
      ]
    }
  }
  ```

- **Errors**:
  - `401` — incorrect username or password
  - `403` — user authenticated via LDAP but not found in the local database
  - `422` — missing required fields

- **Side effect**: Login event is recorded in the audit log (`event: logged_in`).

---

## 3. Logout

Revokes the current access token. The token immediately becomes invalid.

- **URL**: `POST /api/logout`
- **Auth required**: Yes
- **Header**: `Authorization: Bearer <token>`
- **Body**: none

- **Response (200)**:
  ```json
  {
    "message": "Logged out successfully"
  }
  ```

- **Side effect**: Logout event is recorded in the audit log (`event: logged_out`).

---

## 4. Get Authenticated User

Returns information about the currently authenticated user.

- **URL**: `GET /api/user`
- **Auth required**: Yes
- **Header**: `Authorization: Bearer <token>`

- **Response (200)**:
  ```json
  {
    "id": 1,
    "firstname": "Ali",
    "lastname": "Valiyev",
    "username": "ali_valiyev",
    "project_permission": ["project-a"],
    "created_at": "2026-06-02T10:00:00.000000Z",
    "updated_at": "2026-06-02T10:00:00.000000Z"
  }
  ```

---

## Audit log

Every successful login and logout is automatically recorded in the `audits` table with:

| Field | Value |
|-------|-------|
| `event` | `logged_in` / `logged_out` / `registered` |
| `auditable_type` | `App\Models\User` |
| `user_id` | authenticated user's ID |
| `new_values` | `{ "firstname": "...", "username": "..." }` |
| `tags` | `auth` |
| `ip_address` | request IP |
| `user_agent` | request User-Agent |