# Minimal DB + API Specification for Frontend-Only Local Backend

This document is a Copilot-friendly, runnable spec describing the minimal database schema and the HTTP API endpoints required by the frontend. It includes both MySQL and SQLite DDL templates, endpoint contracts (request/response), validation rules, idempotency behavior for image uploads, derived-field computation rules, and example requests.

This file can be used by an automated agent (or a developer) to quickly re-create a working local backend that supports the frontend flows (create transaction → upload images → update transaction) without the full production schema.

---

## Overview

Purpose: Provide the smallest, clear set of DB tables and API endpoints necessary for the frontend to operate locally. The focus is on:
- Weight transactions
- Transaction images
- Sector-specific sharding (optional; single-table fallback supported)
- Image storage metadata (files saved to disk)
- isSynced boolean on both transactions and images to allow sync workflows

Success criteria: The frontend can create a transaction (receives ID), upload one or more images tied to that transaction, and update the transaction including toggling `isSynced`.

---

## Naming conventions

- Base tables:
  - `weight_transactions`
  - `transaction_images`
- Sector-specific table suffix: sanitized, snake_case sector name appended to base table with underscore.
  - Example: `weight_transactions_farm_42` for sector name `Farm 42` -> suffix `farm_42`.
- Model classes (if using Laravel/PHP): `App\Models\WeightTransaction` (base) and optionally `App\Models\SectorSpecific\WeightTransaction_Farm42` (generated)
 
## Frontend model usage

Short, explicit guidance for the frontend and Copilot agents about which models are required or expected:

- Create/Update/Image upload flows: the server may persist writes into sector-specific tables (for example `weight_transactions_{sector}` and `transaction_images_{sector}`). The frontend does NOT need to include or ship the server "base model" implementation for these—only the request/response shapes (DTOs). If you generate client-side models/stubs, generate them to match the sector-specific schema used by the create/update/upload endpoints (the shape is identical to the base model in most fields).

- GET/list endpoints: these return the base model shape and the frontend can rely on the base model response format. For read-only flows the frontend does not need sector-specific model classes; a single base model shape is sufficient.

- Practical rule: the frontend must know the API contracts (fields and types). It does NOT need to ship or maintain both base and sector-specific server model implementations. Treat sector-specific and base models as equivalent JSON shapes from the client's perspective; only the server needs to handle where data is stored.

- Example mapping:
  - POST `/api/storeweight-transactions` -> server may write into `weight_transactions_{sector}` (frontend: send fields; expects `data.id` in response)
  - POST `/api/v1/images/upload` -> server may write into `transaction_images_{sector}` (frontend: send image + metadata; expects idempotent response)
  - GET `/api/getweight-transactions` -> server can read from the base table/view and return the base model JSON (frontend: use base model shape)

This clarification ensures Copilot-generated backends or lightweight local servers will not try to force the frontend to include server-side model classes—only the JSON contract matters.
---

## Minimal MySQL DDL

-- weight_transactions (base)
CREATE TABLE IF NOT EXISTS weight_transactions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  identity VARCHAR(128) NOT NULL,
  transaction_date DATE NOT NULL,
  farmer_name VARCHAR(255),
  weigher_name VARCHAR(255),
  animal_count INT DEFAULT 0,
  gross_weight DECIMAL(12,3) DEFAULT 0,
  tare_weight DECIMAL(12,3) DEFAULT 0,
  net_weight DECIMAL(12,3) DEFAULT 0,
  price_per_kg DECIMAL(12,2) DEFAULT 0,
  amount DECIMAL(14,2) DEFAULT 0,
  real_net DECIMAL(12,3) DEFAULT 0,
  sector_id INT NULL,
  sector_name VARCHAR(255) NULL,
  isSynced TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (identity),
  INDEX (isSynced)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- transaction_images (base)
