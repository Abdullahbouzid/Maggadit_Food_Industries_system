# Kilo Code Prompt — Complete Construction ERP with a Business Central-Style Interface

You are a senior full-stack developer, construction ERP architect, database engineer, and enterprise UI designer. Build the complete working application described below in the current workspace.

## 1. Goal and completion standard

Create a web-based construction management ERP called "Maggadit Construction ERP", with a configurable product name and company branding. Use a real Microsoft SQL Server database.

Deliver one complete browser-based software application. Users open one web address in Edge or Chrome, sign in, and use working forms, approvals, transactions, reports, and administration pages. Every required screen must read/write real business data through the application server to SQL Server. Install the application normally on Windows with IIS and SQL Server; users need only a supported browser.

The complete solution must connect:

1. Project budgets and purchase approvals.
2. Materials received, transferred, issued, consumed, and returned at construction sites.
3. Subcontractor contracts, certified work, payments, advances, and retention.
4. Customer progress billing, payment certificates, and variations.
5. Actual costs, committed costs, forecast completion costs, cash flow, and project profit.

Deliver the frontend, backend, SQL schema, migrations, business rules, authentication, authorization, reports, imports/exports, seed data, automated tests, startup scripts, backup/restore instructions, and deployment documentation.

Implement the application. Do not stop at a proposal, wireframe, database design, or dashboard. Every required module needs working screens and connected backend operations. Persist business data in SQL Server. Demo figures must come from real seeded transactions and the same services used by normal users.

## 2. Technology and architecture

Use these defaults unless an existing repository contains a compatible established stack:

- Frontend: React, TypeScript, Vite, and Microsoft Fluent UI React v9.
- Backend: ASP.NET Core on .NET 10 LTS.
- Persistence: Entity Framework Core with its SQL Server provider and version-compatible packages.
- Database: Microsoft SQL Server 2022 or a later compatible supported release. SQL Server Developer edition is for development/testing; production configuration must support an appropriately licensed server or Azure SQL.
- Authentication: ASP.NET Core Identity with secure cookie sessions.
- Internal web operations: authenticated server endpoints within the same application connect the web screens to business services. OpenAPI documentation is optional for developers. Core functionality must work without external API services.
- Tests: backend unit/integration tests and Playwright browser tests.
- Local environment: a normally installed SQL Server instance and documented Windows development/startup scripts. Docker must not be a prerequisite for development, testing, or production.
- Application structure: one deployable ASP.NET Core web application serving the compiled React frontend and backend operations under the same web address. Organize the source into clear Domain, Application, Infrastructure, Web, and frontend folders.

For production, build the React frontend into static assets and include them in the ASP.NET Core publish package. Host the combined application as one IIS website/application. Vite and frontend build tools are development/build tools; users must not need to run them. Serve client-side routes correctly while preserving backend route behavior.

Only the backend connects to SQL Server. Keep database credentials on the server, and enforce permissions, approvals, posting, and financial calculations there. Internal endpoints support the working web application; do not deliver an API-only project. External integrations can be added later and are outside the mandatory installation.

Keep financial calculations, posting, validation, and permissions in backend services. Use compatible stable dependencies and pin versions. Implement database transactions, concurrency controls, and idempotent posting. Do not require paid UI components or paid external services for the core solution.

## 3. Selected UI theme: Maggadit Fluent

Implement the selected theme named "Maggadit Fluent". This is a custom Microsoft Fluent UI React v9 theme for the construction ERP, with familiar Business Central page patterns and a more polished visual presentation. Use this design consistently throughout the application.

### 3.1. Theme direction and exact palette

Use a light enterprise theme as the default: deep teal navigation, white working surfaces, cool neutral page backgrounds, and restrained warm stone accents. Keep the application practical for daily financial and site operations.

Define reusable design tokens and map them into the Fluent theme. Do not scatter unrelated colors through individual pages.

| Token/purpose | Selected value | Application |
|---|---|---|
| Header/navigation | #073A40 | Application header, login overlay, navigation emphasis |
| Primary brand | #006B70 | Primary buttons, selected navigation, links, focus indicators |
| Primary hover | #00565B | Hover/pressed emphasis with verified contrast |
| Pale brand surface | #E8F3F2 | Selected rows, active navigation background, subtle cue backgrounds |
| Page background | #F5F7FA | Main application canvas |
| Working surface | #FFFFFF | Forms, grids, documents, FactBoxes |
| Primary text | #182B38 | Titles, values, body text |
| Secondary text | #526570 | Supporting labels and descriptions |
| Border/divider | #DDE4EA | Tables, input outlines, section dividers |
| Warm stone accent | #B68A4B | Small decorative details and construction identity |
| Success | #107C41 | Approved/completed states |
| Warning | #8A4B00 | Pending action, budget warnings |
| Error | #B42318 | Rejected/failed/blocked states |
| Information | #0F6CBD | Informational status |

