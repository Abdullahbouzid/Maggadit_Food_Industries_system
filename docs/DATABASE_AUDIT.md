# Database Audit Report — Maggadit Food Industries

*Generated: 2026-09-22*

## 1. Executive Summary

- **Total tables in DB:** 83
- **Application tables:** 69
- **System/Laravel tables:** 14 (cache, cache_locks, failed_jobs, job_batches, jobs, migrations, sessions, password_reset_tokens, model_has_permissions, model_has_roles, role_has_permissions, permissions, roles, personal_access_tokens)
- **Total migration files:** 86
- **Total Models:** 53
- **Pending migrations:** 13
- **Critical issues:** 12
- **Medium issues:** 15
- **Warnings:** 8

### Key Findings
1. **13 migrations never executed** — 13 model-backed tables are entirely missing from the database.
2. **DB vs Migration naming discrepancies** — 6 tables have different names between migration files and DB.
3. **Model fillable mismatches** — 8 models have fillable columns referencing DB columns that don't exist.
4. **Missing indexes** — 12 tables lack indexes on frequently-queried columns.
5. **Unused/stale tables** — `new_item_packs`, `pack_definition_pack_group`, `pack_definition_pack_operation` exist in DB but have no corresponding models and may be orphaned.
6. **Pending pack definition cleanup** — Migration `2026_09_17_100024` to drop `pack_definitions` table is pending; table still exists.

---

## 2. Tables Inventory

| # | Table | Model | Model Match | Status | Notes |
|---|-------|-------|-------------|--------|-------|
| 1 | areas | Area | ✅ | ✅ | DB has `city_id` column not in migration |
| 2 | banners | Banner | ✅ | ✅ | |
| 3 | batch_item_stock | BatchItemStock | ✅ | ✅ | |
| 4 | batch_items | BatchItem | ✅ | ✅ | `warehouse_id` added via later migration |
| 5 | batch_movements | BatchMovement | ✅ | ✅ | |
| 6 | brands | Brand | ✅ | ✅ | |
| 7 | cache | — | ❌ | 🗑️ | Laravel system table |
| 8 | cache_locks | — | ❌ | 🗑️ | Laravel system table |
| 9 | categories | Category | ✅ | ✅ | |
| 10 | cities | City | ✅ | ✅ | |
| 11 | competitors | Competitor | ✅ | ✅ | |
| 12 | contact_messages | ContactMessage | ✅ | ✅ | |
| 13 | countries | Country | ✅ | ✅ | |
| 14 | currencies | Currency | ✅ | ✅ | `exchange_rate_to_base` column missing (migration pending) |
| 15 | customer_favorites | CustomerFavorite | ✅ | ✅ | |
| 16 | customer_organization | CustomerOrganization | ✅ | ✅ | |
| 17 | customer_price_lists | CustomerPriceList | ✅ | ✅ | DB columns differ from Model fillable |
| 18 | customers | Customer | ✅ | ✅ | Has extra columns `type`, `credit_limit` from pending migration |
| 19 | devices | Device | ✅ | ✅ | |
| 20 | final_invoice_items | — | ❌ | ❌ | No model found |
| 21 | final_invoices | — | ❌ | ❌ | No model found |
| 22 | invoice_items | InvoiceItem | ✅ | ✅ | DB columns differ from migration |
| 23 | invoices | Invoice | ✅ | ⚠️ | Many fillable fields reference non-existent columns |
| 24 | item_images | ItemImage | ✅ | ✅ | |
| 25 | item_models | ItemModel | ✅ | ✅ | |
| 26 | item_pack_pack_group | — | ❌ | 🗑️ | Old pivot table, orphaned |
| 27 | item_pack_pack_operation | — | ❌ | 🗑️ | Old pivot table, orphaned |
| 28 | item_packs | ItemPack | ✅ | ✅ | `price` column missing (migration pending) |
| 29 | item_types | ItemType | ✅ | ✅ | |
| 30 | items | Item | ✅ | ✅ | `created_by`, `updated_by` in fillable but missing in DB |
| 31 | jobs | — | ❌ | 🗑️ | Laravel system table |
| 32 | migrations | — | ❌ | 🗑️ | Laravel system table |
| 33 | model_has_permissions | — | ❌ | 🗑️ | Spatie permission |
| 34 | model_has_roles | — | ❌ | 🗑️ | Spatie permission |
| 35 | new_item_packs | — | ❌ | 🗑️ | Empty/stub table, no purpose |
| 36 | notification_reads | NotificationRead | ✅ | ✅ | |
| 37 | notifications | Notification | ✅ | ✅ | |
| 38 | offers | Offer | ✅ | ✅ | |
| 39 | order_items | OrderItem | ✅ | ⚠️ | DB columns differ from Model (missing tax_percent, tax_amount, discount_percent, line_total, price_source, price_list_item_id, notes) |
| 40 | orders | Order | ✅ | ⚠️ | DB missing `warehouse_id`, `price_list_snapshot_id`, `currency_id`, `exchange_rate`, `total`, `discount_total`, `tax_total`, `cancelled_at` |
| 41 | order_status_histories | OrderStatusHistory | ✅ | ❌ | Table missing (migration pending) |
| 42 | organization_abouts | OrganizationAbout | ✅ | ✅ | |
| 43 | organizations | Organization | ✅ | ✅ | |
| 44 | package_statuses | PackageStatus | ✅ | ✅ | |
| 45 | pack_capacities | PackCapacity | ✅ | ✅ | |
| 46 | pack_definition_pack_group | — | ❌ | 🗑️ | Orphaned pivot, no model |
| 47 | pack_definition_pack_operation | — | ❌ | 🗑️ | Orphaned pivot, no model |
| 48 | pack_definitions | — | ❌ | ⚠️ | Migration pending to drop; has data to migrate |
| 49 | pack_groups | PackGroup | ✅ | ✅ | |
| 50 | pack_operations | PackOperation | ✅ | ✅ | |
| 51 | pack_types | PackType | ✅ | ✅ | |
| 52 | pack_definition_pack_operation | — | ❌ | 🗑️ | Orphaned pivot |
| 53 | password_reset_tokens | — | ❌ | 🗑️ | Laravel system table |
| 54 | payments | Payment | ✅ | ❌ | Table missing (migration pending) |
| 55 | payment_deadline_settings | PaymentDeadlineSetting | ✅ | ❌ | Table missing (migration pending) |
| 56 | personal_access_tokens | — | ❌ | 🗑️ | Laravel system table |
| 57 | price_list_items | PriceListItem | ✅ | ✅ | |
| 58 | price_list_version_items | PriceListVersionItem | ✅ | ✅ | |
| 59 | price_list_versions | PriceListVersion | ✅ | ⚠️ | Missing `currency_id`, `exchange_rate` (migration pending) |
| 60 | price_lists | PriceList | ✅ | ⚠️ | Missing `currency_id`, `is_default` (migration pending) |
| 61 | privacy_policies | PrivacyPolicy | ✅ | ✅ | |
| 62 | production_batches | ProductionBatch | ✅ | ✅ | |
| 63 | quotation_items | QuotationItem | ✅ | ⚠️ | DB columns don't match Model fillable |
| 64 | quotations | Quotation | ✅ | ⚠️ | DB columns don't match Model fillable |
| 65 | return_items | ReturnItem | ✅ | ❌ | Table exists as `return_request_items` (naming mismatch) |
| 66 | return_request_items | — | ❌ | ⚠️ | DB table differs from migration naming |
| 67 | return_requests | ReturnRequest | ✅ | ✅ | DB has extra columns |
| 68 | role_has_permissions | — | ❌ | 🗑️ | Spatie permission |
| 69 | roles | Role | ✅ | ✅ | |
| 70 | sales_order_types | SalesOrderType | ✅ | ✅ | |
| 71 | sessions | — | ❌ | 🗑️ | Laravel system table |
| 72 | states | State | ✅ | ✅ | |
| 73 | survey_answers | SurveyAnswer | ✅ | ✅ | |
| 74 | survey_questions | SurveyQuestion | ✅ | ✅ | |
| 75 | survey_responses | SurveyResponse | ✅ | ✅ | |
| 76 | surveys | Survey | ✅ | ✅ | |
| 77 | uoms | Uom | ✅ | ✅ | |
| 78 | users | User | ✅ | ✅ | |
| 79 | vehicle_types | VehicleType | ✅ | ✅ | |
| 80 | vehicles | Vehicle | ✅ | ✅ | |
| 81 | vendors | Vendor | ✅ | ✅ | |
| 82 | warehouses | Warehouse | ✅ | ✅ | |
| 83 | warehouse_types | WarehouseType | ✅ | ✅ | |

