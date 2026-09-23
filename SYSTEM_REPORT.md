# Maggadit Food Industries System - Report

## Overview
Laravel-based inventory/warehouse management system with a dashboard admin panel (Nova design system) and customer mobile app. Bilingual support (Arabic/English), soft deletes, role-based access, multi-level organization structures.

## Total Models: 54

---

## Core Product Models

### Item
**File:** `app/Models/Item.php`

Core product model with 28 fields including code, names (EN/AR), shelf life, stock tolerances, and flags (is_inactive, is_new, is_price_protected, is_power_sku, etc.).

**Relationships:**
| Type | Model | Foreign Key |
|------|-------|-------------|
| belongsTo | ItemType | item_type_id |
| belongsTo | Category | category_id |
| belongsTo | Brand | brand_id |
| belongsTo | ItemModel | model_id |
| belongsTo | Vendor (supplier) | supplier_id |
| hasMany | ItemPack | item_id |
| hasMany | ItemImage | item_id |
| hasMany | PriceListVersionItem | - |

**Accessors:** price, oldPrice, rate, mainImageUrl

---

### ItemType
**File:** `app/Models/ItemType.php`

Item classification with code, name, description, image, is_active, sort_order.

---

### Category
**File:** `app/Models/Category.php`

Product categories.

**Relationships:**
| Type | Model |
|------|-------|
| hasMany | Item |

---

### Brand
**File:** `app/Models/Brand.php`

Product brands with logo and show_in_customer_app flag.

**Relationships:**
| Type | Model |
|------|-------|
| hasMany | Item |

---

### ItemModel
**File:** `app/Models/ItemModel.php`

Item models/specs with code, name (EN/AR), is_active, sort_order.

---

### Uom
**File:** `app/Models/Uom.php`

Units of measure with code, name_en, name_ar, uom_symbol, description.

---

## Supply Chain Models

### Vendor
**File:** `app/Models/Vendor.php`

Suppliers with contact info, commission_percentage, inactive flag.

---

### Warehouse
**File:** `app/Models/Warehouse.php`

Storage facilities with GPS coordinates, address, contact info.

**Relationships:**
| Type | Model |
|------|-------|
| belongsTo | WarehouseType |
| belongsTo | Organization |
| belongsTo | Country |
| belongsTo | State |
| belongsTo | City |
| belongsTo | Area |

---

### WarehouseType
**File:** `app/Models/WarehouseType.php`

Warehouse classifications.

---

### Area
**File:** `app/Models/Area.php`

Geographic areas within warehouses.

---

## Organization & User Management

### Organization
**File:** `app/Models/Organization.php`

Multi-level organization structure with GLN, contact info, location.

**Relationships:**
| Type | Model |
|------|-------|
| belongsTo | Organization (parent) |
| hasMany | Organization (children) |
| hasMany | PackageStatus |
| hasMany | Warehouse |
| hasMany | Vehicle |
| hasMany | Area |
| hasMany | User |
| belongsToMany | Customer (via customer_organization) |
| hasOne | OrganizationAbout |

---

### Customer
**File:** `app/Models/Customer.php`

User management (extends Authenticatable) with organization relationships.

**Relationships:**
| Type | Model |
|------|-------|
| belongsToMany | Organization (via customer_organization) |
| belongsTo | Organization (active_organization_id) |
| belongsTo | PriceList |
| hasMany | CustomerFavorite |
| belongsToMany | Notification (via notification_reads) |

---

### User
**File:** `app/Models/User.php`

System users (admin/staff).

**Relationships:**
| Type | Model |
|------|-------|
| belongsTo | Organization |

---

## Packaging Models

### PackDefinition
**File:** `app/Models/PackDefinition.php`

Pack configurations for items with barcode, dimensions, weight, pricing flags.

**Relationships:**
| Type | Model |
|------|-------|
| belongsTo | Item |
| belongsTo | PackType |
| belongsTo | PackCapacity |
| belongsToMany | PackOperation (via pack_definition_pack_operation) |
| belongsToMany | PackGroup (via pack_definition_pack_group) |

---

### PackType
**File:** `app/Models/PackType.php`

Pack classifications with pieces_per_pack.

**Relationships:**
| Type | Model |
|------|-------|
| hasMany | ItemPack |

---

### PackCapacity
**File:** `app/Models/PackCapacity.php`

Pack capacities with value, uom_id.

**Relationships:**
| Type | Model |
|------|-------|
| belongsTo | Uom |
| hasMany | ItemPack |

---

### PackOperation
**File:** `app/Models/PackOperation.php`

Packaging operations (pack, unpack, etc.).

---

### PackGroup
**File:** `app/Models/PackGroup.php`

Pack operation groups.

**Relationships:**
| Type | Model |
|------|-------|
| belongsToMany | ItemPack (via item_pack_pack_group) |

---

### ItemPack
**File:** `app/Models/ItemPack.php`

Specific item pack configurations with quantity, dimensions, pricing.

**Relationships:**
| Type | Model |
|------|-------|
| belongsTo | Item |
| belongsTo | PackDefinition |
| belongsTo | PackType |
| belongsTo | PackCapacity |
| belongsTo | Warehouse |
| belongsToMany | PackOperation (via item_pack_pack_operation) |
| belongsToMany | PackGroup (via item_pack_pack_group) |

---