CREATE TABLE IF NOT EXISTS transaction_images (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  transaction_id BIGINT UNSIGNED NULL,
  weighing_id BIGINT UNSIGNED NULL,
  identity VARCHAR(128) NULL,
  camera_no VARCHAR(64) NULL,
  image_path TEXT NOT NULL,
  image_mime VARCHAR(64) NULL,
  checksum_sha256 CHAR(64) NOT NULL,
  ingest_status ENUM('pending','linked') DEFAULT 'pending',
  isSynced TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (weighing_id, checksum_sha256),
  INDEX (transaction_id, checksum_sha256),
  INDEX (isSynced)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

---

## Minimal SQLite DDL (for fast local development)

-- weight_transactions
CREATE TABLE IF NOT EXISTS weight_transactions (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  identity TEXT NOT NULL,
  transaction_date TEXT NOT NULL,
  farmer_name TEXT,
  weigher_name TEXT,
  animal_count INTEGER DEFAULT 0,
  gross_weight REAL DEFAULT 0,
  tare_weight REAL DEFAULT 0,
  net_weight REAL DEFAULT 0,
  price_per_kg REAL DEFAULT 0,
  amount REAL DEFAULT 0,
  real_net REAL DEFAULT 0,
  sector_id INTEGER NULL,
  sector_name TEXT NULL,
  isSynced INTEGER DEFAULT 0,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP,
  updated_at TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_wt_identity ON weight_transactions(identity);
CREATE INDEX IF NOT EXISTS idx_wt_isSynced ON weight_transactions(isSynced);

-- transaction_images
CREATE TABLE IF NOT EXISTS transaction_images (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  transaction_id INTEGER NULL,
  weighing_id INTEGER NULL,
  identity TEXT NULL,
  camera_no TEXT NULL,
  image_path TEXT NOT NULL,
  image_mime TEXT NULL,
  checksum_sha256 TEXT NOT NULL,
  ingest_status TEXT DEFAULT 'pending',
  isSynced INTEGER DEFAULT 0,
  created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_img_weighing_checksum ON transaction_images(weighing_id, checksum_sha256);
CREATE INDEX IF NOT EXISTS idx_img_transaction_checksum ON transaction_images(transaction_id, checksum_sha256);
CREATE INDEX IF NOT EXISTS idx_img_isSynced ON transaction_images(isSynced);

Both DDL templates intentionally omit `sector_code`: it is not a `weight_transactions`
column. API responses derive it from the `sectors."sectorCode"` column maintained by the
HRM sector sync (see "Read-only `sector_code` field").

---

## API Endpoints (required by frontend)

All endpoints accept and return JSON unless noted.

1) Create transaction
- URL: POST /api/storeweight-transactions
- Purpose: Create a new weight transaction. The frontend expects the response to include `data.id` (the created transaction id).
- Request body (JSON):
  {
    "identity": "unique-identity-string",  // required
    "transaction_date": "YYYY-MM-DD",      // required
    "farmer_name": "...",
    "weigher_name": "...",
    "animal_count": 10,
    "gross_weight": 100.0,
    "tare_weight": 2.5,
    "price_per_kg": 120.50,
    "sector_id": 42,                      // optional
    "sector_name": "Farm 42",           // optional
    "isSynced": false                     // optional
  }
- Derived fields server-side (compute before insert):
  - net_weight = gross_weight - tare_weight
  - amount = net_weight * price_per_kg
  - real_net = net_weight (or additional business logic can apply)
- Response (201):
  {
    "success": true,
    "data": { "id": 123, "sector_code": 42 }
  }
  - `sector_code` (integer or null) is an additive, read-only field resolved from the transaction's sector; it is never accepted on write and never stored on `weight_transactions`. See "Read-only `sector_code` field" below.
- Errors: 422 for validation errors, 500 for server errors.

2) Upload image (current preferred endpoint)
- URL: POST /api/v1/images/upload
- Purpose: Upload an image (base64 or multipart). The endpoint must deduplicate by checksum and return 200/201/202 per rules below.
- Request body (JSON):
  {
    "transaction_id": 123,            // optional (if known)
    "weighing_id": 123,              // optional
    "identity": "unique-identity", // optional fallback
    "camera_no": "cam1",           // optional
    "image_base64": "data:image/jpeg;base64,/9j/...", // OR use multipart form
    "isSynced": false                // optional
  }
- Behavior and idempotency rules:
  - Compute SHA256 checksum of the raw image bytes.
  - If a record with same (weighing_id, checksum_sha256) OR (transaction_id, checksum_sha256) exists, return 200 with the existing record info (idempotent response).
  - Else save bytes to disk (under a path like `pictures/{segment}/{YYYY-MM-DD}/{identity}/{filename}`), then insert a DB row in `transaction_images` with `image_path`, `checksum_sha256`, `ingest_status` (`linked` if a transaction/weighing matches, else `pending`), `isSynced` default false.
  - On DB create error, delete the saved file to avoid orphan files and return 500.
- Responses:
  - 200: Existing image found. Body: { "success": true, "data": { "id": existing_id, ... } }
  - 201: New image saved and linked. Body: { "success": true, "data": { "id": new_id, ... } }
  - 202: New image saved but not linked (ingest_status: pending). Body: { "success": true, "data": { "id": new_id, "ingest_status": "pending" } }
- Validation: image_base64 required (or use multipart `image_file`). sector_id/sector_name optional and used to pick sector-specific image table if implemented.

3) Update transaction
- URL: PUT /api/updateweight-transactions/{id}
- Purpose: Update fields on an existing transaction. This must accept `isSynced` and persist it.
- Request body (JSON): subset of fields used in Create endpoint.
- Response (200): { "success": true, "data": { "id": 123, "sector_code": 42 } }
  - `sector_code` is read-only and resolved from the sector attached to the stored row (after refresh), so it is reported even when the request did not change `sector_id`. It is `null` when the row has no sector or the sector has no HRM code.