Use color and text/icon labels together for status. The warm stone accent is decorative; use a darker, contrast-checked variant if it must carry text. Verify foreground/background contrast rather than assuming every token combination is accessible.

For English, use Segoe UI with system fallbacks. For Arabic, use a locally bundled, appropriately licensed Noto Sans Arabic font with Tahoma/system fallbacks. Target 14px body text, 12px secondary labels, 20px section headings, and 28px module titles. Right-align monetary values and use tabular numerals. Keep text and date/currency formatting correct in Arabic.

Use a 4px spacing scale, 6px input/button corners, 8px panels, thin borders, and very light shadows only for elevated panels/dialogs. Use Fluent line icons consistently. Avoid oversized rounded cards, glass surfaces behind data, flashy gradients, or excessive animation.

### 3.2. Application shell and page layouts

Create:

- A compact 52–56px teal application header with product identity, company selector, global "Tell me" search, notifications, help, language selector, and user menu.
- A collapsible department navigation rail, approximately 220px expanded, with consistent Fluent icons, favorites, and active-page emphasis.
- Role Center home pages with modest activity cues, approval queues, project indicators, and real-data charts.
- Department navigation for Projects, Purchasing, Inventory, Subcontracts, Billing, Finance, Reports, and Administration, with supporting Budget/Forecast, Labor, Equipment, Site Operations, Documents, and Approvals pages.
- List pages with compact grids, filter panes, saved views, sorting, column selection/resizing, pagination, totals, and linked records.
- Card pages with collapsible FastTabs such as General, Financial, Planning, Approvals, and Attachments.
- Document pages with a header, editable line grid, totals, posting actions, and linked documents.
- Worksheet pages for budget entry, material issues, payment journals, and forecasting.
- Contextual FactBoxes, approximately 280–320px wide on desktop, showing balances, budget impact, approval history, notes, attachments, and related documents.
- Command bars containing relevant New, Edit, Process, Approve, Post, Report, and Related actions.
- Breadcrumbs, lookup fields, keyboard navigation, visible document status, and explicit save state.

Use approximately 36px grid rows in desktop compact mode and a comfortable mode of at least 44px for touch use. Keep sticky column headers and document totals useful on long worksheets. On narrow screens, place FactBox content in an accessible drawer and preserve the most important actions.

The Role Center must use the overview background specified below, followed by four to six compact activity cues, a project/work queue, and relevant approval/payment tasks. All numbers and charts must come from the real backend data.

### 3.3. Selected background imagery for every module

Use cohesive professional architectural/workplace photography: muted teal/blue shadows, neutral stone and concrete, soft natural daylight, and realistic construction environments. Images must feel like one collection even where the subject differs.

Apply each module's image to a compact 120–144px module landing-page header. Use a dark teal overlay, typically 55–75% depending on the image, and place the module title and short description in accessible white text. Choose crops with usable negative space for text. Provide an RTL-safe crop where needed.

Use solid white surfaces for working forms, line grids, ledgers, totals, and FactBoxes. Keep the photography in the header or surrounding shell so it does not compete with transaction data. Detail documents may use a smaller 64–80px version of the same header and must not repeat large photos between sections. Use one static photo per module, with no autoplay slideshow or parallax.

Use the following explicit image selections and filenames. These are the required asset briefs to source/create during implementation; they are not a statement that image files are already present.