### ItemImage
**File:** `app/Models/ItemImage.php`

Product images with sort_order.

**Relationships:**
| Type | Model |
|------|-------|
| belongsTo | Item |

---

## Sales & Pricing

### PriceList
**File:** `app/Models/PriceList.php`

Price lists for customers.

**Relationships:**
| Type | Model |
|------|-------|
| belongsToMany | Customer |

---

### PriceListVersion
**File:** `app/Models/PriceListVersion.php`

Price list versions.

---

### PriceListItem
**File:** `app/Models/PriceListItem.php`

Individual price list items.

**Relationships:**
| Type | Model |
|------|-------|
| belongsTo | Item |

---

### PriceListVersionItem
**File:** `app/Models/PriceListVersionItem.php`

Price list version items.

---

### SalesOrderType
**File:** `app/Models/SalesOrderType.php`

Sales order classifications.

---

### Invoice
**File:** `app/Models/Invoice.php`

Sales invoices.

---

### InvoiceItem
**File:** `app/Models/InvoiceItem.php`

Individual invoice line items.

---

### Offer
**File:** `app/Models/Offer.php`

Product offers (mobile app).

---

## Inventory Management

### ProductionBatch
**File:** `app/Models/ProductionBatch.php`

Production batches.

---

### BatchItem
**File:** `app/Models/BatchItem.php`

Items within batches.

---

### BatchItemStock
**File:** `app/Models/BatchItemStock.php`

Stock levels for batch items.

---

### BatchMovement
**File:** `app/Models/BatchMovement.php`

Stock movements (in/out transfers).

---

### PackageStatus
**File:** `app/Models/PackageStatus.php`

Status tracking for packages.

**Relationships:**
| Type | Model |
|------|-------|
| belongsTo | Organization |

---

## System Support

### Currency
**File:** `app/Models/Currency.php`

Currency definitions.

---

### Country
**File:** `app/Models/Country.php`

Countries.

---

### State
**File:** `app/Models/State.php`

States/Provinces.

---

### City
**File:** `app/Models/City.php`

Cities.

---

### Vehicle
**File:** `app/Models/Vehicle.php`

Delivery vehicles.

---

### VehicleType
**File:** `app/Models/VehicleType.php`

Vehicle classifications.

---

### Device
**File:** `app/Models/Device.php`

Mobile devices (FCM tokens).

---

### Competitor
**File:** `app/Models/Competitor.php`

Competitor information.

---

## Content & Communication

### Banner
**File:** `app/Models/Banner.php`

Promotional banners.

---

### Faq
**File:** `app/Models/Faq.php`

FAQ entries.

---

### Survey
**File:** `app/Models/Survey.php`

Customer surveys.

---

### SurveyQuestion
**File:** `app/Models/SurveyQuestion.php`

Survey questions.

---

### SurveyAnswer
**File:** `app/Models/SurveyAnswer.php`

Survey answers.

---

### SurveyResponse
**File:** `app/Models/SurveyResponse.php`

Survey responses.

---

### ContactMessage
**File:** `app/Models/ContactMessage.php`

Contact form messages.

---

### Notification
**File:** `app/Models/Notification.php`

System notifications.

---

### NotificationRead
**File:** `app/Models/NotificationRead.php`

Read notification tracking.

---

### PrivacyPolicy
**File:** `app/Models/PrivacyPolicy.php`

Privacy policy content.

---

### OrganizationAbout
**File:** `app/Models/OrganizationAbout.php`

Organization about page content.

---

### CustomerFavorite
**File:** `app/Models/CustomerFavorite.php`

Customer favorites/bookmarks.

---

## Relationship Diagram (Simplified)

```
Organization
├── parent → Organization
├── children → Organization[]
├── warehouses → Warehouse[]
├── vehicles → Vehicle[]
├── areas → Area[]
├── users → User[]
├── customers → Customer (pivot: customer_organization)
└── packageStatuses → PackageStatus[]

Customer
├── organizations → Organization (pivot: customer_organization)
├── activeOrganization → Organization
├── priceList → PriceList
└── favorites → CustomerFavorite[]

Item
├── itemType → ItemType
├── category → Category
├── brand → Brand
├── model → ItemModel
├── supplier → Vendor
├── packs → ItemPack[]
├── images → ItemImage[]
└── priceListItems → PriceListVersionItem[]

ItemPack
├── item → Item
├── packDefinition → PackDefinition
├── packType → PackType
├── packCapacity → PackCapacity
├── warehouse → Warehouse
├── packOperations → PackOperation (pivot)
└── packGroups → PackGroup (pivot)

PackDefinition
├── item → Item
├── packType → PackType
├── packCapacity → PackCapacity
├── packOperations → PackOperation (pivot)
└── packGroups → PackGroup (pivot)
```

---

## Key Features
- **Bilingual**: All core models support English/Arabic names
- **Soft Deletes**: Item, Warehouse, Organization
- **Role-Based Access**: Spatie Laravel Permission (Organization, Brand, Vendor)
- **Multi-tenancy**: Organization-based data isolation
- **Mobile App**: Customer app with FCM push notifications
- **API**: Sanctum token authentication for Customer model
- **Inventory**: Batch tracking, stock movements, warehouse management
- **Pricing**: Multi-level price lists with versioning
- **Packaging**: Complex pack definitions with operations and groups

---

*Generated: 2026-09-17*