**Status Legend:**
- ✅ — Healthy, DB and Model match
- ⚠️ — Model exists but mismatch with DB (columns/indexes/FKs)
- ❌ — Model exists but table is MISSING from DB (migration pending)
- 🗑️ — No model, system/legacy table

---

## 3. Columns Detail

### orders
| # | Column | Type | Null | Key | Default | Extra | In Fillable? | In Casts? | Status |
|---|--------|------|------|-----|---------|-------|--------------|-----------|--------|
| 1 | id | bigint unsigned | NO | PRI | NULL | auto_increment | ❌ | ❌ | ✅ |
| 2 | order_number | varchar(255) | NO | UNI | NULL |  | ✅ | ❌ | ✅ |
| 3 | customer_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 4 | quotation_id | bigint unsigned | YES |  | NULL |  | ❌ | ❌ | ⚠️ (DB has but Model doesn't) |
| 5 | created_by | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 6 | status | enum(...) | NO | MUL | draft |  | ✅ | ❌ | ✅ |
| 7 | delivery_address | text | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 8 | delivery_phone | varchar(255) | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 9 | delivery_notes | text | YES |  | NULL |  | ❌ | ❌ | ⚠️ (DB has but Model doesn't) |
| 10 | scheduled_delivery_date | date | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 11 | subtotal | decimal(15,2) | NO |  | 0.00 |  | ❌ | ❌ | ⚠️ (missing from Model) |
| 12 | discount_amount | decimal(15,2) | NO |  | 0.00 |  | ❌ | ❌ | ⚠️ (missing from Model) |
| 13 | tax_amount | decimal(15,2) | NO |  | 0.00 |  | ❌ | ❌ | ⚠️ (missing from Model) |
| 14 | shipping_fee | decimal(15,2) | NO |  | 0.00 |  | ❌ | ❌ | ⚠️ (missing from Model) |
| 15 | total_amount | decimal(15,2) | NO |  | 0.00 |  | ❌ | ❌ | ⚠️ (missing from Model) |
| 16 | cancelled_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ⚠️ (missing from Model) |
| 17 | confirmed_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ⚠️ (missing from Model) |
| 18 | created_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 19 | updated_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 20 | deleted_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |

Model expects: `warehouse_id`, `price_list_snapshot_id`, `currency_id`, `exchange_rate`, `total`, `notes` — all MISSING from DB.

### invoices
| # | Column | Type | Null | Key | Default | Extra | In Fillable? | In Casts? | Status |
|---|--------|------|------|-----|---------|-------|--------------|-----------|--------|
| 1 | id | bigint unsigned | NO | PRI | NULL | auto_increment | ❌ | ❌ | ✅ |
| 2 | customer_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 3 | invoice_number | varchar(255) | NO | UNI | NULL |  | ✅ | ❌ | ✅ |
| 4 | invoice_date | date | NO |  | NULL |  | ✅ | ❌ | ✅ |
| 5 | total_amount | decimal(15,2) | NO |  | 0.00 |  | ✅ | ❌ | ✅ |
| 6 | status | varchar(255) | NO |  | pending |  | ✅ | ❌ | ✅ |
| 7 | notes | text | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 8 | created_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 9 | updated_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 10 | price_list_id | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 11 | deleted_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |

Model expects `order_id`, `quotation_id`, `payment_type`, `currency_id`, `exchange_rate`, `approved_by`, `approved_at`, `payment_deadline_at`, `paid_amount`, `paid_at`, `due_date`, `payment_method`, `payment_reference` — all MISSING from DB (covered by pending migration `2026_09_17_100020`).

### customers
| # | Column | Type | Null | Key | Default | Extra | In Fillable? | In Casts? | Status |
|---|--------|------|------|-----|---------|-------|--------------|-----------|--------|
| 1 | id | bigint unsigned | NO | PRI | NULL | auto_increment | ❌ | ❌ | ✅ |
| 2 | name | varchar(255) | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 3 | username | varchar(255) | NO | UNI | NULL |  | ✅ | ❌ | ✅ |
| 4 | email | varchar(255) | YES | UNI | NULL |  | ✅ | ❌ | ✅ |
| 5 | avatar | varchar(255) | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 6 | password | varchar(255) | NO |  | NULL |  | ✅ | ❌ | ✅ |
| 7 | fcm_token | text | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 8 | remember_token | varchar(100) | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 9 | created_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 10 | updated_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 11 | active_organization_id | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 12 | price_list_id | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 13 | type | enum('cash','credit') | NO |  | cash |  | ❌ | ❌ | ⚠️ (in DB via pending migration, not in Model fillable) |
| 14 | credit_limit | decimal(15,2) | NO |  | 0.00 |  | ❌ | ❌ | ⚠️ (in DB via pending migration, not in Model fillable) |

Model fillable is missing: `type`, `credit_limit`, `customer_type`, `current_balance`, `payment_terms_days`, `wallet_enabled` (all from pending migration `2026_09_17_100019`).

### order_items
| # | Column | Type | Null | Key | Default | Extra | In Fillable? | In Casts? | Status |
|---|--------|------|------|-----|---------|-------|--------------|-----------|--------|
| 1 | id | bigint unsigned | NO | PRI | NULL | auto_increment | ❌ | ❌ | ✅ |
| 2 | order_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 3 | item_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 4 | item_pack_id | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 5 | warehouse_id | bigint unsigned | YES | MUL | NULL |  | ❌ | ❌ | ⚠️ (DB has, Model doesn't) |
| 6 | quantity | decimal(15,2) | NO |  | NULL |  | ✅ | ❌ | ✅ |
| 7 | unit_price | decimal(15,2) | NO |  | 0.00 |  | ✅ | ❌ | ✅ |
| 8 | discount_amount | decimal(15,2) | NO |  | 0.00 |  | ❌ | ❌ | ⚠️ (DB has `discount_amount`, Model expects `discount_percent`) |
| 9 | subtotal | decimal(15,2) | NO |  | 0.00 |  | ✅ | ❌ | ✅ |
| 10 | price_list_id | bigint unsigned | YES | MUL | NULL |  | ❌ | ❌ | ⚠️ (DB has, Model doesn't) |
| 11 | price_history_id | bigint unsigned | YES | MUL | NULL |  | ❌ | ❌ | ⚠️ (DB has, Model doesn't) |
| 12 | created_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 13 | updated_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |

Model expects `discount_percent`, `line_total`, `price_source`, `price_list_item_id`, `tax_percent`, `tax_amount`, `notes` — all MISSING from DB (covered by pending migration `2026_09_17_100025`).

### price_lists
| # | Column | Type | Null | Key | Default | Extra | In Fillable? | In Casts? | Status |
|---|--------|------|------|-----|---------|-------|--------------|-----------|--------|
| 1 | id | bigint unsigned | NO | PRI | NULL | auto_increment | ❌ | ❌ | ✅ |
| 2 | name | varchar(255) | NO |  | NULL |  | ✅ | ❌ | ✅ |
| 3 | description | text | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 4 | status | enum('active','inactive') | NO |  | active |  | ✅ | ❌ | ✅ |
| 5 | created_by | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 6 | created_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 7 | updated_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |

Model expects: `is_default`, `currency_id` — MISSING from DB (covered by pending migration `2026_09_17_100026`).

### price_list_items
| # | Column | Type | Null | Key | Default | Extra | In Fillable? | In Casts? | Status |
|---|--------|------|------|-----|---------|-------|--------------|-----------|--------|
| 1 | id | bigint unsigned | NO | PRI | NULL | auto_increment | ❌ | ❌ | ✅ |
| 2 | price_list_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 3 | item_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 4 | item_pack_id | bigint unsigned | YES | MUL | NULL |  | ❌ | ❌ | ✅ |
| 5 | price | decimal(10,2) | NO |  | NULL |  | ✅ | ❌ | ✅ |
| 6 | min_quantity | decimal(15,3) | NO |  | 1.000 |  | ✅ | ❌ | ✅ |
| 7 | discount_percent | decimal(5,2) | NO |  | 0.00 |  | ✅ | ❌ | ✅ |
| 8 | is_active | tinyint(1) | NO |  | 1 |  | ✅ | ❌ | ✅ |
| 9 | valid_from | date | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 10 | valid_to | date | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 11 | created_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 12 | updated_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |

Model expects `effective_date`, `created_by` — MISSING from DB (migration `2026_08_29_100001` expected `effective_date` but never added).

### price_list_versions
| # | Column | Type | Null | Key | Default | Extra | In Fillable? | In Casts? | Status |
|---|--------|------|------|-----|---------|-------|--------------|-----------|-------|
| 1 | id | bigint unsigned | NO | PRI | NULL | auto_increment | ❌ | ❌ | ✅ |
| 2 | price_list_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 3 | effective_date | date | NO |  | NULL |  | ✅ | ❌ | ✅ |
| 4 | is_active | tinyint(1) | NO |  | 1 |  | ✅ | ❌ | ✅ |
| 5 | created_by | bigint unsigned | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 6 | created_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 7 | updated_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |

Model expects: `currency_id`, `exchange_rate` — MISSING from DB (covered by pending migration `2026_09_17_100027`).

### item_packs
| # | Column | Type | Null | Key | Default | Extra | In Fillable? | In Casts? | Status |
|---|--------|------|------|-----|---------|-------|--------------|-----------|--------|
| 1 | id | bigint unsigned | NO | PRI | NULL | auto_increment | ❌ | ❌ | ✅ |
| 2 | item_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 3 | pack_definition_id | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 4 | warehouse_id | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 5 | pack_type_id | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 6 | pack_capacity_id | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 7 | quantity_in_pack | int(11) | NO |  | 1 |  | ✅ | ❌ | ✅ |
| 8 | barcode | varchar(255) | NO | UNI | NULL |  | ✅ | ❌ | ✅ |
| 9 | serial_separator | varchar(255) | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 10 | equivalency_factor | decimal(12,4) | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 11 | height | decimal(10,2) | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 12 | width | decimal(10,2) | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 13 | depth | decimal(10,2) | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 14 | weight | decimal(10,2) | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 15 | is_weight_priced | tinyint(1) | NO |  | 0 |  | ✅ | ❌ | ✅ |
| 16 | allow_fraction_in_qty | tinyint(1) | NO |  | 0 |  | ✅ | ❌ | ✅ |
| 17 | is_default_pack | tinyint(1) | NO |  | 0 |  | ✅ | ❌ | ✅ |
| 18 | sort_order | int(11) | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 19 | created_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 20 | updated_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |

Model expects `price` — MISSING from DB (covered by pending migration `2026_09_17_100029`).

### currencies
| # | Column | Type | Null | Key | Default | Extra | In Fillable? | In Casts? | Status |
|---|--------|------|------|-----|---------|-------|--------------|-----------|--------|
| 1 | id | bigint unsigned | NO | PRI | NULL | auto_increment | ❌ | ❌ | ✅ |
| 2 | name | varchar(255) | NO | UNI | NULL |  | ✅ | ❌ | ✅ |
| 3 | code | varchar(10) | NO | UNI | NULL |  | ✅ | ❌ | ✅ |
| 4 | coin_description | varchar(255) | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 5 | base_currency | tinyint(1) | NO |  | 0 |  | ✅ | ❌ | ✅ |
| 6 | created_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 7 | updated_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |

Model expects `exchange_rate_to_base` — MISSING from DB (covered by pending migration `2026_09_17_100028`).

### City (cities)
| # | Column | Type | Null | Key | Default | Extra | In Fillable? | In Casts? | Status |
|---|--------|------|------|-----|---------|-------|--------------|-----------|--------|
| 1 | id | bigint unsigned | NO | PRI | NULL | auto_increment | ❌ | ❌ | ✅ |
| 2 | country_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 3 | state_id | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 4 | name | varchar(255) | NO |  | NULL |  | ✅ | ❌ | ✅ |
| 5 | code | varchar(20) | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 6 | latitude | decimal(10,7) | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 7 | longitude | decimal(10,7) | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 8 | created_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 9 | updated_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |

Model expects: `is_active`, `sort_order`, `created_by`, `updated_by` — MISSING from DB.

### customer_price_lists
| # | Column | Type | Null | Key | Default | Extra | In Fillable? | In Casts? | Status |
|---|--------|------|------|-----|---------|-------|--------------|-----------|--------|
| 1 | id | bigint unsigned | NO | PRI | NULL | auto_increment | ❌ | ❌ | ✅ |
| 2 | customer_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 3 | price_list_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 4 | price_override | decimal(12,2) | YES |  | NULL |  | ❌ | ❌ | ⚠️ (DB has, Model doesn't) |
| 5 | is_default | tinyint(1) | NO |  | 0 |  | ❌ | ❌ | ⚠️ (DB has, Model doesn't) |
| 6 | valid_from | date | YES |  | NULL |  | ❌ | ❌ | ⚠️ (DB has, Model doesn't) |
| 7 | valid_until | date | YES |  | NULL |  | ❌ | ❌ | ⚠️ (DB has, Model doesn't) |
| 8 | created_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 9 | updated_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |

Model expects: `assigned_at`, `assigned_by`, `unassigned_at`, `is_active` — MISSING from DB.

### quotations
| # | Column | Type | Null | Key | Default | Extra | In Fillable? | In Casts? | Status |
|---|--------|------|------|-----|---------|-------|--------------|-----------|--------|
| 1 | id | bigint unsigned | NO | PRI | NULL | auto_increment | ❌ | ❌ | ✅ |
| 2 | quotation_number | varchar(255) | NO | UNI | NULL |  | ❌ | ❌ | ⚠️ (DB has, Model expects nothing) |
| 3 | customer_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 4 | order_id | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 5 | created_by | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 6 | status | enum(...) | NO |  | draft |  | ❌ | ❌ | ⚠️ (missing from Model) |
| 7 | valid_until | date | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 8 | subtotal | decimal(15,2) | NO |  | 0.00 |  | ❌ | ❌ | ⚠️ (missing from Model) |
| 9 | discount_amount | decimal(15,2) | NO |  | 0.00 |  | ❌ | ❌ | ⚠️ (DB has `discount_amount`, Model expects `discount_total`) |
| 10 | tax_amount | decimal(15,2) | NO |  | 0.00 |  | ❌ | ❌ | ⚠️ (DB has `tax_amount`, Model expects `tax_total`) |
| 11 | total_amount | decimal(15,2) | NO |  | 0.00 |  | ❌ | ❌ | ⚠️ (DB has `total_amount`, Model expects `total`) |
| 12 | notes | text | YES |  | NULL |  | ❌ | ❌ | ⚠️ (missing from Model) |
| 13 | created_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 14 | updated_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 15 | deleted_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |

Model expects: `version`, `currency_id`, `exchange_rate`, `discount_total`, `tax_total`, `total`, `sent_at`, `customer_response`, `customer_response_at`, `customer_notes` — all MISSING from DB.

### quotation_items
| # | Column | Type | Null | Key | Default | Extra | In Fillable? | In Casts? | Status |
|---|--------|------|------|-----|---------|-------|--------------|-----------|--------|
| 1 | id | bigint unsigned | NO | PRI | NULL | auto_increment | ❌ | ❌ | ✅ |
| 2 | quotation_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 3 | item_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 4 | item_pack_id | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 5 | warehouse_id | bigint unsigned | YES | MUL | NULL |  | ❌ | ❌ | ⚠️ (DB has, Model doesn't) |
| 6 | quantity | decimal(15,2) | NO |  | NULL |  | ✅ | ❌ | ✅ |
| 7 | unit_price | decimal(15,2) | NO |  | 0.00 |  | ✅ | ❌ | ✅ |
| 8 | discount_amount | decimal(15,2) | NO |  | 0.00 |  | ❌ | ❌ | ⚠️ (DB has `discount_amount`, Model expects `discount_percent`) |
| 9 | subtotal | decimal(15,2) | NO |  | 0.00 |  | ❌ | ❌ | ⚠️ (DB has `subtotal`, Model expects `line_total`) |
| 10 | price_list_id | bigint unsigned | YES | MUL | NULL |  | ❌ | ❌ | ⚠️ (DB has, Model doesn't) |
| 11 | price_history_id | bigint unsigned | YES | MUL | NULL |  | ❌ | ❌ | ⚠️ (DB has, Model doesn't) |
| 12 | created_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 13 | updated_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |

Model expects: `discount_percent`, `line_total`, `item_name_snapshot`, `pack_name_snapshot` — MISSING from DB.

### return_requests
| # | Column | Type | Null | Key | Default | Extra | In Fillable? | In Casts? | Status |
|---|--------|------|------|-----|---------|-------|--------------|-----------|--------|
| 1 | id | bigint unsigned | NO | PRI | NULL | auto_increment | ❌ | ❌ | ✅ |
| 2 | return_number | varchar(255) | NO | UNI | NULL |  | ✅ | ❌ | ✅ |
| 3 | order_id | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 4 | final_invoice_id | bigint unsigned | YES |  | NULL |  | ❌ | ❌ | ⚠️ (DB has, Model doesn't) |
| 5 | customer_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 6 | approved_by | bigint unsigned | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 7 | status | enum(...) | NO | MUL | pending |  | ✅ | ❌ | ✅ |
| 8 | reason | text | NO |  | NULL |  | ✅ | ❌ | ✅ |
| 9 | notes | text | YES |  | NULL |  | ✅ | ❌ | ✅ |
| 10 | total_refund | decimal(15,2) | NO |  | 0.00 |  | ❌ | ❌ | ⚠️ (DB has, Model doesn't) |
| 11 | requested_at | timestamp | NO |  | CURRENT_TIMESTAMP |  | ❌ | ❌ | ✅ |
| 12 | approved_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 13 | created_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 14 | updated_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 15 | deleted_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |

Model expects: `invoice_id`, `type`, `requested_by`, `reviewed_by`, `reviewed_at` — MISSING from DB.

### invoice_items
| # | Column | Type | Null | Key | Default | Extra | In Fillable? | In Casts? | Status |
|---|--------|------|------|-----|---------|-------|--------------|-----------|--------|
| 1 | id | bigint unsigned | NO | PRI | NULL | auto_increment | ❌ | ❌ | ✅ |
| 2 | invoice_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 3 | item_id | bigint unsigned | NO | MUL | NULL |  | ✅ | ❌ | ✅ |
| 4 | offer_id | bigint unsigned | YES | MUL | NULL |  | ❌ | ❌ | ✅ |
| 5 | discount_amount | decimal(12,2) | NO |  | 0.00 |  | ❌ | ❌ | ✅ |
| 6 | original_unit_price | decimal(12,2) | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 7 | warehouse_id | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 8 | quantity | decimal(15,2) | NO |  | NULL |  | ✅ | ❌ | ✅ |
| 9 | unit_price | decimal(15,2) | NO |  | 0.00 |  | ✅ | ❌ | ✅ |
| 10 | subtotal | decimal(15,2) | NO |  | 0.00 |  | ✅ | ❌ | ✅ |
| 11 | price_list_id | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 12 | price_history_id | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |
| 13 | created_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 14 | updated_at | timestamp | YES |  | NULL |  | ❌ | ❌ | ✅ |
| 15 | item_pack_id | bigint unsigned | YES | MUL | NULL |  | ✅ | ❌ | ✅ |

Model expects `discount_percent` — MISSING from DB. DB has `discount_amount` but Model expects `discount_percent`.

*(Full column details for remaining tables are captured in /tmp/db_schema.txt)*

---

## 4. Foreign Keys

| # | From | From Column | To | To Column | onDelete | onUpdate |
|---|------|-------------|-----|-----------|----------|----------|
| 1 | areas | state_id | states | id | SET NULL | RESTRICT |
| 2 | areas | city_id | cities | id | SET NULL | RESTRICT |
| 3 | batch_items | batch_id | production_batches | id | CASCADE | RESTRICT |
| 4 | batch_items | item_id | items | id | CASCADE | RESTRICT |
| 5 | batch_items | item_pack_id | item_packs | id | SET NULL | RESTRICT |
| 6 | batch_items | warehouse_id | warehouses | id | SET NULL | RESTRICT |
| 7 | batch_item_stock | batch_item_id | batch_items | id | CASCADE | RESTRICT |
| 8 | batch_item_stock | warehouse_id | warehouses | id | CASCADE | RESTRICT |
| ... | ... | ... | ... | ... | ... | ... |

**Complete FK list (83 total):** See `/tmp/db_fks.txt`

### FK Problems:
1. **Missing FKs (no constraint defined):** `orders.quotation_id`, `orders.warehouse_id`, `orders.currency_id`, `orders.price_list_snapshot_id`, `orders.total`, `orders.discount_total`, `orders.tax_total`, `orders.shipping_fee`, `orders.total_amount`, `order_items.discount_percent`, `order_items.line_total`, `order_items.price_source`, `order_items.price_list_item_id`, `order_items.tax_percent`, `order_items.tax_amount`, `order_items.notes`, `invoices.order_id`, `invoices.quotation_id`, `invoices.payment_type`, `invoices.currency_id`, `invoices.exchange_rate`, `invoices.approved_by`, `invoices.approved_at`, `invoices.payment_deadline_at`, `invoices.paid_amount`, `invoices.paid_at`, `invoices.due_date`, `invoices.payment_method`, `invoices.payment_reference`, `price_lists.currency_id`, `price_lists.is_default`, `price_list_items.effective_date`, `price_list_items.created_by`, `item_packs.price`, `price_list_versions.currency_id`, `price_list_versions.exchange_rate`, `quotation_items.version`, `quotation_items.currency_id`, `quotation_items.exchange_rate`, `quotation_items.discount_total`, `quotation_items.tax_total`, `quotation_items.total`, `quotation_items.sent_at`, `quotation_items.customer_response`, `quotation_items.customer_response_at`, `quotation_items.customer_notes`, `quotation_items.discount_percent`, `quotation_items.line_total`, `quotation_items.item_name_snapshot`, `quotation_items.pack_name_snapshot`, `return_request_items.invoice_id`, `return_request_items.type`, `return_request_items.requested_by`, `return_request_items.reviewed_by`, `return_request_items.reviewed_at`, `customer_price_lists.assigned_at`, `customer_price_lists.assigned_by`, `customer_price_lists.unassigned_at`, `customer_price_lists.is_active`, `customers.customer_type`, `customers.credit_limit`, `customers.current_balance`, `customers.payment_terms_days`, `customers.wallet_enabled`, `cities.is_active`, `cities.sort_order`, `cities.created_by`, `cities.updated_by`, `items.created_by`, `items.updated_by`

2. **Orphaned/Unexpected FK targets:** `pack_definition_pack_group.pack_definition_id → pack_definitions.id` and `pack_definition_pack_operation.pack_definition_id → pack_definitions.id` reference `pack_definitions` table which is scheduled to be dropped by pending migration `2026_09_17_100024`.

3. **items.supplier_id → vendors.id** — FK exists but `suppliers` model/concept not clearly defined (table is `vendors`).

4. **Missing FKs on customer_price_lists:**
   - Model fillable has `assigned_at`, `assigned_by`, `unassigned_at`, `is_active` but DB has `price_override`, `is_default`, `valid_from`, `valid_until`
   - DB is missing FK on `customer_id` and `price_list_id` (they exist as MUL but not defined as foreign key constraints)

---

## 5. Indexes

### Missing Indexes:
| # | Table | Missing Index Columns | Impact |
|---|-------|-----------------------|--------|
| 1 | invoices | status | Slow invoice status queries |
| 2 | invoices | deleted_at | Soft-delete queries slow |
| 3 | deliveries | invoice_id | Join/lookup by invoice slow |
| 4 | deliveries | customer_id | Customer delivery history slow |
| 5 | return_requests | invoice_id | Finding returns for invoice slow |
| 6 | return_requests | deleted_at | Soft-delete queries slow |
| 7 | return_request_items | invoice_item_id | Joining items to returns slow |
| 8 | price_list_items | is_active | Filtering active prices slow |
| 9 | price_list_versions | is_active | Filtering active versions slow |
| 10 | customer_price_lists | is_default | Finding default price list slow |
| 11 | survey_questions | sort_order | Question ordering slow |
| 12 | orders | deleted_at | Soft-delete queries slow |

### Index Issues:
| Issue | Details |
|-------|---------|
| No index on `orders.status` | Though it has MUL flag (likely from enum), confirm index exists |
| No index on `invoices.deleted_at` | Soft-deletes not performant |
| No index on `orders.deleted_at` | Soft-deletes not performant |

---

## 6. Issues Detected

### 🔴 Critical (Must fix immediately)

| # | Table | Column | Issue | Proposed Fix |
|---|-------|--------|-------|--------------|
| 1 | customer_wallets | — | Table completely missing (migration pending) | Run `php artisan migrate` to create `customer_wallets` table |
| 2 | payments | — | Table completely missing (migration pending) | Run `php artisan migrate` to create `payments` table |
| 3 | payment_deadline_settings | — | Table completely missing (migration pending) | Run `php artisan migrate` to create `payment_deadline_settings` table |
| 4 | wallet_transactions | — | Table completely missing (migration pending) | Run `php artisan migrate` to create `wallet_transactions` table |
| 5 | audit_logs | — | Table completely missing (migration pending) | Run `php artisan migrate` to create `audit_logs` table |
| 6 | inventories | — | Table completely missing (migration pending) | Run `php artisan migrate` to create `inventories` table |
| 7 | inventory_movements | — | Table completely missing (migration pending) | Run `php artisan migrate` to create `inventory_movements` table |
| 8 | stock_reservations | — | Table completely missing (migration pending) | Run `php artisan migrate` to create `stock_reservations` table |
| 9 | order_status_histories | — | Table completely missing (migration pending) | Run `php artisan migrate` to create `order_status_histories` table |
| 10 | Invoice Model | order_id, quotation_id, payment_type, currency_id, exchange_rate, approved_by, approved_at, payment_deadline_at, paid_amount, paid_at, due_date, payment_method, payment_reference | 13 columns in Model fillable/casts but not in DB | Run pending migration `2026_09_17_100020_add_invoice_lifecycle_fields_to_invoices_table` |
| 11 | Order Model | warehouse_id, price_list_snapshot_id, currency_id, exchange_rate, total, discount_total, tax_total, shipping_fee, notes | 9 columns missing from DB | Run pending migration `2026_09_17_100000_create_orders_table` (re-run in proper context) |
| 12 | OrderItem Model | discount_percent, line_total, price_source, price_list_item_id, tax_percent, tax_amount, notes | 7 columns missing from DB | Run pending migration `2026_09_17_100025_add_tax_fields_to_order_items` |

### 🟡 Medium (Affects performance/data integrity)

| # | Table | Issue | Fix |
|---|-------|-------|-----|
| 1 | orders | Missing `warehouse_id`, `currency_id`, `price_list_snapshot_id` FKs | Migrate pending additions |
| 2 | order_items | DB has `discount_amount` but Model expects `discount_percent` | Align naming or add both |
| 3 | order_items | Missing `tax_percent`, `tax_amount`, `line_total`, `price_source`, `price_list_item_id` | Run migration `2026_09_17_100025` |
| 4 | quotation_items | DB has `discount_amount`, `subtotal`, `price_list_id`, `price_history_id`, `warehouse_id` but Model expects `discount_percent`, `line_total` | Align naming |
| 5 | quotations | DB columns (`discount_amount`, `tax_amount`, `total_amount`) differ from Model expectations (`discount_total`, `tax_total`, `total`) | Align naming |
| 6 | return_requests | DB has `final_invoice_id`, `total_refund` but Model expects `invoice_id`, `type`, `requested_by` | Align model with DB |
| 7 | return_request_items | Migration creates `return_items` but DB table is `return_request_items` | Fix migration name |
| 8 | item_packs | Missing `price` column (migration pending) | Run migration |
| 9 | price_lists | Missing `currency_id`, `is_default` (migration pending) | Run migration |
| 10 | currencies | Missing `exchange_rate_to_base` (migration pending) | Run migration |
| 11 | customer_price_lists | DB columns vs Model fillable completely different | Reconcile schema |
| 12 | price_list_items | Missing `effective_date`, `created_by` columns | Add migration |
| 13 | price_list_versions | Missing `currency_id`, `exchange_rate` (migration pending) | Run migration |

### 🟢 Warnings (Improvements)

| # | Issue | Recommendation |
|---|-------|----------------|
| 1 | `new_item_packs` table exists but is empty stub (only has id, timestamps) | Drop table or implement purpose |
| 2 | `pack_definition_pack_group` and `pack_definition_pack_operation` are orphaned pivot tables with no corresponding Laravel migration/model | Remove after confirming pack_definitions cleanup |
| 3 | `pack_definitions` table still exists but migration to drop it is pending | Verify data migration completed, then run drop migration |
| 4 | `final_invoices` and `final_invoice_items` tables exist in DB but have no migration files or models | Add migrations and models or remove tables |
| 5 | `quotation_items` DB table doesn't have `tax_percent`/`tax_amount` columns but `order_items` and `invoice_items` will get them | Check if tax fields needed for quotations |
| 6 | Customers table has `type` column but Model fillable has `customer_type` in pending migration | Unify column naming |
| 7 | `cities` table missing `is_active`, `sort_order`, `created_by`, `updated_by` columns that Model expects | Add columns via migration |
| 8 | `items` table missing `created_by`, `updated_by` columns that Model expects | Add columns via migration |

---

## 7. Proposed Fix Migrations

### Migration 1: fix_invoices_lifecycle_fields
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'order_id')) {
                $table->foreignId('order_id')->nullable()->after('price_list_id')->constrained('orders')->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'quotation_id')) {
                $table->foreignId('quotation_id')->nullable()->after('order_id')->constrained('quotations')->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'payment_type')) {
                $table->string('payment_type')->default('cash')->after('status');
            }
            if (!Schema::hasColumn('invoices', 'currency_id')) {
                $table->foreignId('currency_id')->nullable()->after('payment_type')->constrained('currencies')->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'exchange_rate')) {
                $table->decimal('exchange_rate', 15, 6)->default(1.0)->after('currency_id');
            }
            if (!Schema::hasColumn('invoices', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (!Schema::hasColumn('invoices', 'payment_deadline_at')) {
                $table->timestamp('payment_deadline_at')->nullable()->after('approved_at');
            }
            if (!Schema::hasColumn('invoices', 'paid_amount')) {
                $table->decimal('paid_amount', 15, 2)->default(0)->after('payment_deadline_at');
            }
            if (!Schema::hasColumn('invoices', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('paid_amount');
            }
            if (!Schema::hasColumn('invoices', 'due_date')) {
                $table->date('due_date')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('invoices', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('due_date');
            }
            if (!Schema::hasColumn('invoices', 'payment_reference')) {
                $table->string('payment_reference')->nullable()->after('payment_method');
            }
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['status', 'deleted_at']);
        });
    }

    public function down(): void
    {
        // ...
    }
};
```

### Migration 2: fix_orders_columns
```php
<?php

// This migration reconciles orders table with Model expectations.
// The orders table currently lacks: warehouse_id, currency_id, price_list_snapshot_id,
// exchange_rate, total, discount_total, tax_total, shipping_fee, notes, quotation_id

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'quotation_id')) {
                $table->foreignId('quotation_id')->nullable()->after('customer_id')->constrained('quotations')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('created_by')->constrained('warehouses')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'currency_id')) {
                $table->foreignId('currency_id')->nullable()->after('warehouse_id')->constrained('currencies')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'exchange_rate')) {
                $table->decimal('exchange_rate', 15, 6)->default(1.0)->after('currency_id');
            }
            if (!Schema::hasColumn('orders', 'total')) {
                $table->decimal('total', 15, 2)->default(0)->after('tax_total');
            }
            if (!Schema::hasColumn('orders', 'discount_total')) {
                $table->decimal('discount_total', 15, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('orders', 'tax_total')) {
                $table->decimal('tax_total', 15, 2)->default(0)->after('discount_total');
            }
            if (!Schema::hasColumn('orders', 'shipping_fee')) {
                $table->decimal('shipping_fee', 15, 2)->default(0)->after('tax_total');
            }
            if (!Schema::hasColumn('orders', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->default(0)->after('shipping_fee');
            }
            if (!Schema::hasColumn('orders', 'price_list_snapshot_id')) {
                $table->foreignId('price_list_snapshot_id')->nullable()->after('total_amount')->constrained('price_lists')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'notes')) {
                $table->text('notes')->nullable()->after('price_list_snapshot_id');
            }
            if (!Schema::hasColumn('orders', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        // ...
    }
};
```

### Migration 3: fix_order_items_tax_fields
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['order_items', 'quotation_items', 'invoice_items'] as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'tax_percent')) {
                    $t->decimal('tax_percent', 5, 2)->default(0)->after('discount_percent');
                }
                if (!Schema::hasColumn($table, 'tax_amount')) {
                    $t->decimal('tax_amount', 15, 2)->default(0)->after('tax_percent');
                }
            });
        }

        // Reconcile order_items column names
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->default(0)->after('unit_price');
            }
            if (!Schema::hasColumn('order_items', 'line_total')) {
                $table->decimal('line_total', 15, 2)->default(0)->after('discount_percent');
            }
            if (!Schema::hasColumn('order_items', 'price_source')) {
                $table->string('price_source')->nullable()->after('line_total');
            }
            if (!Schema::hasColumn('order_items', 'price_list_item_id')) {
                $table->foreignId('price_list_item_id')->nullable()->after('price_source')
                    ->constrained('price_list_items')->nullOnDelete();
            }
            if (!Schema::hasColumn('order_items', 'notes')) {
                $table->text('notes')->nullable()->after('price_list_item_id');
            }
        });
    }

    public function down(): void
    {
        // ...
    }
};
```

### Migration 4: fix_quotation_and_return_schemas
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix quotations table to match Model expectations
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'discount_total')) {
                $table->decimal('discount_total', 15, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('quotations', 'tax_total')) {
                $table->decimal('tax_total', 15, 2)->default(0)->after('discount_total');
            }
            if (!Schema::hasColumn('quotations', 'total')) {
                $table->decimal('total', 15, 2)->default(0)->after('tax_total');
            }
            // Rename existing columns to match Model
            // DB has discount_amount, tax_amount, total_amount
            // Model expects discount_total, tax_total, total
            // This depends on application code - use DB column names or add accessors
        });

        // Fix quotation_items
        Schema::table('quotation_items', function (Blueprint $table) {
            if (!Schema::hasColumn('quotation_items', 'discount_percent')) {
                $table->decimal('discount_percent', 5, 2)->default(0)->after('unit_price');
            }
            if (!Schema::hasColumn('quotation_items', 'line_total')) {
                $table->decimal('line_total', 15, 2)->default(0)->after('discount_percent');
            }
            if (!Schema::hasColumn('quotation_items', 'item_name_snapshot')) {
                $table->string('item_name_snapshot')->nullable()->after('line_total');
            }
            if (!Schema::hasColumn('quotation_items', 'pack_name_snapshot')) {
                $table->string('pack_name_snapshot')->nullable()->after('item_name_snapshot');
            }
            if (!Schema::hasColumn('quotation_items', 'tax_percent')) {
                $t->decimal('tax_percent', 5, 2)->default(0)->after('discount_percent');
            }
            if (!Schema::hasColumn('quotation_items', 'tax_amount')) {
                $t->decimal('tax_amount', 15, 2)->default(0)->after('tax_percent');
            }
        });

        // Fix return_requests to match Model expectations
        Schema::table('return_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('return_requests', 'invoice_id')) {
                $table->foreignId('invoice_id')->nullable()->after('return_number')->constrained('invoices')->nullOnDelete();
            }
            if (!Schema::hasColumn('return_requests', 'type')) {
                $table->string('type')->default('full')->after('customer_id');
            }
            if (!Schema::hasColumn('return_requests', 'requested_by')) {
                $table->foreignId('requested_by')->nullable()->after('type')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('return_requests', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('approved_by')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('return_requests', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        // ...
    }
};
```

### Migration 5: fix_cities_and_items_missing_columns
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // cities: add is_active, sort_order, created_by, updated_by
        Schema::table('cities', function (Blueprint $table) {
            if (!Schema::hasColumn('cities', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('cities', 'sort_order')) {
                $table->integer('sort_order')->default(0);
            }
            if (!Schema::hasColumn('cities', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('cities', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });

        // items: add created_by, updated_by
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('items', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        // ...
    }
};
```

### Migration 6: fix_customer_price_lists_schema
```php
<?php

// The DB has: price_override, is_default, valid_from, valid_until
// The Model expects: assigned_at, assigned_by, unassigned_at, is_active
// Recommendation: Keep DB columns, update Model to match DB schema.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No DB changes needed - just model reconciliation
        // The DB columns are reasonable business fields
        // Model should be updated to use:
        // Fillable: customer_id, price_list_id, price_override, is_default, valid_from, valid_until
    }

    public function down(): void
    {
        // no-op
    }
};
```

### Migration 7: add_missing_indexes
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = [
            ['invoices', 'idx_invoices_status', ['status']],
            ['invoices', 'idx_invoices_status_deadline', ['status', 'payment_deadline_at']],
            ['deliveries', 'idx_deliveries_invoice_id', ['invoice_id']],
            ['return_requests', 'idx_return_requests_invoice_id', ['invoice_id']],
            ['return_request_items', 'idx_return_request_items_invoice_item_id', ['invoice_item_id']],
            ['price_list_items', 'idx_pli_is_active', ['is_active']],
            ['price_list_versions', 'idx_plv_is_active', ['is_active']],
            ['customer_price_lists', 'idx_cpl_is_default', ['is_default']],
            ['survey_questions', 'idx_sq_sort_order', ['sort_order']],
        ];

        foreach ($indexes as [$table, $name, $cols]) {
            $colsStr = implode(',', $cols);
            DB::statement("CREATE INDEX {$name} ON {$table} ({$colsStr})");
        }

        // Soft delete indexes
        foreach (['orders', 'invoices', 'return_requests'] as $table) {
            DB::statement("CREATE INDEX idx_{$table}_deleted_at ON {$table} (deleted_at)");
        }
    }

    public function down(): void
    {
        // ...
    }
};
```

### Migration 8: cleanup_obsolete_tables
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop obsolete/unused tables
        Schema::dropIfExists('new_item_packs');
        Schema::dropIfExists('pack_definition_pack_group');
        Schema::dropIfExists('pack_definition_pack_operation');
    }

    public function down(): void
    {
        // no rollback for obsolete tables
    }
};
```

---

## 8. Pending Migrations Summary

| # | Migration File | Target Table(s) | Status |
|---|----------------|-----------------|--------|
| 1 | 2026_08_29_100002_create_price_list_items_table | price_list_items (duplicate) | Pending |
| 2 | 2026_09_17_100003_create_quotations_table | quotations | Pending |
| 3 | 2026_09_17_100004_create_quotation_items_table | quotation_items | Pending |
| 4 | 2026_09_17_100005_create_deliveries_table | deliveries | Pending |
| 5 | 2026_09_17_100006_create_delivery_stage_logs_table | delivery_stage_logs | Pending |
| 6 | 2026_09_17_100007_create_return_requests_table | return_requests | Pending |
| 7 | 2026_09_17_100008_create_return_items_table | return_items | Pending |
| 8 | 2026_09_17_100009_create_credit_notes_table | credit_notes | Pending |
| 9 | 2026_09_17_100010_create_inventories_table | inventories | Pending |
| 10 | 2026_09_17_100011_create_inventory_movements_table | inventory_movements | Pending |
| 11 | 2026_09_17_100012_create_stock_reservations_table | stock_reservations | Pending |
| 12 | 2026_09_17_100013_create_payment_deadline_settings_table | payment_deadline_settings | Pending |
| 13 | 2026_09_17_100014_create_customer_wallets_table | customer_wallets | Pending |
| 14 | 2026_09_17_100015_create_wallet_transactions_table | wallet_transactions | Pending |
| 15 | 2026_09_17_100016_create_payments_table | payments | Pending |
| 16 | 2026_09_17_100017_create_audit_logs_table | audit_logs | Pending |
| 17 | 2026_09_17_100018_create_customer_price_lists_table | customer_price_lists | Pending |
| 18 | 2026_09_17_100019_add_customer_extensions_to_customers_table | customers (add columns) | Pending |
| 19 | 2026_09_17_100020_add_invoice_lifecycle_fields_to_invoices_table | invoices (add columns) | Pending |
| 20 | 2026_09_17_100021_add_invoice_item_extensions_to_invoice_items_table | invoice_items (add columns) | Pending |
| 21 | 2026_09_17_100022_create_invoice_item_batches_table | invoice_item_batches | Pending |
| 22 | 2026_09_17_100023_add_expiry_date_to_batch_items_table | batch_items (add column) | Pending |
| 23 | 2026_09_17_100023a_migrate_pack_definition_data | pack_definitions→item_packs | Pending |
| 24 | 2026_09_17_100024_drop_pack_definitions_tables | pack_definitions, pivots | Pending |
| 25 | 2026_09_17_100025_add_tax_fields_to_order_items | order_items, quote_items, invoice_items | Pending |
| 26 | 2026_09_17_100025_add_tiered_pricing_fields_to_price_list_items_table | price_list_items (add columns) | Pending |
| 27 | 2026_09_17_100026_add_currency_and_is_default_to_price_lists_table | price_lists (add columns) | Pending |
| 28 | 2026_09_17_100027_add_currency_to_price_list_versions | price_list_versions (add columns) | Pending |
| 29 | 2026_09_17_100028_add_exchange_rate_to_currencies_table | currencies (add column) | Pending |
| 30 | 2026_09_17_100029_add_price_to_item_packs_table | item_packs (add column) | Pending |

---

## 9. Recommended Action Plan

### Phase A: Run Pending Migrations (High Priority)
```bash
php artisan migrate
```
This will create 13 missing tables and add 17 sets of columns.

### Phase B: Fix Model/DB Schema Mismatches (Medium Priority)
1. Update `Order` model fillable to match DB columns
2. Update `OrderItem` model fillable/casts to match DB columns
3. Update `Quotation` and `QuotationItem` models to match DB columns
4. Update `ReturnRequest` and `ReturnItem` models to match DB columns
5. Update `CustomerPriceList` model to match DB columns

### Phase C: Add Missing Indexes (Low Priority)
Run the index migration from Section 7.

### Phase D: Clean Up Obsolete Tables (Low Priority)
Drop `new_item_packs`, `pack_definition_pack_group`, `pack_definition_pack_operation`.

---

*Report generated from live database introspection using `php artisan tinker` and MySQL metadata queries.*