| Module/page | Required image filename | Selected scene and image brief |
|---|---|---|
| Sign-in and company selection | login-construction.webp | Wide modern building site near completion, a tower crane and neutral stone facades, soft morning light, uncluttered space for the sign-in panel; no recognizable landmark |
| Role Center / Overview | overview-construction.webp | Panoramic view of an organized construction development with cranes and structural frames, calm pale sky, clean professional setting |
| Projects and BOQ | projects-blueprints.webp | Architectural plans, a neatly placed hard hat, ruler and structural model on a light desk; printed details should not be legible or contain real project information |
| Budgets and Forecasts | budgets-planning.webp | Quantity-surveying desk with a calculator, measuring tools, closed project folder and softly blurred planning sheets; restrained natural light |
| Purchasing | purchasing-materials.webp | Organized construction material supply yard with steel sections, wrapped material bundles and loading area, tidy and professional |
| Inventory and Site Materials | inventory-warehouse.webp | Bright organized warehouse/site store with racks, pallets and neatly stocked construction materials; clear depth and an uncluttered crop |
| Subcontracts and Payments | subcontracts-coordination.webp | Two site professionals in appropriate PPE reviewing plans at a safe site meeting area, natural candid teamwork, no visible logos or readable contracts |
| Progress Billing and Variations | billing-progress.webp | Architectural facade showing clear stages of building completion, survey measurement context and clean lines; no decorative financial charts |
| Finance and Cash Flow | finance-office.webp | Modern finance desk with calculator, neutral folders and blurred paperwork, warm daylight, no cash piles or real account details |
| Labor and Timesheets | labor-site-team.webp | Small construction team in appropriate PPE attending a morning work briefing in a safe organized area |
| Equipment | equipment-machinery.webp | Well-maintained excavator and crane at an organized site, parked/safe working context, neutral concrete and muted metal colors |
| Site Operations and Daily Logs | site-operations.webp | Site engineer with hard hat checking a tablet beside a structural frame, safe footing and realistic site conditions |
| Reports and Analytics | reports-workspace.webp | Tidy reporting workspace with a widescreen monitor softly out of focus and a neutral project model; screen content must be abstract/unreadable rather than fabricated figures |
| Documents and Drawings | documents-plans.webp | Rolled technical drawings, labeled-neutral folders and measuring tools on a pale table, calm top-down composition |
| Approval Inbox | approvals-review.webp | Professional reviewing a project folder with a tablet on a clear desk, no readable names, signatures or invoice details |
| Administration and Master Data | administration-architecture.webp | Clean contemporary office corridor with glass and stone architectural details, restrained geometric rhythm and soft daylight |

For each module, the required scene is also the generation/search brief. Use brand-neutral imagery with no baked-in UI text, company names, watermarks, private documents, or inappropriate/unsafe construction activity. Keep image composition broad enough for desktop and mobile crops.

Use appropriately licensed local stock images, original provided photography, or original generated images where suitable tools are available. Do not require a paid image-generation API or external service at runtime. Record asset source, author/license where applicable, and any required attribution in the asset manifest.

### 3.4. Asset delivery and image settings

Ship the actual selected images as local application assets, for example under frontend/public/assets/backgrounds, and include them in the combined IIS publish package.

- Provide optimized WebP/JPEG assets with roughly 1600–1920px source width and responsive mobile variants.
- Aim for 250KB or less per desktop header where visual quality permits.
- Load the active module image only; do not preload all module backgrounds.
- Include dimensions to prevent layout shifts and use appropriate responsive image handling.
- Provide a quiet CSS theme background as a fallback if an image fails to load.
- Do not depend on random-image URLs, hotlinked stock-photo sites, or external font/image CDNs for normal operation.
- Support company branding and authorized replacement of each module image.
- Store module image mapping, crop/focal point, overlay strength, and visibility setting in controlled company configuration backed by SQL.
- Allow a user to hide decorative module backgrounds and keep a compact working mode.
- Treat decorative images as decorative for accessibility. Module titles and controls remain real HTML text, not text inside pictures.

Include an asset manifest mapping all module keys to their actual file paths, descriptions, crop settings, and licensing information. User-uploaded replacement backgrounds must use the existing file validation, private storage, authorization, and backup rules.

### 3.5. Design behavior and acceptance criteria

Support English/Arabic RTL, responsive desktop/tablet/mobile layouts, accessible focus states, field labels, contrast, keyboard navigation, validation, loading indicators, empty states, and clear success/failure messages. Mirror the layout where appropriate without blindly mirroring photos or numbers.

Use restrained transitions, respect reduced-motion preferences, and keep posting/approval controls explicit and easy to locate. Status badges, icons, toolbar actions, and totals must use the same components everywhere.

Maintain one shared component/theme system covering the shell, module header, grid, filters, FastTabs, FactBoxes, document totals, approval timeline, status badge, attachment area, report viewer, and dialogs.

Before marking UI work complete:

- Demonstrate the Role Center, Project Card, Purchase Order, Material Issue, Subcontract Certificate, Progress Invoice, and Forecast Worksheet with the chosen theme.
- Show every module's selected image loading from a local asset and verify consistent crops/overlay treatment.
- Verify long tables, Arabic/RTL, keyboard operation, mobile forms, and text contrast over header photos.
- Verify all 16 image mappings and the company replacement/hide-image settings.
- Capture desktop and mobile screenshots when browser tools are available.
- Document any image brief that could not yet be fulfilled rather than claiming an asset exists.

## 4. Required pages and navigation

Provide working list, card, document, or worksheet pages as appropriate for:

- Companies, users, roles, departments, locations, and setup.
- Customers, vendors, subcontractors, items, units, warehouses, and site stores.
- Projects, project tasks/WBS, BOQ, budgets, milestones, and forecasts.
- Purchase requisitions, quotations/comparisons, purchase orders, receipts, and purchase invoices.
- Stock transfers, material requests, consumption issues, returns, adjustments, and stock counts.
- Subcontracts, subcontract variations, work certificates, advances, retention, and payments.
- Customer contracts, progress certificates, sales invoices, customer advances, variations, and receipts.
- Time entries, equipment usage, daily site logs, and supporting documents.
- Approval inbox, notifications, ledgers, finance journals, reports, and audit history.

Include an assisted setup checklist for creating a company, configuring accounts/number series, adding users, and creating the first project.

## 5. Company setup and master data

Support multiple companies and projects with strictly separated company records. A user can access only authorized companies/projects.

Configure:

- Company legal name, logo, address, contacts, base currency, document layouts, and financial periods.
- Locations and projects, including example locations Tripoli, Misurata, Benghazi, and Zintan.
- Customers, vendors/subcontractors, payment terms, bank information, and tax identifiers.
- Items, categories, units of measure, unit conversions, reorder settings, and warehouse/site-store locations.
- Work breakdown structure, cost codes, cost categories, project phases, and responsibility assignments.
- Chart of accounts, posting groups, bank/cash accounts, tax codes, and exchange rates.
- Labor resources/rates, equipment resources/rates, and billing/payment settings.
- Company-specific document number series.

Use LYD as the example base currency, with three decimal places, while allowing other currencies and configurable rounding. Store rates and calculations using decimal precision, never binary floating point. Keep document currency, company currency, exchange-rate date, and applied rate.

Use atomic SQL-backed number series, for example PR000001, PO000001, GRN000001, MI000001, SC000001, PC000001, INV000001, and VAR000001. Enforce uniqueness within company/document type.

## 6. Projects, BOQ, and budgets

Project cards must include customer, contract reference, dates, project manager, location, status, currency, original contract value, approved variations, and revised contract value.

Implement hierarchical WBS/tasks and cost codes. Support BOQ import from an Excel template, validation preview, quantities, units, rates, selling amounts, estimated cost components, and mapping to WBS/cost codes.

Budgets must support:

- Material, labor, equipment, subcontract, overhead, and other costs.
- Original approved budget and separately approved budget revisions.
- Baseline history, revision reasons, effective dates, and approval records.
- Budgets by project, WBS, cost code, and category.
- Quantity/rate-based estimates and monthly planned expenditure.
- Allocation of relevant overhead costs through approved journals.

Show budget, actual cost, open commitments, pending materials, optional PR reservations, available budget, and forecast at each relevant level.

An approved customer variation changes contract value only when approved. A cost budget revision has its own approval. Do not automatically treat a selling-price increase as an identical cost-budget increase.

Completed/closed projects must block new transactions unless reopened through an authorized, audited action.

## 7. Approvals and document lifecycle

Provide configurable approval workflows by company, document type, project, department, amount threshold, and cost category.

Support sequential/parallel steps, approver groups, authorized time-limited delegation, comments, attachments, rejection, resubmission, reminders, and escalation.

Implement states such as Draft, Submitted, Pending Approval, Approved/Released, Rejected, Posted, Cancelled, and Reversed, with document-specific transition rules.

- Validate transitions and permissions on the server.
- Separate creating, approving, and posting permissions.
- Block self-approval by default.
- Snapshot the approved version and approval rules.
- Material changes to amounts, quantities, supplier, project, currency, or cost allocation must invalidate approval and require resubmission.
- Revalidate budget availability at approval and posting.
- Require a separate authorized approval and recorded reason for a budget exception.
- Cancellation of partially fulfilled orders can release only the remaining eligible balance.
- Posting locks the financial/stock document. Corrections use linked reversal or adjustment entries.

The approval inbox must show document details, attachments, financial impact, and history before a decision.

## 8. Purchasing and purchase approvals

Implement the full flow:

Purchase requisition -> approval -> quotation comparison -> purchase order -> receipt -> invoice matching -> payment.

PR lines must contain project, WBS/cost code, item/service, quantity, unit, estimated price, needed date, delivery site/store, and justification.

Support supplier quotations, attachments, comparison by price/delivery/terms, and an approved supplier selection.

Approved POs create project commitments for project-allocated lines. Support partial receipts, partial invoices, returns, credit notes, order changes, and controlled closure of outstanding balances.

Implement three-way matching between PO, receipt/work acceptance, and invoice with configurable quantity/price tolerances and exception approval. Use service/work acceptance records where physical goods receipts do not apply.

Detect duplicate vendor invoice references within company/vendor. Keep links from each PR line through PO, receipt, invoice, stock movement, and payment. Support splitting/combining PR lines without losing their source references.

If PR budget reservations are enabled, converting an approved PR into a PO must transfer the reservation into the commitment without counting both.

## 9. Inventory and materials at sites

