# Migration Rebuild Plan — Maggadit Food Industries

*Generated: 2026-09-22*

## 1.1 Table Dependency Map (69 Application Tables)

### TIER 0 — Independent (no FK dependencies)
| Table | Model | Notes |
|-------|-------|-------|
| users | User | Laravel default |
| currencies | Currency | Has `exchange_rate_to_base` (pending migration) |
| countries | Country | |
| organizations | Organization | Self-FK on `parent_id` |
| permissions | — | Spatie (no model in app/Models) |
| roles | Role | Spatie (no model in app/Models) |
| item_types | ItemType | |
| categories | Category | |
| brands | Brand | |
| uoms | Uom | |
| pack_types | PackType | |
| pack_capacities | PackCapacity | → uoms |
| pack_operations | PackOperation | |
| pack_groups | PackGroup | |
| vendors | Vendor | (called "suppliers" in audit, actual table name is `vendors`) |
| vehicle_types | VehicleType | |
| warehouse_types | WarehouseType | |
| sales_order_types | SalesOrderType | |
| banners | Banner | |
| faqs | Faq | |
| privacy_policies | PrivacyPolicy | |

### TIER 1 — Depends on TIER 0
| Table | Model | FK Dependencies |
|-------|-------|----------------|
| states | State | → countries |
| cities | City | → countries, states |
| areas | Area | → states, cities (via pending migration) |
| organization_abouts | OrganizationAbout | → organizations |
| contact_messages | ContactMessage | → organizations |
| package_statuses | PackageStatus | → organizations |
| devices | Device | → organizations |
| vehicles | Vehicle | → organizations, vehicle_types |
| warehouses | Warehouse | → organizations, warehouse_types, countries, states, cities, areas |
| item_models | ItemModel | — |
| items | Item | → item_types, categories, brands, item_models, vendors |

### TIER 2 — Depends on TIER 1
| Table | Model | FK Dependencies |
|-------|-------|----------------|
| price_lists | PriceList | → users, currencies |
| customers | Customer | → organizations, price_lists, currencies |
| item_packs | ItemPack | → items, warehouses, pack_types, pack_capacities |
| price_list_items | PriceListItem | → price_lists, items, item_packs |
| price_list_versions | PriceListVersion | → price_lists, users, currencies |
| price_list_version_items | PriceListVersionItem | → price_list_versions, items, item_packs |
| customer_price_lists | CustomerPriceList | → customers, price_lists |
| customer_organization | CustomerOrganization | → customers, organizations |
| customer_favorites | CustomerFavorite | → customers, items |
| offers | Offer | → items, item_packs, price_lists |
| item_images | ItemImage | → items |
| production_batches | ProductionBatch | — |
| batch_items | BatchItem | → production_batches, items, item_packs, warehouses |
| batch_item_stock | BatchItemStock | → batch_items, warehouses |
| batch_movements | BatchMovement | → batch_items, warehouses |
| notifications | Notification | — (customers stored as JSON) |
| notification_reads | NotificationRead | → notifications, customers |
| surveys | Survey | → users |
| survey_questions | SurveyQuestion | → surveys |
| survey_responses | SurveyResponse | → surveys, customers |
| survey_answers | SurveyAnswer | → survey_responses, survey_questions |
| model_has_permissions | — | → permissions |
| model_has_roles | — | → roles |
| role_has_permissions | — | → roles, permissions |

### TIER 3 — Transactional (Depends on TIER 2)
| Table | Model | FK Dependencies |
|-------|-------|----------------|
| orders | Order | → customers, warehouses, price_lists, currencies, users |
| order_items | OrderItem | → orders, items, item_packs, price_list_items, warehouses |
| order_status_histories | OrderStatusHistory | → orders, users |
| quotations | Quotation | → orders, customers, currencies, users |
| quotation_items | QuotationItem | → quotations, items, item_packs, price_list_version_items, price_lists, warehouses |
| invoices | Invoice | → customers, orders, quotations, currencies, users, price_lists |
| invoice_items | InvoiceItem | → invoices, items, item_packs, warehouses, offers, price_lists, price_list_version_items |
| invoice_item_batches | InvoiceItemBatch | → invoice_items, batch_items |
| payments | Payment | → invoices, customers, currencies, users |
| deliveries | Delivery | → invoices, orders, customers, vehicles, users |
| delivery_stage_logs | DeliveryStageLog | → deliveries, users |
| return_requests | ReturnRequest | → orders, invoices, customers, users |
| return_items | ReturnItem | → return_requests, invoice_items, items, item_packs, batch_items |
| credit_notes | CreditNote | → return_requests, customers, invoices, currencies |
| inventories | Inventory | → items, item_packs, warehouses |
| inventory_movements | InventoryMovement | → inventories, items, item_packs, warehouses, batch_items, users |
| stock_reservations | StockReservation | → items, item_packs, warehouses |
| payment_deadline_settings | PaymentDeadlineSetting | → users |
| customer_wallets | CustomerWallet | → customers, currencies |
| wallet_transactions | WalletTransaction | → customer_wallets, currencies, users |
| audit_logs | AuditLog | → users |
| offers | Offer | → items, item_packs, price_lists |