4) Get transactions (list)
- URL: GET /api/getweight-transactions
- Query params: optional pagination and filters (identity, sector_id, isSynced)
- Response (200): { "success": true, "data": [ { transaction row, ..., "sector_code": 42 }, ... ] }
  - Every row keeps its existing keys and gains `sector_code` (integer or null), read from the joined `sectors."sectorCode"`.

4b) Get one transaction
- URL: GET /api/weight-transactions/{id}
- Response (200): the transaction row as a JSON object (the same shape as one list row, including `sector_code`). If the transaction does not exist the endpoint returns 404.
- No nested `sector` object is returned; `sector_id` / `sector_name` / `sector_code` stay flat.

5) (Optional) Upload older client flow
- URL: POST /api/v1/images/uploadold
- Behavior: Backwards-compatible endpoint. Same idempotency rules.

### Read-only `sector_code` field

`weight_transactions` has **no** `sector_code` column. The endpoints above add a
`sector_code` key derived from `sectors."sectorCode"` for the row's `sector_id`:

| Endpoint | Where the key appears |
| --- | --- |
| `POST /api/storeweight-transactions` | inside the response `data` object |
| `PUT /api/updateweight-transactions/{id}` | inside the response `data` object |
| `GET /api/getweight-transactions` | on every returned row |
| `GET /api/weight-transactions/{id}` | on the returned object |

Rules:

- Type is integer or null. Postgres returns BIGINT as a string, so the API casts it.
- `null` when `sector_id` is empty, the sector is gone, or the sector has not been synced from HRM yet.
- It is never accepted from the request body and never written to the table: the write paths' `$request->only()` list and validation rules are unchanged, so an extra `sector_code` in a request is ignored.
- Existing `sector_id` / `sector_name` keys and all other keys are unchanged; clients that do not know the key can simply ignore it.
- A soft-deleted sector still reports its code in the list and single-transaction reads, so historical rows keep their code.

---

## Image storage path conventions