Maintain stock by company, item, warehouse/site store, and unit, with posted quantity and value ledgers.

Provide:

- Goods receipts and supplier returns.
- Transfers from central stores to site stores and between sites.
- Dispatch, in-transit, and receiving stages where required.
- Site material requests and approval.
- Project consumption issues and unused-material returns.
- Stock counts, approved adjustments, and reorder alerts.
- Item traceability through source documents; lot/serial tracking for items configured to require it.

Use moving weighted-average costing by default and document the valuation rules. Implement unit conversions and transactional stock availability checks. Block negative stock by default.

Financial treatment must be explicit:

- Store-to-store transfers move inventory and preserve total company inventory value.
- Project consumption issues reduce inventory and charge the project/WBS/cost code.
- Unused-material returns reverse the associated project charge at the appropriate linked valuation.
- Direct-to-project deliveries can create linked receipt and consumption records where explicitly requested.
- Project-allocated stock awaiting consumption is separately visible as pending material cost.
- Receipt/invoice price adjustments must update stock or consumed-project cost as appropriate through traceable adjustment entries.

Preserve the original issue/receipt references, valuation basis, posting date, and user. Prevent concurrent issues from consuming the same available stock.

## 10. Subcontracts, advances, retention, and payments

Subcontract records must include subcontractor, project, BOQ/WBS scope, agreed value, rates, dates, payment terms, retention percentage/cap, advance terms, and recovery rules.

Support approved subcontract variations and revised contract values.

Implement cumulative and current-period work certificates with:

- Measured quantities, rates, approved work, and supporting documents.
- Previous certified quantities/value and current certified value.
- Retention withheld, advance recovery, other authorized deductions, tax, and net payable.
- Quantity/value limits against the approved subcontract and approved changes.
- Site/QS certification and financial approval.

Track advances as recoverable balances, with applications to approved certificates and limits preventing over-recovery. Advance payments themselves must not create project cost.

Recognize project cost from posted certified/accepted work according to the configured posting rules. Settle the related accrued liability when the invoice is posted without charging the same work again.

Provide separate subcontractor statements showing contract value, variations, certificates, invoices, payments, advance balance, retention balance, and outstanding amounts.

Retention releases require authorization and a release date/reason. Releasing retention changes the payable classification/settlement; it does not create another construction expense.

## 11. Customer progress billing and variations

Customer contracts must contain BOQ/schedule-of-values lines, approved contract value, payment terms, retention rules, advance recovery, and billing method.

Support quantity-based, milestone-based, and percentage-based progress certificates with cumulative/current values, prior certified work, evidence, approval, and printable payment applications.

Implement progress invoices, customer advances, receipts, credit notes, and retention release.

Calculate and display separately:

- Gross value of current certified work.
- Tax, customer retention, advance recovery, authorized deductions, and net receivable.
- Previously billed/certified amounts and remaining billable value.
- Customer advance liability and retained receivable balances.

An advance received is a liability until applied under the defined policy; it is not automatically revenue. Retention withheld must not reduce earned contract value.

Implement customer and subcontract variations with scope, quantity/rate/value impact, cost impact, reason, documents, and approval history. Pending variations appear separately in risk/scenario reporting. Only approved variations change firm contract/budget/commitment values.

Keep earned/recognized revenue, invoiced amounts, and collected cash as distinct measures. Configure and document the revenue recognition policy. In the demo, recognize customer-certified earned work when its progress invoice posts; account for any separately posted recognition adjustments without duplicating revenue.

## 12. Finance and posting engine

Implement a real double-entry general ledger, AP/AR subledgers, project cost ledger, commitment ledger, inventory ledger, payment journals, advance balances, and retention balances.

Include chart of accounts, posting groups, posting periods, tax configuration, journal approval/posting, bank accounts, receipt/payment allocation, supplier/customer aging, trial balance, project WIP, income statement, balance sheet, and bank reconciliation using statement CSV import.

Every financial posting must balance debits and credits and preserve source document references and project dimensions.

Define and implement a posting matrix covering at least:

- PO/subcontract approval: commitment entry; no GL expense.
- Stock receipt: inventory against goods-received-not-invoiced.
- Project consumption: project WIP/cost against inventory.
- Direct-use service/work acceptance: project WIP/cost against an accrued liability.
- Matched supplier invoice: clear the receipt/work accrual into AP and account for price/tax differences.
- Supplier advance: advance asset against bank.
- Subcontract certificate/invoice: work cost/accrual, advance recovery, retention payable, and net AP.
- Customer advance: bank against advance liability.
- Progress billing: revenue under the chosen policy, customer advance recovery, retention receivable, and net AR.
- Payments/receipts: settle balances without duplicating cost/revenue.
- Retention release: reclassify/settle the existing retention balance.
- Credit notes, returns, and reversals: linked correcting entries.