### Obsolete Tables (to be dropped)
| Table | Reason |
|-------|--------|
| pack_definitions | Migration pending to drop; Model fillable expects columns that don't exist |
| pack_definition_pack_group | Orphaned pivot; no migration, no model |
| pack_definition_pack_operation | Orphaned pivot; no migration, no model |
| new_item_packs | Empty stub table; no purpose, no references |
| final_invoices | No migration, no model; completely orphaned |
| final_invoice_items | No migration, no model; completely orphaned |

---

## 1.2 Final Creation Order

1. users
2. currencies
3. countries
4. organizations
5. permissions _(Spatie)_
6. roles _(Spatie)_
7. item_types
8. categories
9. brands
10. uoms
11. pack_types
12. pack_capacities
13. pack_operations
14. pack_groups
15. vendors
16. vehicle_types
17. warehouse_types
18. sales_order_types
19. banners
20. faqs
21. privacy_policies
22. model_has_permissions
23. model_has_roles
24. role_has_permissions
25. states
26. cities
27. areas
28. organization_abouts
29. contact_messages
30. package_statuses
31. devices
32. vehicles
33. warehouses
34. item_models
35. items
36. price_lists
37. customers
38. customer_organization
39. customer_favorites
40. item_packs
41. price_list_items
42. price_list_versions
43. price_list_version_items
44. customer_price_lists
45. offers
46. item_images
47. production_batches
48. batch_items
49. batch_item_stock
50. batch_movements
51. orders
52. order_items
53. order_status_histories
54. quotations
55. quotation_items
56. invoices
57. invoice_items
58. invoice_item_batches
59. payments
60. deliveries
61. delivery_stage_logs
62. return_requests
63. return_items
64. credit_notes
65. inventories
66. inventory_movements
67. stock_reservations
68. payment_deadline_settings
69. customer_wallets
70. wallet_transactions
71. audit_logs
72. notifications
73. notification_reads
74. surveys
75. survey_questions
76. survey_responses
77. survey_answers

---

## 1.3 Tables to Delete

| Table | Reason | Safe to Delete? |
|-------|--------|----------------|
| pack_definitions | Referenced by ItemPack model + ItemController; pending migration to drop | ⚠️ Only after code cleanup |
| pack_definition_pack_group | Orphaned pivot, no FK dependencies in DB | ✅ |
| pack_definition_pack_operation | Orphaned pivot, no FK dependencies in DB | ✅ |
| new_item_packs | Empty stub, zero references in code | ✅ |
| final_invoices | No migration, no model, zero references in code | ✅ |
| final_invoice_items | No migration, no model, zero references in code | ✅ |

---

## 1.4 Restructuring Plan

### Proposed Migration Structure (11 migrations):

| # | Migration Name | Tier | Tables |
|---|----------------|------|--------|
| 1 | `2026_09_22_000001_create_tier0_system_tables` | TIER 0 | users, currencies, countries, organizations, permissions, roles, model_has_permissions, model_has_roles, role_has_permissions |
| 2 | `2026_09_22_000002_create_tier0_product_catalogs` | TIER 0 | item_types, categories, brands, uoms, item_models, pack_types, pack_capacities, pack_operations, pack_groups |
| 3 | `2026_09_22_000003_create_tier0_suppliers_vehicles` | TIER 0 | vendors, vehicle_types, warehouse_types, sales_order_types |
| 4 | `2026_09_22_000004_create_tier1_geo_location` | TIER 1 | states, cities, areas |
| 5 | `2026_09_22_000005_create_tier1_warehouses_users` | TIER 1 | warehouses, organization_abouts, contact_messages, package_statuses, devices, vehicles |
| 6 | `2026_09_22_000006_create_tier2_pricing` | TIER 2 | price_lists, price_list_versions, price_list_items, price_list_version_items, customer_price_lists |
| 7 | `2026_09_22_000007_create_tier2_items_customers` | TIER 2 | items, item_packs, item_images, customers, customer_organization, customer_favorites, offers |
| 8 | `2026_09_22_000008_create_tier2_batches_notifications` | TIER 2 | production_batches, batch_items, batch_item_stock, batch_movements, notifications, notification_reads, banners, faqs, privacy_policies |
| 9 | `2026_09_22_000009_create_tier3_transactional` | TIER 3 | orders, order_items, order_status_histories, quotations, quotation_items, invoices, invoice_items, invoice_item_batches, payments, deliveries, delivery_stage_logs, return_requests, return_items, credit_notes, inventories, inventory_movements, stock_reservations, payment_deadline_settings, customer_wallets, wallet_transactions, audit_logs |
| 10 | `2026_09_22_000010_create_tier3_survey_tables` | TIER 3 | surveys, survey_questions, survey_responses, survey_answers |
| 11 | `2026_09_22_000011_drop_obsolete_tables` | Cleanup | pack_definitions, pack_definition_pack_group, pack_definition_pack_operation, new_item_packs, final_invoices, final_invoice_items |
| 12 | `2026_09_22_000012_add_performance_indexes` | All | Add missing indexes across all tables |