- Save files under project storage directory, e.g. `storage/app/public/pictures` or a configurable `pictures/` folder in `public` for local testing.
- Path template: `pictures/{segment}/{YYYY-MM-DD}/{identity}/{random_filename}.{ext}`
  - `segment` can be `weighings` or `transactions`.
  - `identity` is passed in request or derived from transaction.
  - `random_filename` should be collision-resistant (UUID or timestamp+random).
- Keep original extension or optionally convert to `webp` if desired. Store `image_mime` in DB.

---

## Validation rules (concise)

- Create transaction (POST /storeweight-transactions):
  - identity: required, string
  - transaction_date: required, date (YYYY-MM-DD)
  - gross_weight, tare_weight, price_per_kg: numeric
  - sector_id: integer (optional)
  - sector_code: not accepted — read-only, derived server-side from `sectors."sectorCode"`
  - isSynced: boolean (optional)

- Upload image:
  - image_base64 OR multipart file required
  - if present, transaction_id or weighing_id recommended
  - checksum computed server-side; client may omit
  - camera_no: optional string
  - isSynced: boolean (optional)

- Update transaction:
  - id path param required
  - body fields optional
  - isSynced: boolean allowed
  - sector_code: not accepted — read-only, derived server-side after the row is refreshed

---

## Derived field computations (server-side)

- net_weight = gross_weight - tare_weight
- amount = net_weight * price_per_kg
- real_net = net_weight (or other business logic if required)

Always compute these server-side during create and update to keep frontend simple.

---

## Example curl requests

Create transaction:

```bash
curl -s -X POST "http://localhost:8000/api/storeweight-transactions" \
  -H "Content-Type: application/json" \
  -d '{
    "identity": "abc-123",
    "transaction_date": "2025-10-21",
    "farmer_name": "Farmer A",
    "weigher_name": "Weigher X",
    "gross_weight": 100.5,
    "tare_weight": 2.0,
    "price_per_kg": 120.0
  }'
```

Upload image (base64):

```bash
curl -s -X POST "http://localhost:8000/api/v1/images/upload" \
  -H "Content-Type: application/json" \
  -d '{
    "weighing_id": 123,
    "identity": "abc-123",
    "camera_no": "cam1",
    "image_base64": "data:image/jpeg;base64,/9j/..."
  }'
```

Update transaction:

```bash
curl -s -X PUT "http://localhost:8000/api/updateweight-transactions/123" \
  -H "Content-Type: application/json" \
  -d '{ "animal_count": 12, "isSynced": 1 }'
```

---

## Quick implementation checklist for Copilot

- Create DB and run the minimal DDL (MySQL or SQLite).
- Implement HTTP routes for the 3 core endpoints above.
- Implement server-side validations and derived-field computations.
- Implement image handling:
  - Accept base64 or file
  - Compute SHA256 checksum
  - Deduplicate using indexes
  - Save file to disk and insert metadata in `transaction_images`
  - Return correct HTTP status codes for idempotency
- Make `isSynced` writable by the frontend and indexed for queries.

---

## Notes and recommendations

- For quick local testing, SQLite is easiest. Use the SQLite DDL and a small PHP or Node app that mounts a `pictures/` folder and exposes the endpoints.
- Unit tests: implement at least the following tests:
  - Create transaction returns id and computed fields saved correctly.
  - Upload duplicate image returns 200 with existing id.
  - Upload new image returns 201/202 depending on linked status.
  - Update transaction timeline including toggling `isSynced`.

---

## Contact points in this repository

- If you want to wire this spec into the existing Laravel app, see:
  - `routes/api.php` for route definitions
  - `app/Http/Controllers/Api/WeightTransactionController.php`
  - `app/Http/Controllers/Api/ImageUploadController.php`
  - `app/Services/ImageStorageService.php`
  - `app/Services/SectorModelResolver.php` (sector-specific behaviour)

---

End of minimal DB + API spec.
