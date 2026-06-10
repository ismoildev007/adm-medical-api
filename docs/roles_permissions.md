# Roles and Permissions API Documentation

Bu hujjat orqali rollar va ularning ruxsatnomalarini (permissions) boshqarish bo'yicha barcha API so'rovlari ko'rsatilgan.

## Asosiy Base URL
`{{base_url}}/api`

---

## 1. List Roles
Rollar ro'yxatini va ularga tegishli statistikalarni (permissionlar soni) qaytaradi.
- **URL**: `GET /role`
- **Headers**:
  - `Authorization`: Bearer {{token}}
  - `Accept`: application/json
- **Query Params**:
  - `search` (ixtiyoriy): Rol nomi yoki izohidan qidirish
  - `permission` (ixtiyoriy): Ma'lum bir ruxsatnoma (permission) ga ega rollarni filtrlash
- **Response (200)**:
  ```json
  [
    {
      "name": "admin",
      "type": 1,
      "description": "Administrator roli",
      "permissions_count": 12
    }
  ]
  ```

---

## 2. Create Role
Yangi rol yaratish.
- **URL**: `POST /role`
- **Headers**:
  - `Authorization`: Bearer {{token}}
  - `Accept`: application/json
  - `Content-Type`: application/json
- **Body**:
  ```json
  {
    "name": "editor",
    "type": 2,
    "description": "Faqat tahrirlash huquqiga ega rol"
  }
  ```
- **Response (201)**:
  ```json
  {
    "success": true,
    "message": "Role yaratildi.",
    "data": {
      "name": "editor",
      "type": 2,
      "description": "Faqat tahrirlash huquqiga ega rol"
    }
  }
  ```

---

## 3. Get Role
Bitta rolni barcha permissionlari bilan olish.
- **URL**: `GET /role/{role_name}`
- **Headers**:
  - `Authorization`: Bearer {{token}}
  - `Accept`: application/json
- **Response (200)**:
  ```json
  {
    "name": "admin",
    "type": 1,
    "description": "Administrator roli",
    "permissions": [
      {
        "name": "users-index",
        "pivot": {
          "role_name": "admin",
          "permission_name": "users-index"
        }
      }
    ]
  }
  ```

---

## 4. Update Role
Mavjud rolni tahrirlash (nomini o'zgartirib bo'lmaydi).
- **URL**: `PUT /role/{role_name}`
- **Headers**:
  - `Authorization`: Bearer {{token}}
  - `Accept`: application/json
  - `Content-Type`: application/json
- **Body**:
  ```json
  {
    "type": 2,
    "description": "Yangi izoh"
  }
  ```
- **Response (200)**:
  ```json
  {
    "success": true,
    "message": "Role yangilandi.",
    "data": { ... }
  }
  ```

---

## 5. Delete Role
Rolni o'chirish.
- **URL**: `DELETE /role/{role_name}`
- **Headers**:
  - `Authorization`: Bearer {{token}}
  - `Accept`: application/json
- **Response (200)**:
  ```json
  {
    "success": true,
    "message": "Role o'chirildi."
  }
  ```

---

## 6. Get Permissions For Role
Rolga biriktirilgan va umumiy tizimdagi barcha ruxsatnomalar (route nomlari) ro'yxatini qaytaradi.
- **URL**: `GET /role/{role_name}/permissions`
- **Headers**:
  - `Authorization`: Bearer {{token}}
  - `Accept`: application/json
- **Response (200)**:
  ```json
  {
    "all": [
      "users-index",
      "users-store",
      "role-index"
    ],
    "assigned": [
      "users-index"
    ]
  }
  ```

---

## 7. Sync Role Permissions
Rolga ruxsatnomalarni biriktirish (avvalgilarini o'chirib, faqat yuborilganlarini saqlaydi).
- **URL**: `POST /role/{role_name}/sync-permissions`
- **Headers**:
  - `Authorization`: Bearer {{token}}
  - `Accept`: application/json
  - `Content-Type`: application/json
- **Body**:
  ```json
  {
    "permissions": [
      "users-index",
      "users-store"
    ]
  }
  ```
- **Response (200)**:
  ```json
  {
    "success": true,
    "message": "Ruxsatnomalar muvaffaqiyatli saqlandi.",
    "count": 2
  }
  ```

---

## 8. Paginated List Permissions
Barcha mavjud permissionlarni paginatsiya qilib qaytaradi.
- **URL**: `GET /permission`
- **Headers**:
  - `Authorization`: Bearer {{token}}
  - `Accept`: application/json
- **Response (200)**:
  ```json
  {
    "current_page": 1,
    "data": [
      {
        "name": "users-index"
      }
    ],
    "total": 1
  }
  ```

---

## 9. Array List Permissions
Tizimdagi barcha permissionlarni oddiy array sifatida (dropdown yoki checkbox uchun) qaytaradi.
- **URL**: `GET /permission-list`
- **Headers**:
  - `Authorization`: Bearer {{token}}
  - `Accept`: application/json
- **Response (200)**:
  ```json
  {
    "data": [
      "role-index",
      "users-index",
      "users-store"
    ]
  }
  ```
