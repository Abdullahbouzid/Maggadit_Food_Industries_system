# Gap Analysis — Maggadit Food Industries System

## Order
| # | Field/Element | Current | Required | Action | Migration File | Notes |
|---|--------------|---------|----------|--------|----------------|-------|
| 1 | Order model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100000_create_orders_table.php` | New independent model |
| 2 | Order table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100000_create_orders_table.php` | With all fields per spec |
| 3 | OrderItem model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100001_create_order_items_table.php` | Line items for orders |
| 4 | OrderItem table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100001_create_order_items_table.php` | |
| 5 | OrderStatusHistory model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100002_create_order_status_histories_table.php` | Status transitions |
| 6 | OrderStatusHistory table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100002_create_order_status_histories_table.php` | |
| 7 | OrderStatus enum | ❌ Not exists | ✅ Required | **New** | `app/Enums/OrderStatus.php` | PHP 8.1 Backed Enum |
| 8 | SalesOrderType model | ✅ Exists | ✅ Keep | **Keep** | N/A | Classification only, no FK to Order |

## Quotations
| # | Field/Element | Current | Required | Action | Migration File | Notes |
|---|--------------|---------|----------|--------|----------------|-------|
| 9 | Quotation model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100003_create_quotations_table.php` | Linked to Order |
| 10 | Quotation table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100003_create_quotations_table.php` | |
| 11 | QuotationItem model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100004_create_quotation_items_table.php` | Snapshot pricing |
| 12 | QuotationItem table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100004_create_quotation_items_table.php` | |

## Deliveries
| # | Field/Element | Current | Required | Action | Migration File | Notes |
|---|--------------|---------|----------|--------|----------------|-------|
| 13 | Delivery model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100005_create_deliveries_table.php` | Tracking delivery lifecycle |
| 14 | Delivery table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100005_create_deliveries_table.php` | |
| 15 | DeliveryStageLog model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100006_create_delivery_stage_logs_table.php` | GPS + stage tracking |
| 16 | DeliveryStageLog table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100006_create_delivery_stage_logs_table.php` | |

## Returns & Credit Notes
| # | Field/Element | Current | Required | Action | Migration File | Notes |
|---|--------------|---------|----------|--------|----------------|-------|
| 17 | ReturnRequest model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100007_create_return_requests_table.php` | Full/partial returns |
| 18 | ReturnRequest table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100007_create_return_requests_table.php` | |
| 19 | ReturnItem model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100008_create_return_items_table.php` | Items being returned |
| 20 | ReturnItem table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100008_create_return_items_table.php` | |
| 21 | CreditNote model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100009_create_credit_notes_table.php` | Auto-generated on return |
| 22 | CreditNote table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100009_create_credit_notes_table.php` | |