Block posting into closed periods. Handle multicurrency settlement gains/losses and reconciliation differences through configured accounts. Do not invent local tax rates or statutory rules; provide configurable settings and use zero tax in the numerical demo.

## 13. Financial integrity and concurrency

Use SQL transactions so posting either writes all associated ledgers and changes or writes nothing.

Implement:

- Idempotency keys and unique posting constraints.
- Optimistic concurrency for document editing.
- Transactional protection for stock, number series, and budget/commitment updates.
- Rechecks preventing two approvals/orders from spending the same available budget.
- Unique source-entry references preventing duplicate actual costs.
- Immutable posted ledger entries with linked reversals.
- Reconciliation between subledgers, control accounts, source documents, and project reports.
- Explicit accounting for rounding differences.
- Validation preventing overbilling, over-certification, overpayment, excessive retention release, and excessive advance recovery.

Keep stock quantity, financial value, cash settlement, commitment consumption, and revenue recognition as distinct operations with explicit links.

## 14. Costs, completion forecasts, and project profit

Calculate indicators from posted ledgers and approved forecasts:

- Actual cost: recognized project costs to date.
- Total approved contracted value: original orders/subcontracts plus approved changes.
- Open committed cost: approved remaining obligations for costs not already recognized.
- Pending material cost: project-allocated inventory that will be consumed but is not yet actual cost.
- Uncommitted ETC: forecast future costs beyond open commitments and pending materials.
- Estimate to complete (ETC): open commitments + pending material cost + uncommitted ETC.
- Estimate at completion (EAC): actual cost + ETC.
- Forecast budget variance: approved revised budget - EAC.
- Approved contract revenue: original customer contract + approved customer variations.
- Forecast project profit: approved contract revenue - EAC.
- Forecast margin: forecast profit / approved contract revenue, with zero-value protection.

Expose the components so users can reconcile every total. Uncommitted ETC must exclude amounts already covered by open commitments or pending materials.

Provide versioned forecasts by WBS/cost code and month, with assumptions, forecast owner, approval, effective date, risk allowance, and revision history. Approved forecasts form reporting snapshots; draft forecasts remain visibly separate.

Show available budget after actuals, open commitments, pending allocated materials, and any active PR reservations. Show recognized profit-to-date separately from forecast final profit and from cash flow.

Include monthly customer collections, supplier/subcontractor payments, advance recoveries, retention releases, and projected cash requirements.

## 15. Labor, equipment, and site records

Provide approved time entries by person, date, project/task/cost code, hours, and rate. Post project labor costs through a defined posting route and prevent a later payroll/import entry from duplicating those costs.

Provide equipment usage by project, equipment, date, hours/days, and agreed internal/rental rates, with corresponding cost postings and allocation rules.

Create daily site logs for completed work, manpower, equipment, materials, delays, photographs, and notes. Link relevant logs to work certification and progress records. Include simple project tasks, milestones, due dates, and progress tracking.

## 16. Documents, notifications, and audit history

Support record-linked PDFs, images, Excel files, drawings, and comments.

Store uploaded files privately, with SQL metadata and record/company authorization. Validate allowed types and sizes, sanitize filenames, and prevent unauthorized download links or executable uploads. Include attachments in backup/restore procedures.

Provide in-app notifications for approvals, rejection, budget exceptions, missing receipts, overdue payments, and retention release dates. Implement configurable email delivery using an outbox, retries, and delivery status; local development uses a test mailbox/sink.

Record who created, changed, submitted, approved, posted, cancelled, reopened, reversed, exported, or changed permissions, with time, reason, and relevant before/after data. Restrict audit-log modification through application/database roles.

## 17. Reporting and exports

Provide filterable reports with drill-through to documents/ledger entries:

- Project budget versus actual, open commitments, pending materials, ETC, EAC, and profit.
- BOQ quantities/rates and certified/billed progress.
- Procurement and purchase approval status.
- Material stock, movement, site consumption, and valuation.
- Subcontract contract/variation/certificate/payment summaries.
- Advances, recoveries, retention aging, and retention release schedules.
- Customer progress billing, collections, and outstanding balances.
- Project WIP, cash flow, forecast history, labor, and equipment cost.
- AP/AR aging, trial balance, financial statements, and audit history.

Support company/project/date/cost-code/vendor/customer filters, Excel/CSV export, printable PDF documents, logos, page numbers, approval references, and Arabic/English layouts.

Preserve source PR references on PO screens and printed POs. Export results must respect the same permissions and filters as the screen. Large reports require paged queries/background generation rather than silent row limits.

## 18. SQL schema and backend requirements

