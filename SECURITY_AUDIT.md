## VirtueMart Joomla Module Security Audit (v4.6.4.11226)

Authorized white-box review of the packaged VirtueMart component/template set. Scope limited to externally reachable HTTP entrypoints in `components/com_virtuemart` (frontend) plus associated callback handlers. No speculative issues are reported.

### Phase 1 — Entrypoint Mapping

| Entrypoint | Handler | Transport / Methods | Parameters | Auth / Trust Assumption |
| --- | --- | --- | --- | --- |
| `components/com_virtuemart/virtuemart.php` front controller | determines controller class then executes task (lines 35-147) | HTTP (all) routed through Joomla | `view`/`controller`, `task`, `vmFE` | Public; FE manager paths gated by `vmAccess::isManagingFE` |
| `/controllers/plugin.php` → `VirtueMartControllerPlugin::display` | JSON/HTML self-call render (lines 35-80) | HTTP GET | `vmtype`/`type`, `name`, `format`, `cache` | Public; plugin type/name must match installed plugin and whitelist |
| `/controllers/vmplg.php` → `pluginNotification`, `PaymentResponseReceived` | Payment/shipping callback renderer (lines 62-150) | HTTP callbacks/GET | `layout` (view layout), gateway payload | Public endpoint intended for PSP callbacks; relies on plugin logic for validation |
| `/controllers/invoice.php` → `display` | Invoice/delivery PDF rendering (lines 50-90, 272-360) | HTTP GET | `format`, `layout`, `invoiceNumber`, `order_number`, `order_pass` | Customer with session order or valid guest order token; uses orders model for access control |

### Phase 2 — Data Flow Traces

**Front controller (`virtuemart.php`)**  
*Source*: `view/controller` via `vRequest::getCmd` (line 36) and length/character checks (lines 38-41); `task` via `vRequest::getCmd` (line 43).  
*Transformations*: controller name constrained to alnum/underscore, max length 25; FE/BE path chosen via `vmAccess::isManagingFE` (lines 50-78).  
*Sink*: File include path built as `$basePath.'/controllers/'.$_controller.'.php'` (line 113) and class instantiation (line 134).  
*User control preserved?* No — invalid characters/length rejected and whitelist of existing files limits reachability.

**Plugin self-call (`controllers/plugin.php::display`)**  
*Source*: `vmtype/type` via `vRequest::getCmd` with default `vmcustom` (lines 37-39); `name` via `getCmd` (line 45); `format/cache` via `getCmd` (lines 60, 64).  
*Transformations*: `type` enforced by static whitelist (lines 40-42); `name` blocked if in coupon blacklist (lines 47-51).  
*Sink*: `JPluginHelper::importPlugin($type, $name)` (line 53) and `vDispatcher::directTrigger(... 'plgVmOnSelfCallFE', ...)` (line 56), optional JSON response.  
*User control preserved?* No — only existing whitelisted plugin types/names can be invoked; name filter strips command injection and coupon abuse.

**Payment callback view (`controllers/vmplg.php`)**  
*Source*: `layout` via `vRequest::getVar` (lines 72, 143) and gateway payload through plugin trigger.  
*Transformations*: View layout set with Joomla `setLayout`, which applies an alphanumeric filter before loading tmpl file; plugins are loaded via `JPluginHelper::importPlugin('vmpayment')` (line 64) / `vDispatcher::trigger` (line 69) and handle their own signature validation.  
*Sink*: Rendered `vmplg` view (lines 71-79, 142-149).  
*User control preserved?* No — layout name sanitized by Joomla, payload handed to payment plugins that are expected to validate signatures.

**Invoice download (`controllers/invoice.php`)**  
*Source*: `format`/`layout` via `vRequest::getCmd` (lines 51-53); `invoiceNumber` via `vRequest::getString` (line 279); `order_number`/`order_pass` handled in `OrdersModel::getMyOrderDetails` (administrator model lines 160-236).  
*Transformations*: Requires valid order session or order_number+order_pass and respects `VmConfig::get('ordertracking', ...)` (model lines 162-223); invoice name generated via `VirtueMartModelInvoice::getInvoiceName` (line 332) after sanitizing extensions.  
*Sink*: PDF generated through `VmPdf::createVmPdf` (line 360) and streamed via `downloadFile` (lines 92-209).  
*User control preserved?* No — access control enforces ownership/guest token; file path built from sanitized invoice names and checked for existence.

### Phase 3 — Control Elimination

- Controller selection: invalid controller names rejected before file include (lines 38-41, 113-119), preventing LFI/RCE.  
- Plugin self-call: `typeWhiteList`/`nameBlackList` gates plugin loading (lines 40-51), eliminating arbitrary plugin execution.  
- Payment callbacks: layout sanitized by Joomla view layer; plugin triggers rely on installed PSP plugins which enforce their own signatures, so untrusted request data never reaches core sinks without plugin validation.  
- Invoice download: `getMyOrderDetails` enforces session ownership or order password (model lines 160-223); invoice filenames derived from sanitized tokens (line 332) before filesystem access.

### Phase 4 — Exploitability

No data flow retained sufficient attacker control to reach a sensitive sink (filesystem writes, code execution, unrestricted data exposure) after the above eliminations. All inspected entrypoints validate controller names, plugin types, layouts, and invoice identifiers, and enforce ownership or plugin-level validation before reaching file or DB sinks. **No exploitable vulnerabilities were proven.**

### Phase 5 — Chaining

No viable chains identified because each mapped entrypoint loses attacker control prior to sensitive sinks.

**Conclusion:** No externally exploitable vulnerabilities were demonstrated in the reviewed VirtueMart Joomla module version 4.6.4.11226.

**CVE-2025-6001:** Not observed/applicable in this module version; none of the mapped entrypoints or data flows correspond to the described issue.