## Invoicing (Modify existing)
| # | Field/Element | Current | Required | Action | Migration File | Notes |
|---|--------------|---------|----------|--------|----------------|-------|
| 23 | Invoice model | ✅ Exists | ⬆️ Modify | **Modify** | `2026_09_17_100020_add_invoice_lifecycle_fields_to_invoices_table.php` | Add ~16 new fields |
| 24 | Invoice table | ✅ Exists | ⬆️ Modify | **Modify** | `2026_09_17_100020_add_invoice_lifecycle_fields_to_invoices_table.php` | order_id, quotation_id, payment_type, currency_id, exchange_rate, status (extended), approved_by, approved_at, payment_deadline_at, paid_amount, paid_at, due_date, payment_method, payment_reference |
| 25 | InvoiceItem model | ✅ Exists | ⬆️ Modify | **Modify** | `2026_09_17_100021_add_invoice_item_extensions_to_invoice_items_table.php` | Add: batch_item_id, discount_percent, price_source, price_list_item_id, currency_id, exchange_rate |
| 26 | InvoiceItem table | ✅ Exists | ⬆️ Modify | **Modify** | `2026_09_17_100021_add_invoice_item_extensions_to_invoice_items_table.php` | Same as above |
| 27 | Invoice status enum | ✅ `status` (string) | ⬆️ Modify | **Modify** | `2026_09_17_100020_add_invoice_lifecycle_fields_to_invoices_table.php` | Extend to: pending_approval\|approved\|rejected\|paid\|partially_paid\|cancelled\|expired |
| 28 | InvoiceItemBatch (FEFO) | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100022_create_invoice_item_batches_table.php` | FEFO allocation tracking |

## Inventory & Reservations
| # | Field/Element | Current | Required | Action | Migration File | Notes |
|---|--------------|---------|----------|--------|----------------|-------|
| 29 | Inventory model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100010_create_inventories_table.php` | Aggregated balance per Item+Pack+Warehouse |
| 30 | Inventory table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100010_create_inventories_table.php` | UNIQUE(item_id, item_pack_id, warehouse_id) |
| 31 | InventoryMovement model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100011_create_inventory_movements_table.php` | Audit trail for all stock changes |
| 32 | InventoryMovement table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100011_create_inventory_movements_table.php` | |
| 33 | StockReservation model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100012_create_stock_reservations_table.php` | Pre-sale reservations |
| 34 | StockReservation table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100012_create_stock_reservations_table.php` | |
| 35 | InventoryService | ❌ Not exists | ✅ Required | **New** | N/A (app/Services/InventoryService.php) | FEFO engine |
| 36 | BatchItemStock.remaining_quantity | ✅ Exists (attribute) | ⬆️ Extend | **Modify** | N/A | Keep existing, use for FEFO |
| 37 | BatchItem.expiry_date | ❌ Not exists | ✅ Required | **New column** | `2026_09_17_100023_add_expiry_date_to_batch_items_table.php` | Needed for FEFO |

## Payments
| # | Field/Element | Current | Required | Action | Migration File | Notes |
|---|--------------|---------|----------|--------|----------------|-------|
| 38 | Payment model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100016_create_payments_table.php` | Payment records |
| 39 | Payment table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100016_create_payments_table.php` | |
| 40 | PaymentDeadlineSetting model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100013_create_payment_deadline_settings_table.php` | Configurable deadline |
| 41 | PaymentDeadlineSetting table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100013_create_payment_deadline_settings_table.php` | |

## Customer Wallet
| # | Field/Element | Current | Required | Action | Migration File | Notes |
|---|--------------|---------|----------|--------|----------------|-------|
| 42 | CustomerWallet model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100014_create_customer_wallets_table.php` | Per-customer, per-currency |
| 43 | CustomerWallet table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100014_create_customer_wallets_table.php` | |
| 44 | WalletTransaction model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100015_create_wallet_transactions_table.php` | All wallet movements |
| 45 | WalletTransaction table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100015_create_wallet_transactions_table.php` | |
| 46 | WalletService | ❌ Not exists | ✅ Required | **New** | N/A (app/Services/WalletService.php) | Deposit/Debit/Refund |
| 47 | Customer.customer_type | ❌ Not exists | ✅ Required | **Modify** | `2026_09_17_100019_add_customer_extensions_to_customers_table.php` | Add enum field: cash\|credit |
| 48 | Customer.credit_limit | ❌ Not exists | ✅ Required | **Modify** | `2026_09_17_100019_add_customer_extensions_to_customers_table.php` | Add decimal field |
| 49 | Customer.current_balance | ❌ Not exists | ✅ Required | **Modify** | `2026_09_17_100019_add_customer_extensions_to_customers_table.php` | Add decimal field |
| 50 | Customer.payment_terms_days | ❌ Not exists | ✅ Required | **Modify** | `2026_09_17_100019_add_customer_extensions_to_customers_table.php` | Add integer field |
| 51 | Customer.wallet_enabled | ❌ Not exists | ✅ Required | **Modify** | `2026_09_17_100019_add_customer_extensions_to_customers_table.php` | Add boolean field |

## Audit & Compliance
| # | Field/Element | Current | Required | Action | Migration File | Notes |
|---|--------------|---------|----------|--------|----------------|-------|
| 52 | AuditLog model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100017_create_audit_logs_table.php` | Track all changes |
| 53 | AuditLog table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100017_create_audit_logs_table.php` | |
| 54 | CustomerPriceList model | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100018_create_customer_price_lists_table.php` | History of price list assignments |
| 55 | CustomerPriceList table | ❌ Not exists | ✅ Required | **New** | `2026_09_17_100018_create_customer_price_lists_table.php` | |
| 56 | PriceListItem.min_quantity | ❌ Not exists | ✅ Required | **Modify** | `2026_09_17_100025_add_tiered_pricing_fields_to_price_list_items_table.php` | Tiered pricing |
| 57 | PriceListItem.discount_percent | ❌ Not exists | ✅ Required | **Modify** | `2026_09_17_100025_add_tiered_pricing_fields_to_price_list_items_table.php` | Tiered pricing |
| 58 | PriceListItem.item_pack_id | ❌ Not exists | ✅ Required | **Modify** | `2026_09_17_100025_add_tiered_pricing_fields_to_price_list_items_table.php` | |
| 59 | PriceList.currency_id | ❌ Not exists | ✅ Required | **Modify** | `2026_09_17_100026_add_currency_and_is_default_to_price_lists_table.php` | Multi-currency support |
| 60 | PriceList.is_default | ❌ Not exists | ✅ Required | **Modify** | `2026_09_17_100026_add_currency_and_is_default_to_price_lists_table.php` | For fallback pricing |