Design normalized tables for company setup, permissions, master data, WBS/BOQ/budgets, workflows, document headers/lines, document lineage, certificates, contracts, forecasts, attachments, notifications, audits, and ledgers.

Use company-scoped keys and foreign keys that prevent cross-company relationships. Provide suitable indexes for company/project/date/document status/source references. Use Unicode text, decimal financial fields, business posting dates, UTC audit timestamps, and concurrency tokens.

Deliver repeatable migrations, SQL schema scripts, an ER diagram, backup/restore scripts, and a data dictionary describing important fields and posting relationships.

Internal backend operations must validate payloads and permissions, return useful errors, support paged/sorted/filtered queries, and reject invalid transitions. Implement import validation previews and atomic imports with row-level error reporting. Provide developer documentation for these operations; a public integration API or OpenAPI/Swagger interface is not required for users to operate the software.

## 19. Users and security

Provide roles for System Administrator, Company Administrator, Project Manager, Quantity Surveyor, Procurement Officer, Warehouse Officer, Site Engineer, Finance Accountant, Finance Manager/Approver, Executive, and Read-Only Auditor.

Provide configurable permissions for viewing, creating, editing, submitting, approving, posting, reversing, exporting, and configuring each module. Roles alone do not grant access to every company/project.

Implement password hashing, account lockout, password recovery, optional MFA, secure sessions, anti-CSRF protection, input validation, parameterized SQL/EF access, HTTPS configuration, and secret management through environment/configuration services.

Do not use a production SA database account for normal application requests. Avoid hardcoded credentials and financial/security data in logs. Provide a secure first-admin bootstrap; demo accounts exist only in the development seed mode.

## 20. Seed data and demonstrable workflows

Create deterministic development seed data with two companies, several projects, two warehouses/site stores, customers, suppliers, subcontractors, materials, labor/equipment, and users covering the major roles.

Include draft, pending, approved, partially fulfilled, posted, overdue, and reversed examples. Generate posted example data through the real posting services so all balances reconcile.

Provide a demo walkthrough that starts at project/BOQ/budget creation and completes PR approval, PO, receipt, site transfer/consumption, supplier invoice/payment, subcontract certification/payment, progress billing, a variation, forecasting, and reporting.

## 21. Mandatory acceptance tests

Test calculations, real SQL Server transactions, company/project permissions, lifecycle transitions, partial fulfillment, and browser workflows. Do not rely exclusively on an in-memory database for SQL-specific tests.

Run SQL integration tests against a dedicated normally installed test database. Do not make Docker or container-based test tools mandatory.

Include these numerical scenarios with tax disabled:

A. Purchase commitment and matched invoice:

- Approve a project PO for 100,000.
- Recognize direct-use work/material cost of 40,000 through the appropriate acceptance/consumption flow.
- Expected actual cost: 40,000; open PO commitment: 60,000.
- Post the matching 40,000 invoice.
- Actual cost remains 40,000, with no duplicate charge.

B. Subcontract advance, certificate, and retention:

- Approve a subcontract for 200,000 and pay an advance of 20,000.
- The advance creates no actual project cost and does not reduce the contractual work obligation.
- Certify 50,000 of work, with 10% retention and 10,000 advance recovery.
- Expected current retention: 5,000; net payable: 35,000; remaining advance: 10,000.
- Expected subcontract actual cost: 50,000; open work commitment: 150,000.
- Matching invoice/payment must not add another 50,000 cost.
- Retention release must not add another expense.

C. Combined project forecast:

- Combine scenarios A and B in one project.
- Actual cost: 90,000; open commitments: 210,000.
- Pending allocated material cost: zero; uncommitted ETC: 100,000.
- Expected ETC: 310,000; EAC: 400,000.
- Original contract: 1,400,000; approved customer variation: 100,000.
- Expected approved revenue: 1,500,000; forecast profit: 1,100,000.
- Pending variations must not increase approved revenue.

D. Warehouse transfer and consumption:

- Receive ten units at a cost of 100 each.
- Transfer six units to a site store.
- Total inventory value remains 1,000 and actual project cost remains zero.
- Consume four units on a project.
- Expected inventory value: 600; actual project cost: 400.
- Return one unused unit through a linked reversal.
- Expected inventory value: 700; net project actual cost: 300.

E. Customer progress billing:

- Receive a customer advance of 30,000.
- Post certified current earned work of 100,000 with 5% retention and advance recovery of 10,000.
- Expected net AR: 85,000; retention receivable: 5,000; advance liability remaining: 20,000.
- Under the stated demo policy, recognized revenue is 100,000.
- Collecting the invoice or releasing retention must not recognize the same revenue again.

F. Integrity and access:

- Retrying a post creates no duplicate entries.
- Two simultaneous issues cannot consume the same stock.
- Two simultaneous approvals cannot overrun the same budget.
- A requester cannot approve their own request under the default policy.
- Changing an approved amount requires reapproval.
- A user cannot access another company's records through an API, export, attachment, or search.
- Closed-period posting, excessive certificate quantities, and duplicate invoices are blocked.
- Reversal restores the correct balances and retains original history.
- Each posted financial batch balances and relevant ledger/control-account reconciliations pass.

Run frontend/backend builds, meaningful automated tests, and the seeded end-to-end walkthrough. Record actual pass/fail results. Never claim a check passed if it was not run.

## 22. Startup, deployment, and operations

Deliver:

- Source code with a clear repository structure.
- Environment/configuration examples containing placeholders rather than real secrets.
- One combined publish package containing the backend, compiled frontend, all selected module background assets, bundled fonts, configuration templates, and deployment instructions.
- Windows PowerShell scripts/instructions for building, publishing, configuring, and starting the application against a normally installed SQL Server instance.
- IIS setup instructions covering the .NET Hosting Bundle, application pool, site binding, HTTPS, filesystem permissions, and SQL connection configuration.
- Persistent private attachment storage and an appropriately permissioned application database account.
- Migration and optional development-seed commands.
- Production deployment instructions for Windows Server/IIS with SQL Server, with an Azure SQL connection option.
- Health/readiness endpoints and structured operational logging.
- Scheduled backup examples, restore verification steps, upgrade/migration procedures, and rollback guidance.
- Initial administrator/company setup instructions and a user manual.

One documented Windows startup procedure must configure the SQL Server connection, create the application database, apply migrations, optionally load demo data, and start the usable web application. Provide a separate normal IIS production installation procedure. Keep database reset and demo seeding explicit and restricted to development.

The default production installation is one Windows Server running IIS and the application, with SQL Server on the same server or a private database server. End users access one browser URL. Docker, Kubernetes, an API gateway, and separate frontend/backend hosting services are not installation prerequisites.

Document assumptions, configuration requirements, and any genuine external prerequisites. Include dependency/license information and avoid features that secretly require unavailable paid packages.

## 23. Implementation sequence and continuity

Inspect the existing workspace first and preserve unrelated work. Create REQUIREMENTS.md, ARCHITECTURE.md, POSTING_RULES.md, and IMPLEMENTATION_STATUS.md.

Implement incrementally:

1. Repository, database, authentication, company/project permissions, and the Maggadit Fluent shell/theme, with the module image asset manifest.
2. Master data, projects, BOQ, approved budgets, and approval workflows.
3. Purchasing, receipts, inventory, site transfers/consumption, and commitments.
4. Subcontracts, advances, work certificates, retention, and payments.
5. Customer contracts, variations, progress billing, receipts, and revenue.
6. General ledger reconciliation, labor/equipment, forecasts, dashboards, and reports.
7. Imports/exports, Arabic/RTL, browser verification, tests, deployment, and documentation.

At each stage, deliver a working vertical flow and run the relevant checks. Maintain a requirement-to-implementation/test checklist.

Use reasonable documented defaults instead of repeatedly asking routine questions. If a material unresolved issue would change the data model or financial treatment, identify it clearly while continuing independent work.

If context or execution limits interrupt progress, update IMPLEMENTATION_STATUS.md with completed functionality, verified checks, outstanding work, and the exact next step. Resume from that state without restarting or declaring unfinished modules complete.

## 24. Final delivery standard

The task is complete only when the required modules have functioning UI, database persistence, real business operations, financial links, and meaningful verification.

Provide the working application, screenshots if browser tools are available, source paths, startup instructions, demo walkthrough, test results, and a clear list of any remaining limitations.

Use these official references for page patterns and technology:

- Business Central page types and layouts: https://learn.microsoft.com/en-us/dynamics365/business-central/dev-itpro/developer/devenv-page-types-and-layouts
- Business Central Role Centers: https://learn.microsoft.com/en-us/dynamics365/business-central/dev-itpro/developer/devenv-designing-role-centers
- Business Central FactBoxes: https://learn.microsoft.com/en-us/dynamics365/business-central/dev-itpro/developer/devenv-adding-a-factbox-to-page
- Microsoft Fluent UI React: https://fluent2.microsoft.design/get-started/develop
- Fluent design tokens: https://fluent2.microsoft.design/design-tokens
- Fluent typography: https://fluent2.microsoft.design/typography
- .NET supported releases: https://learn.microsoft.com/en-us/dotnet/core/releases-and-support

Start by inspecting the workspace, writing the implementation checklist and posting rules, and then building the foundation. Continue until the complete specification is implemented and verified.