**Total proposed migrations: 12** (11 structure + 1 index consolidation)

Note: Migration #12 (drop_obsolete_tables) should only run after code references to `pack_definitions` are removed (ItemPack model + ItemController validation rules).

---

## 1.5 Full Dependency Matrix

| Table | Direct FK Dependencies (must exist before) |
|-------|-------------------------------------------|
| model_has_permissions | permissions |
| model_has_roles | roles |
| role_has_permissions | roles, permissions |
| states | countries |
| cities | countries, states |
| areas | states, cities |
| organization_abouts | organizations |
| contact_messages | organizations |
| package_statuses | organizations |
| devices | organizations |
| vehicles | organizations, vehicle_types |
| warehouses | organizations, warehouse_types, countries, states, cities, areas |
| items | item_types, categories, brands, item_models, vendors |
| price_lists | users, currencies |
| customers | organizations, price_lists, currencies |
| item_packs | items, warehouses, pack_types, pack_capacities |
| price_list_items | price_lists, items, item_packs |
| price_list_versions | price_lists, users, currencies |
| price_list_version_items | price_list_versions, items, item_packs |
| customer_price_lists | customers, price_lists |
| customer_organization | customers, organizations |
| customer_favorites | customers, items |
| offers | items, item_packs, price_lists |
| item_images | items |
| production_batches | — |
| batch_items | production_batches, items, item_packs, warehouses |
| batch_item_stock | batch_items, warehouses |
| batch_movements | batch_items, warehouses |
| orders | customers, warehouses, price_lists, currencies, users |
| order_items | orders, items, item_packs, price_list_items, warehouses |
| order_status_histories | orders, users |
| quotations | orders, customers, currencies, users |
| quotation_items | quotations, items, item_packs, price_list_version_items, price_lists, warehouses |
| invoices | customers, orders, quotations, currencies, users, price_lists |
| invoice_items | invoices, items, item_packs, warehouses, offers, price_lists, price_list_version_items |
| invoice_item_batches | invoice_items, batch_items |
| payments | invoices, customers, currencies, users |
| deliveries | invoices, orders, customers, vehicles, users |
| delivery_stage_logs | deliveries, users |
| return_requests | orders, invoices, customers, users |
| return_items | return_requests, invoice_items, items, item_packs, batch_items |
| credit_notes | return_requests, customers, invoices, currencies |
| inventories | items, item_packs, warehouses |
| inventory_movements | inventories, items, item_packs, warehouses, batch_items, users |
| stock_reservations | items, item_packs, warehouses |
| payment_deadline_settings | users |
| customer_wallets | customers, currencies |
| wallet_transactions | customer_wallets, currencies, users |
| audit_logs | users |
| notifications | — |
| notification_reads | notifications, customers |
| surveys | users |
| survey_questions | surveys |
| survey_responses | surveys, customers |
| survey_answers | survey_responses, survey_questions |

---

## Notes & Decisions

1. **`customers.price_list_id`**: Customers table has `price_list_id` FK and depends on `price_lists`. `price_lists` must be created BEFORE `customers`. In the creation order: `price_lists` (position 36) precedes `customers` (position 37).

2. **`customer_price_lists`** is a Tier 2 table (depends on both `customers` and `price_lists`). It must be created after both.

3. **`quotations.order_id`** references `orders`, so quotations must be created after orders. The creation order reflects this: `orders` (position 51) precedes `quotations` (position 54).

4. **`final_invoices` and `final_invoice_items`**: Confirmed zero references in code. Safe to drop.

5. **`pack_definitions`**: Cannot be dropped until `ItemPack` model and `ItemController` stop referencing `pack_definition_id`. Migration #12 (drop_obsolete_tables) must run only after code cleanup.

6. **Spatie permissions**: `model_has_permissions`, `model_has_roles`, `role_has_permissions` tables exist in DB but have no corresponding Models in `app/Models/`. These are handled by Spatie's `Permission` trait, so they don't need models.

7. **`customer_organization` vs `customer_price_lists`**: Both are legitimate separate tables. `customer_organization` is a pivot for customer-organization relationships; `customer_price_lists` maps customers to their specific price lists. Both must be preserved.

8. **areas table**: Has both `state_id` and `city_id` FKs (city_id was added in a follow-up migration). Both reference TIER 1 tables.

---

*Report generated from live database introspection, migration file inspection, and Model analysis.*