## Deletions (PackDefinition & related)
| # | Field/Element | Current | Required | Action | Migration File | Notes |
|---|--------------|---------|----------|--------|----------------|-------|
| 61 | PackDefinition model | ✅ Exists | ❌ Remove | **Delete** | `2026_09_17_100024_drop_pack_definitions_tables.php` | Merge useful fields into ItemPack |
| 62 | pack_definitions table | ✅ Exists | ❌ Remove | **Delete** | `2026_09_17_100024_drop_pack_definitions_tables.php` | Data migrated before drop via `2026_09_17_100023a_migrate_pack_definition_data.php` |
| 63 | PackDefinition→PackType FK | ✅ Exists | ❌ Remove | **Delete** | `2026_09_17_100024_drop_pack_definitions_tables.php` | PackType kept, unlinked |
| 64 | PackDefinition→PackCapacity FK | ✅ Exists | ❌ Remove | **Delete** | `2026_09_17_100024_drop_pack_definitions_tables.php` | PackCapacity kept, unlinked |
| 65 | pack_definition_pack_operation pivot | ✅ Exists | ❌ Remove | **Delete** | `2026_09_17_100024_drop_pack_definitions_tables.php` | Move to pack_operations via ItemPack |
| 66 | pack_definition_pack_group pivot | ✅ Exists | ❌ Remove | **Delete** | `2026_09_17_100024_drop_pack_definitions_tables.php` | Move to pack_groups via ItemPack |
| 67 | PackOperation model | ✅ Exists | ⚠️ Check | **Modify** | N/A | Remove PackDefinition relation; keep if used by ItemPack directly |
| 68 | PackGroup model | ✅ Exists | ⚠️ Check | **Modify** | N/A | Remove PackDefinition relation; keep if used by ItemPack directly |
| 69 | PackDefinition→Item FK | ✅ Exists | ❌ Remove | **Delete** | `2026_09_17_100024_drop_pack_definitions_tables.php` | ItemPack keeps Item FK |

---

## Services (Gap Summary)
| Service | Current | Required | Action |
|---------|---------|----------|--------|
| PricingService | ❌ Not exists | ✅ Required | **New** | `app/Services/PricingService.php` |
| InventoryService | ❌ Not exists | ✅ Required | **New** | `app/Services/InventoryService.php` |
| WalletService | ❌ Not exists | ✅ Required | **New** | `app/Services/WalletService.php` |
| OrderLifecycleService | ❌ Not exists | ✅ Required | **New** | `app/Services/OrderLifecycleService.php` |
| InvoiceLifecycleService | ❌ Not exists | ✅ Required | **New** | `app/Services/InvoiceLifecycleService.php` |
| DeliveryService | ❌ Not exists | ✅ Required | **New** | `app/Services/DeliveryService.php` |
| ReturnService | ❌ Not exists | ✅ Required | **New** | `app/Services/ReturnService.php` |
| PaymentDeadlineService | ❌ Not exists | ✅ Required | **New** | `app/Services/PaymentDeadlineService.php` |

## Summary
- **New Models**: 25 (incl. InvoiceItemBatch)
- **Modified Models**: 10 (Invoice, InvoiceItem, Customer, BatchItem, PriceList, PriceListVersion, PriceListVersionItem, PriceListItem, Currency, Offers)
- **Deleted Models**: 1 (PackDefinition) + 2 pivot tables
- **Kept Models**: 108
- **New Services**: 8
- **New Enums**: 6 (OrderStatus, InvoiceStatus, PaymentType, InventoryMovementType, WalletTransactionType, PaymentMethod)
- **New DTOs**: 7 (PriceResult, AvailabilityResult, FefoAllocation, WalletResult, TransitionResult, DeliveryResult, ReturnResult)

---

*Generated: 2026-09-17*
